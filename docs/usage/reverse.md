# Reverse Geocoding

Reverse geocoding allows you to find an address based on latitude and longitude coordinates.

## Basic Usage

```php
<?php

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

## Options

The `reverse` method accepts a third argument for additional Nominatim parameters:

- **`zoom`**: Level of detail (0-18).
- **`addressdetails`**: Include address breakdown.
- **`accept-language`**: Preferred language for the result.
