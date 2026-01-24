# Searching (Geocoding)

Forward geocoding allows you to convert a text address into geographic coordinates.

## Simple Search

```php
<?php

$promise = $client->search('Kyiv, Ukraine');

$promise->then(function ($places) {
    foreach ($places as $place) {
        echo $place->getDisplayName() . "\n";
        echo "Lat: " . $place->getLat() . ", Lon: " . $place->getLon() . "\n";
    }
})->wait();
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
