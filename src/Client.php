<?php

namespace Fyennyi\Nominatim;

use Fyennyi\Nominatim\Exception\TransportException;
use Fyennyi\Nominatim\Model\Place;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;

class Client
{
    private const DEFAULT_USER_AGENT = 'fyennyi-nominatim-php/1.0';
    private const BASE_URL = 'https://nominatim.openstreetmap.org/';

    /** @var ClientInterface The HTTP client instance */
    private ClientInterface $http_client;

    /** @var CacheInterface|null The cache instance */
    private ?CacheInterface $cache;

    /** @var string The user agent string */
    private string $user_agent;

    /**
     * Constructor for Nominatim Client
     *
     * @param  ClientInterface|null  $http_client  Optional Guzzle client
     * @param  CacheInterface|null   $cache        Optional PSR-16 cache
     * @param  string                $user_agent   Custom User-Agent string
     */
    public function __construct(?ClientInterface $http_client = null, ?CacheInterface $cache = null, string $user_agent = self::DEFAULT_USER_AGENT)
    {
        $this->http_client = $http_client ?? new GuzzleClient(['base_uri' => self::BASE_URL]);
        $this->cache = $cache;
        $this->user_agent = $user_agent;
    }

    /**
     * Searches for a place by query string or structured address
     *
     * @param  string|array<string, string>  $query   Search query string or structured array
     * @param  array<string, mixed>          $params  Optional query parameters
     * @return PromiseInterface Promise that resolves to array<int, Place>
     */
    public function search(string|array $query, array $params = []) : PromiseInterface
    {
        $query_params = array_merge($params, [
            'format' => 'jsonv2',
        ]);

        if (is_array($query)) {
            $query_params = array_merge($query_params, $query);
        } else {
            $query_params['q'] = $query;
        }

        return $this->requestAsync('GET', 'search', $query_params)
            ->then(function (array $data) {
                return array_map(fn(array $item) => new Place($item), $data);
            });
    }

    /**
     * Performs reverse geocoding for coordinates
     *
     * @param  float                 $lat     Latitude
     * @param  float                 $lon     Longitude
     * @param  array<string, mixed>  $params  Optional query parameters
     * @return PromiseInterface Promise that resolves to Place|null
     */
    public function reverse(float $lat, float $lon, array $params = []) : PromiseInterface
    {
        $query_params = array_merge($params, [
            'lat' => $lat,
            'lon' => $lon,
            'format' => 'jsonv2',
        ]);

        return $this->requestAsync('GET', 'reverse', $query_params)
            ->then(function (array $data) {
                return isset($data['place_id']) ? new Place($data) : null;
            });
    }

    /**
     * Looks up address details for OSM objects
     *
     * @param  array<string>         $osm_ids  List of OSM IDs (e.g. ['R146656', 'N240109189'])
     * @param  array<string, mixed>  $params   Optional query parameters
     * @return PromiseInterface Promise that resolves to array<int, Place>
     */
    public function lookup(array $osm_ids, array $params = []) : PromiseInterface
    {
        $query_params = array_merge($params, [
            'osm_ids' => implode(',', $osm_ids),
            'format' => 'jsonv2',
        ]);

        return $this->requestAsync('GET', 'lookup', $query_params)
            ->then(function (array $data) {
                return array_map(fn(array $item) => new Place($item), $data);
            });
    }

    /**
     * Checks the status of the Nominatim server
     *
     * @return PromiseInterface Promise that resolves to array{status: int, message: string, ...}
     */
    public function status() : PromiseInterface
    {
        return $this->requestAsync('GET', 'status', ['format' => 'json']);
    }

    /**
     * Internal helper to perform asynchronous HTTP requests
     *
     * @param  string                $method  HTTP method (GET, POST, etc.)
     * @param  string                $path    API endpoint path
     * @param  array<string, mixed>  $query   Query parameters
     * @return PromiseInterface Promise that resolves to decoded JSON array
     *
     * @throws TransportException If request fails or JSON decoding fails
     */
    private function requestAsync(string $method, string $path, array $query) : PromiseInterface
    {
        $options = [
            'query'   => $query,
            'headers' => [
                'User-Agent' => $this->user_agent,
                'Accept'     => 'application/json',
            ]
        ];

        return $this->http_client->requestAsync($method, $path, $options)
            ->then(
                function (ResponseInterface $response) {
                    $body = $response->getBody()->getContents();
                    $data = json_decode($body, true);

                    if (JSON_ERROR_NONE !== json_last_error()) {
                        throw new TransportException('Failed to decode JSON response: ' . json_last_error_msg());
                    }

                    if (! is_array($data)) {
                        throw new TransportException('API returned invalid data format');
                    }

                    return $data;
                },
                function (\Throwable $e) {
                    throw new TransportException('HTTP request failed: ' . $e->getMessage(), 0, $e);
                }
            );
    }
}