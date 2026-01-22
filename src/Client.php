<?php

namespace Fyennyi\Nominatim;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;

class Client
{
    private const DEFAULT_USER_AGENT = 'fyennyi-nominatim-php/1.0';
    private const BASE_URL = 'https://nominatim.openstreetmap.org/';

    private ClientInterface $httpClient;
    private ?CacheInterface $cache;
    private string $userAgent;

    public function __construct(
        ?ClientInterface $httpClient = null,
        ?CacheInterface $cache = null,
        string $userAgent = self::DEFAULT_USER_AGENT
    ) {
        $this->httpClient = $httpClient ?? new GuzzleClient(['base_uri' => self::BASE_URL]);
        $this->cache = $cache;
        $this->userAgent = $userAgent;
    }

    /**
     * Reverse geocoding
     *
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @param array<string, mixed> $params Optional parameters (zoom, addressdetails, etc.)
     * @return PromiseInterface Resolves to array|null
     */
    public function reverse(float $lat, float $lon, array $params = []): PromiseInterface
    {
        $defaultParams = [
            'lat' => $lat,
            'lon' => $lon,
            'format' => 'jsonv2',
        ];
        
        $query = array_merge($defaultParams, $params);

        return $this->requestAsync('GET', 'reverse', $query);
    }

    /**
     * Search
     *
     * @param string $query Query string
     * @param array<string, mixed> $params Optional parameters
     * @return PromiseInterface Resolves to array of results
     */
    public function search(string $query, array $params = []): PromiseInterface
    {
        $defaultParams = [
            'q' => $query,
            'format' => 'jsonv2',
        ];

        $query = array_merge($defaultParams, $params);

        return $this->requestAsync('GET', 'search', $query);
    }

    /**
     * Lookup by OSM IDs
     *
     * @param array<string> $osmIds List of OSM IDs (e.g. ['R123', 'W456'])
     * @param array<string, mixed> $params Optional parameters
     * @return PromiseInterface Resolves to array of results
     */
    public function lookup(array $osmIds, array $params = []): PromiseInterface
    {
        $defaultParams = [
            'osm_ids' => implode(',', $osmIds),
            'format' => 'jsonv2',
        ];

        $query = array_merge($defaultParams, $params);

        return $this->requestAsync('GET', 'lookup', $query);
    }

    private function requestAsync(string $method, string $endpoint, array $query): PromiseInterface
    {
        // TODO: Implement Caching & Rate Limiting here
        
        $options = [
            'query' => $query,
            'headers' => [
                'User-Agent' => $this->userAgent,
            ]
        ];

        return $this->httpClient->requestAsync($method, $endpoint, $options)
            ->then(function (ResponseInterface $response) {
                return json_decode($response->getBody()->getContents(), true);
            });
    }
}
