<?php
namespace liquidbcn\ratelimit;

use craft\base\Model;
use yii\web\HttpException;

class RateLimit extends \craft\base\Plugin
{
    public bool $hasCpSettings = true;

    public function init()
    {
        parent::init();
        if (!\Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->limitRequest();
        }

    }

    protected function limitRequest() {
        $cache = \Craft::$app->getCache();
        $key = $this->getKey();
        $maxRequests = $this->getMaxRequestsPerMinute();
        $numHits = $cache->get($key);
        if($numHits !== false) {
            $cache->set($key, $numHits + 1, 60);
            if($numHits > $maxRequests) {
                throw new HttpException('429', 'Rate Limit Exceeded! Slow Down');
            }

        } else {
            $cache->set($key, 1);
        }
    }

    protected function getKey() {
        return \Craft::$app->getRequest()->getUserIP() . '::' . round(time()/60)*60;
    }

    protected function getMaxRequestsPerMinute() {
        return $this->getSettings()->maxRequestsPerIpPerMinute;
    }

    protected function createSettingsModel(): ?Model
    {
        return new \liquidbcn\ratelimit\models\Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'craft-cms-rate-limit/settings',
            [
                'settings'  => $this->getSettings(),
            ]
        );
    }

}
