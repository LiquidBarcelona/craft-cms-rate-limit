<?php

namespace liquidbcn\ratelimit\services;

use Craft;
use craft\base\Component;

class BlockedRequestsService extends Component
{
    private const CACHE_KEY = 'rate-limit:blocked-requests';
    private const MAX_ENTRIES = 100;
    private const CACHE_DURATION = 86400; // 24 hours

    public function log(string $ip, string $uri): void
    {
        $entries = $this->getAll();

        array_unshift($entries, [
            'ip' => $ip,
            'uri' => $uri,
            'timestamp' => time(),
        ]);

        $entries = array_slice($entries, 0, self::MAX_ENTRIES);

        Craft::$app->getCache()->set(self::CACHE_KEY, $entries, self::CACHE_DURATION);
    }

    public function getAll(): array
    {
        return Craft::$app->getCache()->get(self::CACHE_KEY) ?: [];
    }

    public function clear(): void
    {
        Craft::$app->getCache()->delete(self::CACHE_KEY);
    }

    public function getStats(): array
    {
        $entries = $this->getAll();
        $now = time();
        $lastHour = $now - 3600;
        $last24h = $now - 86400;

        $blockedLastHour = 0;
        $blocked24h = 0;
        $ipCounts = [];

        foreach ($entries as $entry) {
            if ($entry['timestamp'] >= $last24h) {
                $blocked24h++;
                $ipCounts[$entry['ip']] = ($ipCounts[$entry['ip']] ?? 0) + 1;
            }

            if ($entry['timestamp'] >= $lastHour) {
                $blockedLastHour++;
            }
        }

        arsort($ipCounts);
        $topIps = array_slice($ipCounts, 0, 5, true);

        return [
            'blockedLastHour' => $blockedLastHour,
            'blocked24h' => $blocked24h,
            'topIps' => $topIps,
            'totalEntries' => count($entries),
        ];
    }
}