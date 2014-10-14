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

class FetchVariable
{

  public function embed($url, $scripts = true, $twig = true)
  {

    if ( $html = craft()->supercoolFields_embed->get($url, $scripts, $twig) ) {
      return $html;
    } else {
      return $url;
    }

  }

}
