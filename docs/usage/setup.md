# Basic Setup

To get started, simply instantiate the `Client`.

```php
<?php

require 'vendor/autoload.php';

use Fyennyi\Nominatim\Client;

$client = new Client();
```

## Advanced Configuration

The constructor accepts several optional parameters:

```php
public function __construct(
    ?ClientInterface $client = null,
    ?CacheInterface $cache = null,
    string $user_agent = 'fyennyi-nominatim-async/1.0'
)
```

- **`$client`**: A custom Guzzle client (e.g., for setting custom timeouts or proxies).
- **`$cache`**: A PSR-16 cache implementation for caching API responses.
- **`$user_agent`**: A custom User-Agent string (Nominatim requires a descriptive User-Agent).
