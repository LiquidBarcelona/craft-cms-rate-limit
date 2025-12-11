<?php

namespace liquidbcn\ratelimit;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use liquidbcn\ratelimit\models\Settings;
use liquidbcn\ratelimit\services\BlockedRequestsService;
use yii\base\Event;
use yii\web\HttpException;

class RateLimit extends Plugin
{
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;

    public function init(): void
    {
        parent::init();

        $this->setComponents([
            'blockedRequests' => BlockedRequestsService::class,
        ]);

        $this->registerCpRoutes();
        $this->registerCpNavItems();

        if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->limitRequest();
        }
    }

    public function getBlockedRequests(): BlockedRequestsService
    {
        return $this->get('blockedRequests');
    }

    protected function registerCpRoutes(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['rate-limit'] = 'rate-limit/dashboard/index';
                $event->rules['rate-limit/dashboard'] = 'rate-limit/dashboard/index';
                $event->rules['rate-limit/dashboard/clear'] = 'rate-limit/dashboard/clear';
            }
        );
    }

    protected function registerCpNavItems(): void
    {
        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function (RegisterCpNavItemsEvent $event) {
                $event->navItems[] = [
                    'url' => 'rate-limit/dashboard',
                    'label' => 'Rate Limit',
                    'icon' => '@liquidbcn/ratelimit/icon.svg',
                ];
            }
        );
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
                $this->logBlockedRequest($ip);
                throw new HttpException(429, 'Rate Limit Exceeded! Slow Down');
            }
        } else {
            $cache->set($key, 1, 60);
        }
    }

    protected function logBlockedRequest(string $ip): void
    {
        $uri = Craft::$app->getRequest()->getUrl();
        $this->getBlockedRequests()->log($ip, $uri);
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