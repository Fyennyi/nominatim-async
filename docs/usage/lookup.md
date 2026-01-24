# Address Lookup

The `lookup` method allows you to fetch details for multiple OpenStreetMap objects simultaneously using their OSM IDs.

## Usage

```php
<?php

// Supports multiple IDs at once
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

## OSM ID Format

OSM IDs should be prefixed with the type character:
- **N**: Node
- **W**: Way
- **R**: Relation
