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
use supercool\fetch\fields\FetchField;

class FetchController extends BaseController
{

    public function actionGet()
    {
        // only ajax post requests
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        if(Craft::$app->request->getIsCpRequest() && !Fetch::$plugin->getSettings()->validateUrlsOnSave) {
            return $this->asJson(['success' => false, 'validationDisabled' => true]);
        }

        // grab url
        $request = Craft::$app->getRequest();
        $url = $request->getBodyParam('url');

        // return result as json
        return $this->asJson( array_merge(Fetch::$plugin->getFetch()->get($url, new FetchField()), ['validationDisabled' => false]) );
    }

}
