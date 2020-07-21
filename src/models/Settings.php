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

class Settings extends Model
{
    /**
     * @var bool
     */
    public $validateUrlsOnSave = true;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['validateUrlsOnSave'], 'boolean']
        ];
    }
}
