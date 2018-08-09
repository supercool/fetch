<?php

/**
 * Fetch plugin for Craft CMS 3.x
 *
 * A field type to embed videos for Craft CMS
 *
 * @link      http://www.supercooldesign.co.uk/
 * @copyright Copyright (c) 2018 Supercool Ltd
 */

namespace supercool\fetch\models;

use craft\base\Model;
use craft\helpers\Template as TemplateHelper;

use supercool\fetch\Fetch as FetchPlugin;
use supercool\fetch\validators\FetchValidator;

class Fetch extends Model
{

    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $url;

    /**
     * @var
     */
    private $_result;


    // Public Methods
    // =========================================================================


    /**
     * Use the plain url as the string representation.
     *
     * @return \Twig_Markup
     */
    public function __toString()
    {
        return $this->url;
    }


    /**
     * Returns the embed code as a Twig_Markup
     *
     * @return \Twig_Markup
     */
    public function getTwig()
    {
        return TemplateHelper::raw($this->_getHtml());
    }


    /**
     * Returns the embed code as a plain HTML
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->_getHtml();
    }

    /**
     * Returns the whole json object
     *
     * @return string
     */
    public function getObject()
    {
        return $this->_getObject();
    }


    /**
     * Returns the provider
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->_getProvider();
    }


    /**
     * @inheritDoc BaseModel::rules()
     *
     * @return array
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = ['url', FetchValidator::class];
        return $rules;
    }


    // Private Methods
    // =========================================================================

    private function _getHtml()
    {
        if (!isset($this->_result))
        {
          $this->_result = FetchPlugin::$plugin->getFetch()->get($this->url);
        }

        return $this->_result['html'];
    }


    private function _getObject()
    {
        if (!isset($this->_result))
        {
          $this->_result = FetchPlugin::$plugin->getFetch()->get($this->url);
        }

        return $this->_result['object'];
    }


    private function _getProvider()
    {
        if (!isset($this->_result))
        {
          $this->_result = FetchPlugin::$plugin->getFetch()->get($this->url);
        }

        return $this->_result['provider'];
    }

}
