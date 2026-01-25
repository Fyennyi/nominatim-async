# Searching (Geocoding)

Forward geocoding allows you to convert a text address into geographic coordinates.

## Simple Search

=== "PHP Model"

    ```php
    $promise = $client->search('Kyiv, Ukraine');

    $promise->then(function ($places) {
        foreach ($places as $place) {
            echo $place->getDisplayName() . "\n";
            echo "Lat: " . $place->getLat() . ", Lon: " . $place->getLon() . "\n";
        }
    })->wait();
    ```

=== "Raw API Response (Reference)"

    ```json
    [
      {
        "place_id": 123456789,
        "licence": "Data © OpenStreetMap contributors, ODbL 1.0. http://osm.org/copyright",
        "osm_type": "relation",
        "osm_id": 421866,
        "lat": "50.4500336",
        "lon": "30.5241361",
        "display_name": "Kyiv, Ukraine",
        "category": "boundary",
        "type": "administrative"
      }
    ]
    ```

## Structured Search

You can also pass an array of parameters for more precise results.

```php
<?php

$query = [
    'street' => 'Khreshchatyk',
    'city' => 'Kyiv',
    'country' => 'Ukraine'
];

$params = [
    'addressdetails' => 1,
    'limit' => 5
];

$places = $client->search($query, $params)->wait();
```
