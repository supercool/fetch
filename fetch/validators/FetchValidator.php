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


    if ( ! empty($value) )
    {

      // process it
      $result = craft()->fetch->get($value);

      // if it didn’t work, spit back the error message
      if ( $result['success'] === false )
      {
        $message = $result['error'];
        $this->addError($object, $attribute, $message);
      }

    }

  }

}
