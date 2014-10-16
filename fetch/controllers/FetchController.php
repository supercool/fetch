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

class FetchController extends BaseController
{

  public function actionGet()
  {

    // only ajax post requests
    $this->requirePostRequest();
    $this->requireAjaxRequest();

    // grab url
    $url = craft()->request->getPost('url');

    // return result as json
    $this->returnJson(craft()->fetch->get($url));

  }

}
