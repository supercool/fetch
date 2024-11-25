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
use supercool\fetch\fields\FetchField;

class Fetch extends Model
{

    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $url;

    /**
     * @var FetchField
     */
    public $field;

    /**
     * @var
     */
    private $_result;


    // Public Methods
    // =========================================================================


    /**
     * Use the plain url as the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->url ? (string) $this->url : '';
    }


    /**
     * Returns the embed code as a Twig_Markup
     *
     * @return \Twig\Markup
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

    public function getSuccess()
    {
        return $this->_getSuccess();
    }

    public function getErrorMessage()
    {
        return $this->_getErrorMessage();
    }


    /**
     * @inheritDoc BaseModel::rules()
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = ['url', 'validateUrl'];
        return $rules;
    }

    public function validateUrl($attribute)
    {
        if ( ! empty($this->$attribute) )
        {
            // process it
            $result = FetchPlugin::$plugin->getFetch()->get($this->$attribute, $this->field);

            // if it didn’t work, spit back the error message
            if ( $result['success'] === false )
            {
                $message = $result['error'];
                $this->addError($attribute, $message);
            }
        }
    }


    // Private Methods
    // =========================================================================

    private function _getHtml()
    {
        if (!isset($this->_result))
        {
          $this->_result = FetchPlugin::$plugin->getFetch()->get($this->url, $this->field);
        }

        return $this->_result['html'];
    }


    private function _getObject()
    {
        if (!isset($this->_result))
        {
          $this->_result = FetchPlugin::$plugin->getFetch()->get($this->url, $this->field);
        }

        return $this->_result['object'];
    }


    private function _getProvider()
    {
        if (!isset($this->_result))
        {
          $this->_result = FetchPlugin::$plugin->getFetch()->get($this->url, $this->field);
        }

        return $this->_result['provider'];
    }

    private function _getSuccess()
    {
        if (!isset($this->_result))
        {
            $this->_result = FetchPlugin::$plugin->getFetch()->get($this->url, $this->field);
        }

        return $this->_result['success'];
    }

    private function _getErrorMessage()
    {
        {
            if (!isset($this->_result))
            {
                $this->_result = FetchPlugin::$plugin->getFetch()->get($this->url, $this->field);
            }

            if($this->_result['success'])
            {
                return null;
            }

            return $this->_result['error'];
        }
    }
}
