# Rate Limit plugin for Craft CMS
This plugin allows you to limit the requests on your site per IP preventing DDOS attacks from bots and people.

## Requirements

This plugin requires Craft CMS 4.0.0 or later

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require liquid/craftcms-rate-limit

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Rate Limit.


## Configuring Rate Limit

You can configure the Rate Limit creating a file under your config folder named rate-limit with this content
```
<?php

return [
    'maxRequestsPerIpPerMinute' => 100,
];

```


Brought to you by
<a href="https://liquidbcn.com" target="_blank">Liquid Studio</a>
