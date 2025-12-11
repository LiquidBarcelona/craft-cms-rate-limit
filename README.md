# Rate Limit plugin for Craft CMS

Protect your Craft CMS site from DDoS attacks and abuse by limiting requests per IP address.

## Requirements

- Craft CMS 5.0.0 or later
- PHP 8.2 or later

## Installation

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Require the plugin via Composer:

        composer require liquidbcn/craftcms-rate-limit

3. In the Control Panel, go to Settings → Plugins and click "Install" for Rate Limit.

## Configuration

### Control Panel

Navigate to Settings → Rate Limit to configure:

- **Enable Rate Limiting** - Toggle protection on/off
- **Max Requests per IP per Minute** - Request limit within a 60-second window (default: 4000)
- **Excluded IPs** - Whitelist IPs or CIDR ranges that bypass rate limiting

### Config File

You can also configure via `config/rate-limit.php`. Config file values take precedence over CP settings.

```php
<?php

return [
    'enabled' => true,
    'maxRequestsPerIpPerMinute' => 200,
    'excludedIps' => [
        '127.0.0.1',
        '10.0.0.0/8',
        '192.168.1.0/24',
    ],
];
```

### Multi-environment Configuration

```php
<?php

return [
    '*' => [
        'maxRequestsPerIpPerMinute' => 200,
    ],
    'dev' => [
        'enabled' => false,
    ],
    'production' => [
        'maxRequestsPerIpPerMinute' => 100,
    ],
];
```

## How It Works

The plugin tracks requests per IP using Craft's cache system with 60-second windows. When an IP exceeds the configured limit, subsequent requests receive an HTTP 429 (Too Many Requests) response.

---

Brought to you by [Liquid Studio](https://liquidbcn.com)
