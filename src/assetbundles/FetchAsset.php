<?php

/**
 * Fetch plugin for Craft CMS 3.x
 *
 * A field type to embed videos for Craft CMS
 *
 * @link      http://www.supercooldesign.co.uk/
 * @copyright Copyright (c) 2018 Supercool Ltd
 */

namespace supercool\fetch\assetbundles;

use Craft;
use craft\web\View;
use craft\helpers\Json;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class FetchAsset extends AssetBundle
{

  // Public Methods
  // =========================================================================

  /**
   * Initializes the bundle.
   */
  public function init()
  {
      // define the path that your publishable resources live
    $this->sourcePath = "@supercool/fetch/assetbundles/dist";

    // define the dependencies
    $this->depends = [
      CpAsset::class,
    ];

    // define the relative path to CSS/JS files that should be registered with the page
    // when this asset bundle is registered
    $this->js = [
      'js/fetch.js',
    ];

    $this->css = [
      'css/fetch.css',
    ];

    parent::init();
  }

}
