<?php

namespace supercool\fetch\models;

use craft\base\Model;
use craft\helpers\Template;
use Illuminate\Support\Arr;
use supercool\fetch\Fetch as Service;
use Throwable;
use Twig\Markup;

class Fetch extends Model
{
	public string $url;

	private ?array $data = null;

	public function __toString()
	{
		return $this->url ?? '';
	}

	/**
	 * Returns the embed code as a plain HTML.
	 */
	public function getHtml(): ?string
	{
		if (!$this->getOembedData()) {
			return null;
		}

		return Arr::get($this->getOembedData(), 'html');
	}

	/**
	 * Returns the embed code as a Twig\Markup.
	 */
	public function getTwig(): ?Markup
	{
		if (!$html = $this->getHtml()) {
			return null;
		}

		return Template::raw($html);
	}

	/**
	 *
	 */
	public function getData()
	{
		return $this->getObject();
	}

	/**
	 *
	 */
	public function getObject()
	{
		return $this->getOembedData();
	}

	/**
	 *
	 */
	public function getProvider(): ?string
	{
		if (!$this->getOembedData()) {
			return null;
		}

		return Arr::get($this->getOembedData(), 'provider_name');
	}

	/**
	 *
	 */
	public function getOembedData()
	{
		if ($this->data) {
			return $this->data;
		}

		if ($this->url) {
			try {
				$this->data = Service::getInstance()?->getFetch()->getProviderOembedResponse($this->url);
			} catch (Throwable) {
				return null;
			}
		}

		return $this->data;
	}
}
