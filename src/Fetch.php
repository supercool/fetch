<?php

/**
 * Fetch plugin for Craft CMS 3.x
 *
 * A field type to embed videos for Craft CMS
 *
 * @link      http://www.supercooldesign.co.uk/
 * @copyright Copyright (c) 2018 Supercool Ltd
 */

namespace supercool\fetch;


use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;

use supercool\fetch\fields\FetchField;

use yii\base\Event;

/**
 * @author    Supercool Ltd
 * @package   Fetch
 * @since     1.0.0
 */

class Fetch extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Plugin
     */
    public static $plugin;


    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Register services
        $this->setComponents([
            'fetch' => \supercool\fetch\services\Fetch::class,
        ]);

        // Register our field
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
    protected function createSettingsModel(): ?craft\base\Model
    {
        return new \supercool\fetch\models\Settings();
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


    /**
     * @return Entries
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidConfigException
     */
    public function getFetch()
    {
        return $this->get('fetch');
    }

}
