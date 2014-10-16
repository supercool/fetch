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

class FetchFieldType extends BaseFieldType
{

  /**
   * @inheritDoc IComponentType::getName()
   *
   * @return string
   */
  public function getName()
  {
    return Craft::t('Fetch');
  }

  /**
   * @inheritDoc IFieldType::defineContentAttribute()
   *
   * @return mixed
   */
  public function defineContentAttribute()
  {
    return AttributeType::Mixed;
  }

  /**
   * @inheritDoc IFieldType::getInputHtml()
   *
   * @param string $name
   * @param mixed  $value
   *
   * @return string
   */
  public function getInputHtml($name, $value)
  {

    craft()->templates->includeCssResource('fetch/css/fetch.css');
    // craft()->templates->includeJsResource('fetch/js/fetch.js');

    $settings = $this->getSettings();

    return craft()->templates->render('fetch/field', array(
      'name'  => $name,
      'value' => $value
    ));

  }

  /**
   * @inheritDoc IFieldType::validate()
   *
   * @param string $value
   *
   * @return true|string|array
   */
  public function validate($value)
  {
    // get any current errors
    $errors = parent::validate($value);

    if ( ! is_array($errors) )
    {
      $errors = array();
    }

    // get settings - we don't have any yet but this is just here to remind me
    // what I need when we do have them...
    $settings = $this->getSettings();


    // make and populate our model
    $model = new FetchModel;
    $model->fetch = $value;

    // validate the model
    if ( ! $model->validate() )
    {
      $errors = array_merge($errors, $model->getErrors('fetch'));
    }

    // return errors or true
    if ($errors)
    {
      return $errors;
    }
    else
    {
      return true;
    }

  }

}
