# Rate Limit for Craft CMS

Simple, effective request throttling for your Craft CMS site.

## What it does

Rate Limit tracks requests per IP address and blocks those that exceed your configured threshold. When a visitor (or bot) makes too many requests in a short period, they receive an HTTP 429 response instead of consuming your server resources.

## Why you need it

- **Bot protection**: Scrapers, crawlers, and automated tools can hammer your site with thousands of requests per minute
- **DDoS mitigation**: First line of defense against volumetric attacks
- **Resource protection**: Prevent a single bad actor from degrading performance for everyone else
- **API abuse prevention**: Essential if your site exposes any kind of API or form endpoints

## Key features

**Configurable limits**
Set the maximum requests per IP per minute. Default is 4000, but you can dial it down for stricter protection or adjust based on your traffic patterns.

**IP whitelisting**
Exclude trusted IPs from rate limiting. Supports individual addresses and CIDR ranges. Useful for:
- Your office or team IPs
- Payment gateway callbacks
- Health check endpoints from load balancers
- Trusted third-party integrations

**Enable/disable toggle**
Turn rate limiting on or off instantly from the Control Panel. Useful for debugging or temporarily allowing high-volume operations.

**Config file support**
Configure via the Control Panel or `config/rate-limit.php`. Config file values take precedence, allowing environment-specific settings (stricter in production, disabled in dev).

## How it works

The plugin uses Craft's cache system to track request counts in 60-second windows. Each IP gets its own counter. When the counter exceeds your limit, subsequent requests are rejected with a 429 status code until the window resets.

No external services. No database tables. No performance overhead beyond a simple cache read/write per request.

## Requirements

- Craft CMS 5.0+
- PHP 8.2+
