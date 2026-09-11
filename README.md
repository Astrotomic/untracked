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
website_id
date
metric
value
count
```

One request can increment counters such as:

```text
2026-09-11 | path    | /blog/example | +1
2026-09-11 | country | DE            | +1
2026-09-11 | browser | Firefox       | +1
2026-09-11 | os      | Linux         | +1
2026-09-11 | device  | desktop       | +1
2026-09-11 | format  | markdown      | +1
```

Those counters are independent. Untracked cannot later answer which German request used Firefox, which device opened a specific page, or whether two requests came from the same person.

The analytics table intentionally has no `id`, `created_at` or `updated_at`. Exact request times would make otherwise independent counters correlatable again.

## What is not stored

- IP addresses
- full User-Agent strings
- exact timestamps
- query strings
- referrers
- screen sizes
- language
- sessions
- fingerprints
- visitor IDs
- unique visitors

For direct browser collection, the HTTP server inevitably receives the request IP and User-Agent while handling the request. Untracked only uses coarse request information in memory and never writes those raw values to its analytics database. For server-side collection, even that can be avoided by sending the already normalized values from the source website.

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

## Collecting a request

Each website gets a public UUID. Send a `POST` request to:

```text
/api/websites/{uuid}/collect
```

The only required property is the path:

```json
{
  "path": "/blog/example"
}
```

For frontend collection, Untracked can derive coarse browser, OS and device families from the request User-Agent in memory. Country can be read from coarse infrastructure headers such as Cloudflare's `CF-IPCountry`. No IP geolocation is performed by Untracked itself.

For server-side collection, send already normalized values and keep raw IP/User-Agent data on the source server:

```json
{
  "path": "/blog/example",
  "country": "DE",
  "browser": "Firefox",
  "os": "Linux",
  "device": "desktop",
  "format": "markdown"
}
```

Paths are reduced to the pathname before storage, so query strings are discarded.

If your routes can contain personal or secret values, normalize those paths before sending them. Untracked deliberately does not retain a raw event that could be fixed afterwards.

## Bots

Bot collection is configured per website.

When disabled, detected bot requests are discarded before any counter is incremented. When enabled, bots are aggressively coarsened to `Bot` / `bot` browser, OS and device values rather than preserving individual crawler identities.

## Privacy model

The core rule is simple: **there is no visitor model.**

That means Untracked should never grow optional sessions, hashed IP tracking, fingerprinting or a "unique visitors" switch. Those features would undermine the reason this project exists.

Untracked is designed to minimize personal data, but software alone cannot make a deployment GDPR compliant. Your hosting, access logs, reverse proxy, other application data and privacy notice still matter.
