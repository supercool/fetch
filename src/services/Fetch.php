<?php

namespace supercool\fetch\services;

use Craft;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use craft\helpers\Json;
use yii\base\Component;
use yii\base\NotSupportedException;

class Fetch extends Component
{
	public Client|null $client = null;

	/**
	 * @inerhitdoc
	 */
	public function init(): void
	{
		parent::init();

		$this->client = Craft::createGuzzleClient();
	}

	public function getAllOembedProviders()
	{
		/**
		 * @see https://oembed.com/providers.json
		 */
		$filePath = __DIR__ . '/../data/providers.json';

		if (@file_exists($filePath) && $data = @file_get_contents($filePath)) {
			return Json::decode($data);
		}

		return [];
	}

	public function findOembedProviderFromUrl($url): ?array
	{
		$providers = $this->getAllOembedProviders();

		foreach ($providers as $provider) {
			foreach ($provider['endpoints'] ?? [] as $endpoint) {
				foreach ($endpoint['schemes'] ?? [] as $scheme) {
					$schemePattern = $scheme;
					$schemePattern = preg_quote($schemePattern, '/');
					$schemePattern = str_replace('\*', '.*', $schemePattern);

					if (!preg_match('/' . $schemePattern . '/', $url)) {
						continue;
					}

					return [
						'provider' => $provider,
						'matchedEndpoint' => $endpoint,
					];
				}
			}
		}

		return null;
	}

	/**
	 * @throws GuzzleException
	 * @throws NotSupportedException
	 */
	public function getProviderOembedResponse($url)
	{
		$key = "fetch:$url";

		$cache = Craft::$app->getCache();

		if ($value = $cache?->get($key)) {
			return $value;
		}

		$provider = $this->findOembedProviderFromUrl($url);

		if (!$provider || !$oembedEndpoint = Arr::get($provider, 'matchedEndpoint.url')) {
			throw new NotSupportedException(Craft::t('fetch', 'Provider not supported.'));
		}

		$requestUrl = "$oembedEndpoint?format=json&url=$url";
		$requestUrl = str_replace('{format}', 'json', $requestUrl);

		$client = Craft::createGuzzleClient([
			'connect_timeout' => 0,
			'timeout' => 0,
			'http_errors' => false,
			'headers' => [
				'Accept' => 'application/json',
				'Content-Type' => 'application/json',
			],
		]);

		$response = $client->get($requestUrl);
		$contents = $response->getBody()->getContents();

		if (!Json::isJsonObject($contents) || $response->getStatusCode() !== 200) {
			throw new Exception(Craft::t('fetch', 'Unable to fetch content from the provided URL. Please check the provided URL is correct.'));
		}

		$decodedContents = Json::decode($contents);
		$cache?->set($key, $decodedContents);

		return $decodedContents;
	}
}
