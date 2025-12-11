# Roadmap

Future features under consideration.

## Rate Limit Headers (RFC 6585)

Add standard HTTP headers to every response:
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Requests left in current window
- `X-RateLimit-Reset`: When the window resets
- `Retry-After`: Seconds to wait (on 429 responses)

Helps API consumers implement proper backoff strategies.

## Custom Response

Configure the 429 response:
- Custom error message
- JSON or HTML format (auto-detect based on Accept header)
- Custom template support for branded error pages

## Route Exclusions

Exclude specific URLs from rate limiting:
- Webhook endpoints (`/webhooks/*`)
- Health check paths (`/api/health`)
- Static assets or specific sections

## Route-Specific Limits

Different limits for different endpoints:
- Stricter limits on login/auth routes (prevent brute force)
- Higher limits for API endpoints
- Custom limits per URL pattern

## Logging & Notifications

Visibility into rate limit events:
- Log violations to Craft's log system
- Email alerts when threshold exceeded
- Track blocked IPs and patterns

## User-Based Limits

Different limits based on authentication:
- Higher limits for logged-in users
- Custom limits per user group
- Separate tracking for authenticated vs anonymous

## Analytics Dashboard

CP widget showing:
- Requests blocked (24h/7d)
- Top blocked IPs
- Most targeted endpoints
- Traffic patterns over time
