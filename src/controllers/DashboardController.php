<?php

namespace liquidbcn\ratelimit\controllers;

use Craft;
use craft\web\Controller;
use liquidbcn\ratelimit\RateLimit;
use yii\web\Response;

class DashboardController extends Controller
{
    public function actionIndex(): Response
    {
        $service = RateLimit::getInstance()->getBlockedRequests();

        return $this->renderTemplate('rate-limit/dashboard', [
            'stats' => $service->getStats(),
            'entries' => $service->getAll(),
        ]);
    }

    public function actionClear(): Response
    {
        $this->requirePostRequest();

        RateLimit::getInstance()->getBlockedRequests()->clear();

        Craft::$app->getSession()->setNotice('Blocked requests log cleared.');

        return $this->redirect('rate-limit/dashboard');
    }
}