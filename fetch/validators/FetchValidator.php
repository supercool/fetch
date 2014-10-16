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

use CValidator;

class FetchValidator extends CValidator
{

  protected function validateAttribute($object, $attribute)
  {

    // get value
    $value = $object->$attribute;

    // check the url brought something correct back
    $result = craft()->fetch->get($value, false, false);

    if ( $result['success'] === false )
    {
      // $message = Craft::t("Sorry, that url doesn’t seem to work, please try again.");
      $message = $result['error'];
      $this->addError($object, $attribute, $message);
    }

  }

}
