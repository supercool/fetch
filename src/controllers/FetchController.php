<?php

namespace supercool\fetch\controllers;

use Craft;
use craft\web\Controller;
use supercool\fetch\Fetch;
use Throwable;
use yii\web\BadRequestHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

class FetchController extends Controller
{
	/**
	 * @throws BadRequestHttpException
	 * @throws MethodNotAllowedHttpException
	 */
	public function actionFieldPreview(): ?Response
	{
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $url = Craft::$app->getRequest()->getRequiredBodyParam('url');

		try {
			$response = Fetch::getInstance()->getFetch()->getProviderOembedResponse($url);
		} catch (Throwable) {
			return $this->asFailure();
		}

		return $this->asSuccess(data: $response);
    }
}
