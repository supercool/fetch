<?php

namespace supercool\fetch\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Cp;
use Illuminate\Support\Arr;
use supercool\fetch\Fetch;
use supercool\fetch\assetbundles\FetchAsset;
use supercool\fetch\models\Fetch as FetchModel;
use Throwable;

class FetchField extends Field
{
	public array $allowedProviders = [];

	/**
	 * @inheritdoc
	 */
	public function __construct($config = [])
	{
		$legacyAllowedProviders = [
			'allowFlickr' => 'Flickr',
			'allowInstagram' => 'Instagram',
			'allowPintrest' => 'Pinterest',
			'allowSoundcloud' => 'SoundCloud',
			'allowTwitter' => 'Twitter',
			'allowVimeo' => 'Vimeo',
			'allowYoutube' => 'YouTube',
		];

		foreach ($legacyAllowedProviders as $old => $new) {
			if (!empty($config[$old])) {
				$this->allowedProviders[] = $new;
			}

			unset($config[$old]);
		}

		parent::__construct($config);
	}

	/**
	 * @inerhitdoc
	 */
	public static function displayName(): string
	{
		return Craft::t('fetch', 'Fetch');
	}

	/**
	 * @inerhitdoc
	 */
	public function rules(): array
	{
		$rules = parent::rules();

		$rules[] = [['allowedProviders'],
			'in',
			'allowArray' => true,
			'range' => Arr::pluck($this->getProviderOptions(), 'value'),
		];

		return $rules;
	}

	/**
	 * @inerhitdoc
	 */
	public function getElementValidationRules(): array
	{
		$rules = parent::getElementValidationRules();

		if (Fetch::getInstance()->getSettings()->validateUrlsOnSave) {
			$rules[] = 'validateProvider';
		}

		return $rules;
	}

	/**
	 * @inerhitdoc
	 */
	public function getSettingsHtml(): ?string
	{
		return $this->settingsHtml(false);
	}

	/**
	 * @inerhitdoc
	 */
	public function getReadOnlySettingsHtml(): ?string
	{
		return $this->settingsHtml(true);
	}

	private function settingsHtml(bool $readOnly): string
	{
		return Cp::selectizeFieldHtml([
			'label' => Craft::t('fetch', 'Allowed Providers'),
			'instructions' => Craft::t('fetch', 'The providers to allow fetching content from.'),
			'multi' => true,
			'name' => 'allowedProviders',
			'options' => $this->getProviderOptions(),
			'values' => $this->allowedProviders,
			'errors' => $this->getErrors('allowedProviders'),
			'disabled' => $readOnly,
		]);
	}

	/**
	 * @inerhitdoc
	 */
	public function normalizeValue($value, ?ElementInterface $element): mixed
	{
		if ($value instanceof FetchModel) {
			return $value;
		}

		if (!is_string($value)) {
			return null;
		}

		return new FetchModel([
			'url' => $value,
		]);
	}

	/**
	 * @inerhitdoc
	 */
	public function serializeValue($value, ?ElementInterface $element = null): mixed
	{
		$value = trim($value);

		return parent::serializeValue($value, $element);
	}

	/**
	 * @inerhitdoc
	 */
	public function getInputHtml($value, ?ElementInterface $element): string
	{
		$view = Craft::$app->getView();
		$view->registerAssetBundle(FetchAsset::class);

		$name = $this->handle;

		$view->registerJsWithVars(
			fn($name, $fieldId) => "new Craft.Fetch($name, $fieldId)",
			[$view->namespaceInputId($name), $this->id]
		);

		return $view->renderTemplate('fetch/field/_input', [
			'name' => $name,
			'value' => $value
		]);
	}

	public function getProviderOptions(): array
	{
		$options = [];

		foreach (Fetch::getInstance()->getFetch()->getAllOembedProviders() as $provider) {
			$options[] = [
				'label' => $provider['provider_name'],
				'value' => $provider['provider_name'],
			];
		}

		return $options;
	}

	public function validateProvider($element): void
	{
		$fetch = Fetch::getInstance()->getFetch();
		$value = $element->getFieldValue($this->handle);

		$providerData = $fetch->findOembedProviderFromUrl($value);
		$providerName = Arr::get($providerData ?? [], 'provider.provider_name');

		/**
		 * Determine if the URL matches one of the patterns in our dataset and if the provider is allowed.
		 */
		if (!in_array($providerName, $this->allowedProviders, true)) {
			$element->addError($this->handle, Craft::t('fetch', "URL or provider isn't supported."));
			return;
		}

		/**
		 * If the provider is supported, check the validity of the URL by fetching the oembed data.
		 */
		try {
			$response = $fetch->getProviderOembedResponse($value);

			$success = ($response['html'] ?? null);
		} catch (Throwable) {
			$success = false;
		}

		if (!$success) {
			$element->addError($this->handle, Craft::t('fetch', "Unable to retrieve embed content. Either the URL is invalid, or the provider returned invalid data."));
		}
	}
}
