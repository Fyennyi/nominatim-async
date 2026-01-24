<?php

/**
 * CLI wrapper for geolocation lookup using nominatim-async
 * 
 * Usage:
 *   Reverse geocoding: php scripts/geo_lookup.php reverse <lat> <lon>
 *   Text search: php scripts/geo_lookup.php search "<query>"
 *   
 * Output: JSON with location data
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Fyennyi\Nominatim\Client;
use Fyennyi\Nominatim\Model\Place;
use GuzzleHttp\Promise\Utils;

function showUsage() {
    echo json_encode([
        'error' => 'Invalid usage',
        'usage' => [
            'Reverse geocoding: php scripts/geo_lookup.php reverse <lat> <lon>',
            'Text search: php scripts/geo_lookup.php search "<query>"'
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

function createResultFromPlace(Place $place, string $matchedBy, ?float $similarity = null): array {
    $address = $place->getAddress();

    return [
        'uid' => $place->getOsmId() ? $place->getOsmType() . $place->getOsmId() : 'unknown',
        'name' => $place->getDisplayName(),
        'type' => $place->getType(),
        'category' => $place->getCategory(),
        'state' => $address ? $address->getState() : null,
        'district_name' => $address ? $address->getCounty() : ($address ? $address->getDistrict() : null),
        'city_name' => $address ? $address->getCity() : ($address ? $address->getTown() : null),
        'country' => $address ? $address->getCountry() : null,
        'postcode' => $address ? $address->getPostcode() : null,
        'matched_by' => $matchedBy,
        'similarity' => $similarity,
        'importance' => $place->getImportance(),
        'place_rank' => $place->getPlaceRank(),
        'coordinates' => [
            'lat' => $place->getLat(),
            'lon' => $place->getLon(),
        ],
        'boundingbox' => $place->getBoundingBox(),
        'osm_type' => $place->getOsmType(),
        'osm_id' => $place->getOsmId(),
    ];
}

function performReverseSearch(float $lat, float $lon, Client $client): array {
    // Validate coordinates
    if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
        throw new InvalidArgumentException('Coordinates out of range');
    }

    $promise = $client->reverse($lat, $lon, ['addressdetails' => 1]);
    $place = $promise->wait();

    if ($place === null) {
        throw new RuntimeException('Location not found for coordinates');
    }

    return createResultFromPlace(
        $place,
        'nominatim_reverse_geocoding',
        1.0
    );
}

function performTextSearch(string $query, Client $client): array {
    if (empty(trim($query))) {
        throw new InvalidArgumentException('Query cannot be empty');
    }

    $promise = $client->search($query, [
        'addressdetails' => 1,
        'limit' => 1,
        'extratags' => 1,
        'namedetails' => 1
    ]);

    $places = $promise->wait();

    if (empty($places)) {
        throw new RuntimeException("No results found for query: {$query}");
    }

    $place = $places[0];

    return createResultFromPlace(
        $place,
        'nominatim_text_search',
        null // Text search doesn't provide similarity scores
    );
}

// Main execution
try {
    if ($argc < 3) {
        showUsage();
        exit(1);
    }

    $mode = strtolower($argv[1]);
    $client = new Client();

    switch ($mode) {
        case 'reverse':
            if ($argc < 4) {
                throw new InvalidArgumentException('Reverse mode requires lat and lon parameters');
            }

            $lat = (float) $argv[2];
            $lon = (float) $argv[3];

            if (! is_numeric($lat) || ! is_numeric($lon)) {
                throw new InvalidArgumentException('Invalid coordinates format');
            }

            $result = performReverseSearch($lat, $lon, $client);
            break;

        case 'search':
            if ($argc < 3) {
                throw new InvalidArgumentException('Search mode requires query parameter');
            }

            $query = $argv[2];
            $result = performTextSearch($query, $client);
            break;

        default:
            throw new InvalidArgumentException("Invalid mode: {$mode}. Use 'reverse' or 'search'");
    }

    // Add execution metadata
    $result['metadata'] = [
        'search_mode' => $mode,
        'timestamp' => date('c'),
        'api_version' => 'nominatim-async-v1'
    ];

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'metadata' => [
            'search_mode' => $argv[1] ?? 'unknown',
            'timestamp' => date('c'),
            'api_version' => 'nominatim-async-v1'
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit(1);
}
