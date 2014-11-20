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
   * @inheritDoc IFieldType::prepValue()
   *
   * @param string $value
   *
   * @return string|FetchModel
   */
  public function prepValue($value)
  {

    if ( ! empty($value) )
    {

      $model = new FetchModel;
      $model->url = $value;

      if ( $model->validate() )
      {
        return $model;
      }
      else
      {
        return $value;
      }

    }

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
    craft()->templates->includeJsResource('fetch/js/fetch.js');

    craft()->templates->includeJs('new Craft.Fetch("'.craft()->templates->namespaceInputId($name).'");');

    return craft()->templates->render('fetch/field', array(
      'name'  => $name,
      'value' => $value
    ));

  }

  /**
   * @inheritDoc IFieldType::prepValueFromPost()
   *
   * @param string $value
   *
   * @return string
   */
  public function prepValueFromPost($value)
  {

    // clean up spaces, flipping users.
    $value = trim($value);

    if ( ! empty($value) )
    {
      // check if there is a protocol, add if not
      if ( parse_url($value, PHP_URL_SCHEME) === null )
      {
        $value = 'http://' . $value;
      }
    }

    return $value;

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

    if ( empty($value) )
    {
      return true;
    }

    // get any current errors
    $errors = parent::validate($value);

    if ( ! is_array($errors) )
    {
      $errors = array();
    }

    // make and populate our model
    $model = new FetchModel;
    $model->url = $value;

    // validate the model
    if ( ! $model->validate() )
    {
      $errors = array_merge($errors, $model->getErrors('url'));
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
