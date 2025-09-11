<?php

namespace supercool\fetch;

use Craft;
use craft\base\Plugin;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use supercool\fetch\fields\FetchField;
use supercool\fetch\models\Settings;
use yii\base\Event;

class Fetch extends Plugin
{
	/**
	 * @inhertdoc
	 */
	public function init()
	{
		parent::init();

		$this->setComponents([
			'fetch' => services\Fetch::class,
		]);

		Event::on(
			Fields::class,
			Fields::EVENT_REGISTER_FIELD_TYPES,
			function (RegisterComponentTypesEvent $event) {
				$event->types[] = FetchField::class;
			}
		);
	}

	/**
	 * @inhertdoc
	 */
	protected function createSettingsModel(): Settings
	{
		return new Settings();
	}

	/**
	 * @inheritdoc
	 */
	protected function settingsHtml(): ?string
	{
		return Craft::$app->getView()->renderTemplate('fetch/settings', [
			'settings' => $this->getSettings()
		]);
	}

	public function getFetch(): services\Fetch
	{
		return $this->get('fetch');
	}
}
