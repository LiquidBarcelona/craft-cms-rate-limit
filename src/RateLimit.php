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
        $settings = $this->getSettings();

        if (!$settings->enabled) {
            return;
        }

        $ip = Craft::$app->getRequest()->getUserIP();

        if ($this->isIpExcluded($ip)) {
            return;
        }

        $cache = Craft::$app->getCache();
        $key = $this->getCacheKey($ip);
        $maxRequests = $settings->maxRequestsPerIpPerMinute;
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

    protected function getCacheKey(string $ip): string
    {
        $timestamp = (int) (round(time() / 60) * 60);

        return "rate-limit:{$ip}:{$timestamp}";
    }

    protected function isIpExcluded(string $ip): bool
    {
        $excludedIps = $this->getSettings()->excludedIps;

        foreach ($excludedIps as $excluded) {
            $excluded = trim($excluded);

            if ($excluded === '') {
                continue;
            }

            if (str_contains($excluded, '/')) {
                if ($this->ipMatchesCidr($ip, $excluded)) {
                    return true;
                }
            } elseif ($ip === $excluded) {
                return true;
            }
        }

        return false;
    }

    protected function ipMatchesCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);
        $bits = (int) $bits;

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $mask = -1 << (32 - $bits);

        return ($ipLong & $mask) === ($subnetLong & $mask);
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