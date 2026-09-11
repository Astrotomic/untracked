# Untracked

**Analytics without visitors.**

Untracked counts what happened on a website without creating a visitor model at all. No cookies, sessions, fingerprints, pseudonymous IDs, hashed IP addresses or unique visitor estimates.

A request is reduced in memory to a few coarse dimensions and immediately added to independent daily counters. The original request is never stored by Untracked.

## What is stored

Website configuration is normal application data:

- UUID
- name
- domain
- timezone
- whether bots should be counted

Analytics data has exactly five columns:

```text
website_uuid
date
metric
value
count
```

One request can increment counters such as:

```text
2026-09-11 | path         | /blog/example       | +1
2026-09-11 | country      | DE                  | +1
2026-09-11 | browser      | Firefox             | +1
2026-09-11 | os           | Linux               | +1
2026-09-11 | device       | desktop             | +1
2026-09-11 | format       | markdown            | +1
2026-09-11 | referrer     | news.ycombinator.com| +1
2026-09-11 | utm_source   | newsletter          | +1
2026-09-11 | utm_medium   | email               | +1
2026-09-11 | utm_campaign | launch              | +1
```

Those counters are independent. Untracked cannot later answer which German request used Firefox, which referrer led to a specific page, which UTM campaign used which browser, or whether two requests came from the same person.

Optional dimensions such as referrer and UTM parameters only create counters when they are present. There are no fake `(direct)` or empty rows.

The analytics table intentionally has no `id`, `created_at` or `updated_at`. Exact request times would make otherwise independent counters correlatable again.

## What is not stored

- IP addresses
- full User-Agent strings
- exact timestamps
- full query strings
- non-UTM query parameters
- full referrer URLs
- referrer paths or query strings
- screen sizes
- language
- sessions
- fingerprints
- visitor IDs
- unique visitors

Normal web-server, proxy or infrastructure access logs are outside Untracked. Disable or sanitize those separately if you want the same privacy properties across the full stack.

## Install

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan user:create
```

Then run the app normally and log in with the user you created.

## Two collection endpoints

Untracked deliberately separates requests that still contain raw client information from requests that have already been reduced by the source application.

### Raw collection

```text
POST /api/websites/{uuid}/collect/raw
```

The raw collector reads the request IP and User-Agent, resolves them in memory, reduces them to coarse analytics dimensions and immediately forgets the raw values. Only the independent daily counters are persisted.

The bundled browser script uses this endpoint:

```html
<script defer data-website-id="YOUR_WEBSITE_UUID" src="https://analytics.example.com/script.js"></script>
```

The script sends only the current pathname, `html` format, external referrer hostname and the five standard UTM values when present. It parses `document.referrer` and `window.location.search` in the browser, so the full referrer URL and full page query never leave the page.

Same-site referrers are ignored. Browser requests include an `Origin` header, which Untracked checks against the website's configured domain.

### Processed collection

```text
POST /api/websites/{uuid}/collect/processed
```

Use this from a backend that already reduced the request itself. This endpoint never reads the request IP or User-Agent for analytics. Browser and OS values run through the same semantic normalizers as raw UAP data; device and format stay small backed enums.

```json
{
  "path": "/blog/example",
  "country": "DE",
  "browser": "Firefox",
  "os": "Linux",
  "device": "desktop",
  "format": "markdown",
  "referrer": "news.ycombinator.com",
  "utm_source": "newsletter",
  "utm_medium": "email",
  "utm_campaign": "launch",
  "utm_term": "privacy analytics",
  "utm_content": "hero-link"
}
```

Paths are reduced to the pathname before storage, so query strings are discarded. Referrers are reduced to their hostname and same-site referrers are dropped. UTM values are trimmed, control characters are removed and values are bounded to 255 characters.

UTM values are still arbitrary strings chosen by whoever creates the campaign. Do not put email addresses, user IDs or other personal data into them if you want to preserve Untracked's privacy model.

If your routes can contain personal or secret values, normalize those paths before sending them. Untracked deliberately does not retain a raw event that could be fixed afterwards.

## User-Agent processing

User-Agent processing uses a Laravel manager/driver setup. The default `uap` driver uses [`ua-parser/uap-php`](https://packagist.org/packages/ua-parser/uap-php).

```dotenv
ANALYTICS_USER_AGENT_DRIVER=uap
```

UAP already separates each browser and operating-system `family` from its major/minor/patch version fields. Untracked only reads the family fields and ignores all version fields.

Browser and operating-system families are intentionally not enums because UAP's family catalogue is data-driven and can grow. Instead, semantic normalizers remove information that belongs to another dimension or is unnecessarily specific:

```text
Firefox iOS                  -> Firefox
Chrome Mobile iOS            -> Chrome
Chrome Mobile WebView        -> Chrome WebView
Mobile Safari UI/WKWebView   -> Safari WebView
Edge Mobile                  -> Edge
Windows XP                   -> Windows
Windows Server 2003          -> Windows
Mac OS X                     -> macOS
Ubuntu / Debian / Fedora     -> Linux
Chrome OS                    -> ChromeOS
```

Unknown future UAP families are kept as their family name rather than silently becoming `Other`. Device remains intentionally coarse: `desktop`, `mobile`, `tablet`, `bot` or `other`. Laptop-sized devices are folded into `desktop`; Untracked does not collect screen dimensions to split them further.

## IP processing

IP-to-country resolution also uses a Laravel manager/driver setup.

### MaxMind — default

```dotenv
ANALYTICS_IP_DRIVER=maxmind
ANALYTICS_MAXMIND_DATABASE=/absolute/path/to/GeoLite2-Country.mmdb
```

This uses the local MaxMind GeoLite2/GeoIP2 database through `geoip2/geoip2`. The IP never leaves your server. If the database is missing or the address cannot be resolved, country becomes `XX`.

This is the privacy-first production setup.

### ip-api.com

```dotenv
ANALYTICS_IP_DRIVER=ip-api
ANALYTICS_IP_API_URL=http://ip-api.com/json
```

This is convenient for quick testing because it needs no local database, but it sends the visitor IP to an external service. The free ip-api.com endpoint is HTTP-only, rate-limited and not licensed for commercial use. Use it only when those trade-offs are acceptable, or configure a suitable paid/self-hosted compatible endpoint.

## Bots

Bot collection is configured per website.

When disabled, detected bot requests are discarded before any counter is incremented. When enabled, bots are aggressively coarsened to `Bot` / `bot` browser, OS and device values rather than preserving individual crawler identities.

Processed integrations represent bots with the same coarse values; there is no separate visitor or bot identity.

## Privacy model

The core rule is simple: **there is no visitor model.**

That means Untracked should never grow optional sessions, hashed IP tracking, fingerprinting or a "unique visitors" switch. Those features would undermine the reason this project exists.

Untracked is designed to minimize personal data, but software alone cannot make a deployment GDPR compliant. Your hosting, access logs, reverse proxy, external IP resolver, other application data and privacy notice still matter.
