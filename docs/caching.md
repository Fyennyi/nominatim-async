# Caching & Rate Limiting

Nominatim's Usage Policy requires clients to respect rate limits and provide descriptive User-Agent headers. This library handles these requirements automatically.

## Caching Strategy

The library utilizes `fyennyi/async-cache-php` to provide non-blocking caching.

### Recommended Setup

```php
<?php

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Fyennyi\Nominatim\Client;

$cache = new Psr16Cache(new FilesystemAdapter());
$client = new Client(null, $cache);
```

By default, all successful requests are cached for **24 hours**. This ensures:
1. Compliance with "no heavy repetitive scraping" policy.
2. Fast response times for repeated coordinates or addresses.

## Rate Limiting

Nominatim limits public API usage to **1 request per second**. 

The library includes an internal rate limiter that:
- Queues requests if they are too frequent.
- Returns stale data from cache (if available) if the rate limit is hit, preventing your application from blocking.

## User-Agent Requirement

Nominatim requires a descriptive User-Agent. By default, the library uses `fyennyi-nominatim-async/1.0`. You can customize it in the constructor:

```php
$client = new Client(null, $cache, 'MyCoolApp/1.0 (contact@example.com)');
```
