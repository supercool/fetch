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

  public function getName()
  {
    return Craft::t('Fetch');
  }

  public function getInputHtml($name, $value)
  {

    craft()->templates->includeCssResource('fetch/css/fetch.css');
    craft()->templates->includeJsResource('fetch/js/fetch.js');

    $settings = $this->getSettings();

    return craft()->templates->render('fetch/field', array(
      'name'  => $name,
      'value' => $value
    ));

  }

}
