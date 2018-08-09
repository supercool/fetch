<?php

/**
 * Fetch plugin for Craft CMS 3.x
 *
 * A field type to embed videos for Craft CMS
 *
 * @link      http://www.supercooldesign.co.uk/
 * @copyright Copyright (c) 2018 Supercool Ltd
 */

namespace supercool\fetch\validators;

use Craft;
use yii\validators\Validator;

use supercool\fetch\Fetch;

class FetchValidator extends Validator
{

    /**
     * @inhertdoc
     */
    public function validateAttribute($object, $attribute)
    {
        // get value
        $value = $object->$attribute;

        if ( ! empty($value) )
        {
          // process it
          $result = Fetch::$plugin->getFetch()->get($value);

          // if it didn’t work, spit back the error message
          if ( $result['success'] === false )
          {
            $message = $result['error'];
            $this->addError($object, $attribute, $message);
          }

        }
    }
}
