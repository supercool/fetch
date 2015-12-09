<?php
namespace Craft;

/**
 * Fetch by Supercool
 *
 * @package   Fetch
 * @author    Josh Angell
 * @copyright Copyright (c) 2014, Supercool Ltd
 * @link      http://www.supercooldesign.co.uk
 */

class FetchModel extends BaseModel
{

  // Properties
  // =========================================================================

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
    return TemplateHelper::getRaw($this->_getHtml());
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

    $rules[] = array('url', 'Craft\FetchValidator');

    return $rules;
  }


  // Protected Methods
  // =========================================================================

  /**
   * @inheritDoc BaseModel::defineAttributes()
   *
   * @return array
   */
  protected function defineAttributes()
  {
    return array(
      'url'  => array(AttributeType::String, 'required' => true)
    );
  }


  // Private Methods
  // =========================================================================

  private function _getHtml()
  {

    if (!isset($this->_result))
    {
      $this->_result = craft()->fetch->get($this->url);
    }

    return $this->_result['html'];

  }

  private function _getObject()
  {

    if (!isset($this->_result))
    {
      $this->_result = craft()->fetch->get($this->url);
    }

    return $this->_result['object'];

  }


  private function _getProvider()
  {

    if (!isset($this->_result))
    {
      $this->_result = craft()->fetch->get($this->url);
    }

    return $this->_result['provider'];

  }


}
