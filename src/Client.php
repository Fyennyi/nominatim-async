<?php

namespace Fyennyi\Nominatim;

use Fyennyi\AsyncCache\AsyncCacheManager;
use Fyennyi\AsyncCache\CacheOptions;
use Fyennyi\Nominatim\Exception\TransportException;
use Fyennyi\Nominatim\Model\Place;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use React\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class Client
{
    private const DEFAULT_USER_AGENT = 'fyennyi-nominatim-php/1.0';
    private const BASE_URL = 'https://nominatim.openstreetmap.org/';
    private const RATE_LIMIT_KEY = 'nominatim_api';

    /** @var ClientInterface The HTTP client instance */
    private ClientInterface $http_client;

    /** @var AsyncCacheManager The async cache manager instance */
    private AsyncCacheManager $async_cache;

    /** @var CacheInterface The cache adapter instance */
    private CacheInterface $cache;

    /** @var LimiterInterface|null The rate limiter instance */
    private ?LimiterInterface $rate_limiter = null;

    /** @var string The user agent string */
    private string $user_agent;

    /**
     * Constructor for Nominatim Client
     *
     * @param  ClientInterface|null  $http_client  Optional Guzzle client
     * @param  CacheInterface|null  $cache  Optional PSR-16 cache (defaults to InMemory ArrayAdapter)
     * @param  string  $user_agent  Custom User-Agent string
     * @param  LimiterInterface|null  $rate_limiter  Optional Symfony Rate Limiter (defaults to 1 req/sec in-memory)
     */
    public function __construct(
        ?ClientInterface $http_client = null,
        ?CacheInterface $cache = null,
        string $user_agent = self::DEFAULT_USER_AGENT,
        ?LimiterInterface $rate_limiter = null
    )
    {
        $this->http_client = $http_client ?? new GuzzleClient(['base_uri' => self::BASE_URL]);
        $this->user_agent = $user_agent;

        // Setup Cache Manager (Default to ArrayCache if none provided)
        $this->cache = $cache ?? new Psr16Cache(new ArrayAdapter());

        // Setup Rate Limiter (Nominatim Usage Policy: Max 1 request per second)
        $this->rate_limiter = $rate_limiter ?? $this->createRateLimiter(1);

        $this->rebuildAsyncCacheManager();
    }

    /**
     * Searches for a place by query string or structured address
     *
     * @param  string|array<string, string>  $query  Search query string or structured array
     * @param  array<string, mixed>  $params  Optional query parameters
     * @return PromiseInterface Promise that resolves to array<int, Place>
     */
    public function search(string|array $query, array $params = []) : PromiseInterface
    {
        $default_params = ['format' => 'jsonv2'];
        $query_params = array_merge($default_params, $params);

        if (is_array($query)) {
            $query_params = array_merge($query_params, $query);
        } else {
            $query_params['q'] = $query;
        }

        return $this->request('GET', 'search', $query_params)
            ->then(function (array $data) use ($query_params) {
                return $this->processResponse($data, $query_params['format']);
            });
    }

    /**
     * Performs reverse geocoding for coordinates
     *
     * @param  float  $lat  Latitude
     * @param  float  $lon  Longitude
     * @param  array<string, mixed>  $params  Optional query parameters
     * @return PromiseInterface Promise that resolves to Place|null
     */
    public function reverse(float $lat, float $lon, array $params = []) : PromiseInterface
    {
        $default_params = ['format' => 'jsonv2'];
        $query_params = array_merge($default_params, $params, [
            'lat' => $lat,
            'lon' => $lon,
        ]);

        return $this->request('GET', 'reverse', $query_params)
            ->then(function (array $data) use ($query_params) {
                $places = $this->processResponse($data, $query_params['format']);
                return $places[0] ?? null;
            });
    }

    /**
     * Looks up address details for OSM objects
     *
     * @param  array<string>  $osm_ids  List of OSM IDs (e.g. ['R146656', 'N240109189'])
     * @param  array<string, mixed>  $params  Optional query parameters
     * @return PromiseInterface Promise that resolves to array<int, Place>
     */
    public function lookup(array $osm_ids, array $params = []) : PromiseInterface
    {
        $default_params = ['format' => 'jsonv2'];
        $query_params = array_merge($default_params, $params, [
            'osm_ids' => implode(',', $osm_ids),
        ]);

        return $this->request('GET', 'lookup', $query_params)
            ->then(function (array $data) use ($query_params) {
                return $this->processResponse($data, $query_params['format']);
            });
    }

    /**
     * Get details of a place by OSM ID or Place ID
     *
     * @param  array<string, mixed>  $params  Query parameters (e.g., ['osmtype' => 'W', 'osmid' => 123] or ['place_id' => 123])
     * @return PromiseInterface Promise that resolves to Place
     */
    public function details(array $params) : PromiseInterface
    {
        // details endpoint only supports json
        $query_params = array_merge($params, [
            'format' => 'json',
        ]);

        return $this->request('GET', 'details', $query_params)
            ->then(function (array $data) {
                return new Place($data);
            });
    }

    /**
     * Processes the API response based on the format
     *
     * @param  array  $data  Decoded JSON response
     * @param  string  $format  The format requested
     * @return array<int, Place> Array of Place objects
     */
    private function processResponse(array $data, string $format) : array
    {
        if ($format === 'geojson' || $format === 'geocodejson') {
            $features = $data['features'] ?? [];
            return array_map(function (array $feature) use ($format) {
                $properties = $feature['properties'] ?? [];

                // For geocodejson, properties are nested under 'geocoding'
                if ($format === 'geocodejson') {
                    $properties = $properties['geocoding'] ?? $properties;
                }

                if (isset($feature['geometry'])) {
                    $properties['geometry'] = $feature['geometry'];
                }
                return new Place($properties);
            }, $features);
        }

        // Handle single object response (possible in some error cases or specific formats, though typically search returns list)
        // But for consistency, we treat the input as a list of places for standard JSON formats
        // Note: reverse geocoding returns a single object in jsonv2, but we handle that in the reverse method wrapper

        // However, nominatim search returns a list. reverse returns object.
        // Let's standardize: this method returns a LIST of Place objects.

        if (isset($data['place_id']) || isset($data['error'])) {
             // It's a single object (or error)
             // If it's an error, Place constructor might fail or produce empty object. 
             // Ideally we should check for error.
             if (isset($data['error'])) {
                 return [];
             }
             return [new Place($data)];
        }

        return array_map(fn(array $item) => new Place($item), $data);
    }

    /**
     * Checks the status of the Nominatim server
     *
     * @param  string  $format  Output format ('json' or 'text'). Default 'json'.
     * @return PromiseInterface Promise that resolves to array{status: int, message: string, ...} or string
     *
     * @throws \InvalidArgumentException If format is invalid
     */
    public function status(string $format = 'json') : PromiseInterface
    {
        if (! in_array($format, ['json', 'text'])) {
            throw new \InvalidArgumentException('Invalid format. Supported formats: json, text');
        }

        return $this->request('GET', 'status', ['format' => $format], $format === 'json');
    }

    /**
     * Internal helper to perform HTTP requests
     *
     * @param  string  $method  HTTP method (GET, POST, etc.)
     * @param  string  $path  API endpoint path
     * @param  array<string, mixed>  $query  Query parameters
     * @param  bool  $decode_json  Whether to decode the response as JSON
     * @return PromiseInterface Promise that resolves to decoded JSON array or raw string
     *
     * @throws TransportException If request fails or JSON decoding fails
     */
    private function request(string $method, string $path, array $query, bool $decode_json = true) : PromiseInterface
    {
        $cache_key = 'nominatim_' . md5($method . $path . serialize($query));

        $options = new CacheOptions(
            ttl: 86400, // 24 hours logical TTL for Geo Data
            rate_limit_key: self::RATE_LIMIT_KEY,
            serve_stale_if_limited: true
        );

        return $this->async_cache->wrap(
            $cache_key,
            function () use ($method, $path, $query, $decode_json) {
                $http_options = [
                    'query'   => $query,
                    'headers' => [
                        'User-Agent' => $this->user_agent,
                        'Accept'     => $decode_json ? 'application/json' : 'text/plain',
                    ]
                ];

                try {
                    $response = $this->http_client->request($method, $path, $http_options);
                } catch (\Throwable $e) {
                    throw new TransportException('HTTP request failed: ' . $e->getMessage(), 0, $e);
                }

                return $this->parseResponse($response, $decode_json);
            },
            $options
        );
    }

    /**
     * Parses the HTTP response and validates JSON if requested.
     *
     * @param  ResponseInterface  $response
     * @param  bool  $decode_json
     * @return array|string
     *
     * @throws TransportException
     */
    private function parseResponse(ResponseInterface $response, bool $decode_json) : array|string
    {
        $body = $response->getBody()->getContents();

        if (! $decode_json) {
            return $body;
        }

        $data = json_decode($body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new TransportException('Failed to decode JSON response: ' . json_last_error_msg());
        }

        if (! is_array($data)) {
            throw new TransportException('API returned invalid data format');
        }

        return $data;
    }

    /**
     * Sets the minimum interval between requests for the rate limiter
     *
     * @param  int  $seconds  Minimum interval in seconds
     * @return void
     */
    public function setRateLimitInterval(int $seconds) : void
    {
        $this->rate_limiter = $this->createRateLimiter($seconds);
        $this->rebuildAsyncCacheManager();
    }

    /**
     * Sets a custom Symfony rate limiter.
     *
     * @param  LimiterInterface|null  $rate_limiter  Rate limiter instance (null to disable)
     * @return void
     */
    public function setRateLimiter(?LimiterInterface $rate_limiter) : void
    {
        $this->rate_limiter = $rate_limiter;
        $this->rebuildAsyncCacheManager();
    }

    /**
     * Builds a default rate limiter with a fixed window policy.
     */
    private function createRateLimiter(int $seconds) : LimiterInterface
    {
        $seconds = max(1, $seconds);

        $factory = new RateLimiterFactory([
            'id' => self::RATE_LIMIT_KEY,
            'policy' => 'fixed_window',
            'limit' => 1,
            'interval' => sprintf('%d seconds', $seconds),
        ], new InMemoryStorage());

        return $factory->create();
    }

    /**
     * Rebuilds the async cache manager with the current cache and limiter configuration.
     */
    private function rebuildAsyncCacheManager() : void
    {
        $builder = AsyncCacheManager::configure($this->cache);

        if ($this->rate_limiter !== null) {
            $builder->withRateLimiter($this->rate_limiter);
        }

        $this->async_cache = new AsyncCacheManager($builder->build());
    }
}
