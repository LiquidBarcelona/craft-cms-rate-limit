<?php

namespace liquidbcn\ratelimit\models;

use craft\base\Model;

class Settings extends Model
{
    public bool $enabled = true;

    public int $maxRequestsPerIpPerMinute = 4000;

    public array $excludedIps = [];

    public function setExcludedIps(array|string $value): void
    {
        if (is_string($value)) {
            $lines = array_filter(
                array_map('trim', explode("\n", $value)),
                fn($line) => $line !== ''
            );
            $this->excludedIps = array_values($lines);
        } else {
            $this->excludedIps = $value;
        }
    }

    public function rules(): array
    {
        return [
            ['enabled', 'boolean'],
            ['maxRequestsPerIpPerMinute', 'integer', 'min' => 1],
            ['excludedIps', 'each', 'rule' => ['string']],
        ];
    }
}
