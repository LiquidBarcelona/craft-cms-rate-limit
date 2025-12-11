<?php

namespace liquidbcn\ratelimit;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use liquidbcn\ratelimit\models\Settings;
use yii\web\HttpException;

class RateLimit extends Plugin
{
    public bool $hasCpSettings = true;

    public function init(): void
    {
        parent::init();

        if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->limitRequest();
        }
    }

    protected function limitRequest(): void
    {
        $cache = Craft::$app->getCache();
        $key = $this->getKey();
        $maxRequests = $this->getMaxRequestsPerMinute();
        $numHits = $cache->get($key);

        if ($numHits !== false) {
            $cache->set($key, $numHits + 1, 60);

            if ($numHits > $maxRequests) {
                throw new HttpException(429, 'Rate Limit Exceeded! Slow Down');
            }
        } else {
            $cache->set($key, 1, 60);
        }
    }

    protected function getKey(): string
    {
        return Craft::$app->getRequest()->getUserIP() . '::' . (int) (round(time() / 60) * 60);
    }

    protected function getMaxRequestsPerMinute(): int
    {
        return $this->getSettings()->maxRequestsPerIpPerMinute;
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate(
            'rate-limit/settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }
}