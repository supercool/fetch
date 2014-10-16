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

  protected function defineAttributes()
  {
    return array(
      'fetch' => array(
        'type'     => AttributeType::String,
        'required' => true
      )
    );
  }

  public function rules()
  {
    $rules = parent::rules();

    $rules[] = array('fetch', 'Craft\FetchValidator');

    return $rules;
  }

}
