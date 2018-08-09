<?php

/**
 * Fetch plugin for Craft CMS 3.x
 *
 * A field type to embed videos for Craft CMS
 *
 * @link      http://www.supercooldesign.co.uk/
 * @copyright Copyright (c) 2018 Supercool Ltd
 */

namespace supercool\fetch\controllers;

use Craft;
use craft\web\Controller as BaseController;

use supercool\fetch\Fetch;

class FetchController extends BaseController
{

    public function actionGet()
    {
        // only ajax post requests
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        // grab url
        $request = Craft::$app->getRequest();
        $url = $request->getBodyParam('url');

        // return result as json
        return $this->asJson( Fetch::$plugin->getFetch()->get($url) );
    }

}
