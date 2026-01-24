# Nominatim Async PHP Client

[![Latest Stable Version](https://img.shields.io/packagist/v/fyennyi/nominatim-async.svg?label=Packagist&logo=packagist)](https://packagist.org/packages/fyennyi/nominatim-async)
[![Total Downloads](https://img.shields.io/packagist/dt/fyennyi/nominatim-async.svg?label=Downloads&logo=packagist)](https://packagist.org/packages/fyennyi/nominatim-async)
[![License](https://img.shields.io/packagist/l/fyennyi/nominatim-async.svg?label=Licence&logo=open-source-initiative)](https://packagist.org/packages/fyennyi/nominatim-async)
[![Tests](https://img.shields.io/github/actions/workflow/status/Fyennyi/nominatim-async/phpunit.yml?label=Tests&logo=github)](https://github.com/Fyennyi/nominatim-async/actions/workflows/phpunit.yml)
[![Test Coverage](https://img.shields.io/codecov/c/github/Fyennyi/nominatim-async?label=Test%20Coverage&logo=codecov)](https://app.codecov.io/gh/Fyennyi/nominatim-async)

An asynchronous PHP client for the [Nominatim](https://nominatim.org/) API (OpenStreetMap), built on top of Guzzle Promises. This library allows you to perform forward and reverse geocoding, address lookups, and more, without blocking your application's execution flow.

## Installation

To install the Nominatim Async Client, run the following command in your terminal:

```bash
composer require fyennyi/nominatim-async
```

## Usage

### Basic Setup

First, create a client instance. You can optionally pass a custom Guzzle client and a PSR-16 cache adapter.

```php
require 'vendor/autoload.php';

use Fyennyi\Nominatim\Client;

$client = new Client();
```

### Searching (Geocoding)

Search for a place by query string or structured parameters.

```php
// Simple query
$promise = $client->search('Kyiv, Ukraine');

$promise->then(function ($places) {
    foreach ($places as $place) {
        echo $place->getDisplayName() . "\n";
        echo "Lat: " . $place->getLat() . ", Lon: " . $place->getLon() . "\n";
    }
})->wait();

// Structured query with extra parameters
$promise = $client->search([
    'street' => 'Khreshchatyk',
    'city' => 'Kyiv',
    'country' => 'Ukraine'
], [
    'addressdetails' => 1,
    'limit' => 5
]);

$places = $promise->wait();
```

### Reverse Geocoding

Find a place by its coordinates (latitude and longitude).

```php
$lat = 50.4501;
$lon = 30.5234;

$client->reverse($lat, $lon, ['zoom' => 18])
    ->then(function ($place) {
        if ($place) {
            echo "Found: " . $place->getDisplayName() . "\n";
            if ($address = $place->getAddress()) {
                echo "City: " . $address->getCity() . "\n";
            }
        }
    })
    ->wait();
```

### Address Lookup

Look up details for specific OSM objects (Nodes, Ways, Relations).

```php
$osm_ids = ['R146656', 'N240109189'];

$client->lookup($osm_ids, ['addressdetails' => 1])
    ->then(function ($places) {
        foreach ($places as $place) {
            echo "ID: " . $place->getOsmType() . $place->getOsmId() . "\n";
            echo "Name: " . $place->getDisplayName() . "\n";
        }
    })
    ->wait();
```

### Place Details

Get detailed information about a place by its ID.

```php
$client->details(['place_id' => 123456])
    ->then(function ($place) {
        echo "Category: " . $place->getCategory() . "\n";
        echo "Type: " . $place->getType() . "\n";
    })
    ->wait();
```

### Server Status

Check the status of the Nominatim server.

```php
// Get status as array
$client->status('json')
    ->then(function ($status) {
        echo "Status: " . $status['status'] . "\n";
        echo "Message: " . $status['message'] . "\n";
    })
    ->wait();

// Get status as text
$text = $client->status('text')->wait();
echo $text; // "OK"
```

## Asynchronous Operations

The main advantage of this library is the ability to run multiple requests concurrently using Guzzle Promises.

```php
use GuzzleHttp\Promise\Utils;

$promises = [
    'kyiv' => $client->search('Kyiv'),
    'lviv' => $client->search('Lviv'),
    'reverse' => $client->reverse(50.45, 30.52),
];

Utils::all($promises)->then(function ($results) {
    $kyiv_places = $results['kyiv'];
    $lviv_places = $results['lviv'];
    $reverse_place = $results['reverse'];

    echo "Found " . count($kyiv_places) . " places in Kyiv.\n";
    echo "Found " . count($lviv_places) . " places in Lviv.\n";
    echo "Reverse result: " . ($reverse_place ? $reverse_place->getDisplayName() : 'None') . "\n";
})->wait();
```

## Models

### Place

The `Place` object represents a location returned by the API. It provides comprehensive getters for all standard Nominatim fields:

- `getPlaceId()`, `getOsmId()`, `getOsmType()`, `getLicence()`
- `getLat()`, `getLon()`, `getDisplayName()`, `getLabel()`, `getLocalName()`, `getName()`
- `getCategory()`, `getType()`, `getAddressType()`, `getImportance()`, `getCalculatedImportance()`
- `getPlaceRank()`, `getAddressRank()`, `getAdminLevel()`, `getAdminLevels()`
- `getBoundingBox()`, `getIcon()`, `getCentroid()`, `getGeometry()`, `getEntrances()`
- `getAddress()` (returns an `Address` object), `getAddressTags()`, `getExtraTags()`, `getNameDetails()`
- `getParentPlaceId()`, `getHouseNumber()`, `getCalculatedPostcode()`, `getIndexedDate()`, `getCalculatedWikipedia()`, `isArea()`

### Address

The `Address` object provides easy access to address components:

- `getCountry()`, `getCountryCode()`, `getContinent()`, `getState()`, `getIso31662Lvl4()`, `getRegion()`, `getStateDistrict()`, `getCounty()`
- `getMunicipality()`, `getCity()`, `getTown()`, `getVillage()`, `getSettlement()`, `getCityDistrict()`, `getDistrict()`, `getBorough()`
- `getSuburb()`, `getSubdivision()`, `getHamlet()`, `getCroft()`, `getIsolatedDwelling()`, `getNeighbourhood()`, `getAllotments()`, `getQuarter()`, `getCityBlock()`, `getResidential()`
- `getRoad()`, `getHouseNumber()`, `getHouseName()`, `getPostcode()`
- `getFarm()`, `getFarmyard()`, `getIndustrial()`, `getCommercial()`, `getRetail()`
- `getEmergency()`, `getHistoric()`, `getMilitary()`, `getNatural()`, `getLanduse()`, `getPlace()`, `getRailway()`, `getManMade()`, `getAerialway()`, `getBoundary()`, `getAmenity()`, `getAeroway()`, `getClub()`, `getCraft()`, `getLeisure()`, `getOffice()`, `getMountainPass()`, `getShop()`, `getTourism()`, `getBridge()`, `getTunnel()`, `getWaterway()`
- `get(string $key)`, `toArray()`

## Caching

This library utilizes `fyennyi/async-cache-php` to provide seamless, non-blocking caching capabilities.
You can pass any PSR-16 compatible cache implementation (e.g., from `symfony/cache`) to the `Client` constructor.

```php
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

$psr16Cache = new Psr16Cache(new FilesystemAdapter());
$client = new Client(null, $psr16Cache);
```

By default, requests are cached for **24 hours** to reduce load on the Nominatim servers and ensure compliance with usage policies. The client also implements rate limiting automatically.

## Contributing

Contributions are welcome and appreciated! Here's how you can contribute:

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

Please make sure to update tests as appropriate and adhere to the existing coding style.

## License

This library is licensed under the CSSM Unlimited License v2.0 (CSSM-ULv2). See the [LICENSE](LICENSE) file for details.
