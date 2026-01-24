# Place Details

If you already have a `place_id` or other identifiers, you can fetch the most detailed information available for that specific point.

## Usage

```php
<?php

$client->details(['place_id' => 123456])
    ->then(function ($place) {
        if ($place) {
            echo "Category: " . $place->getCategory() . "\n";
            echo "Type: " . $place->getType() . "\n";
            echo "Importance: " . $place->getImportance() . "\n";
        }
    })
    ->wait();
```

## Identifier Types

The `details` method supports various parameters defined by Nominatim:
- `place_id`
- `osm_type` & `osm_id`
- `class`
