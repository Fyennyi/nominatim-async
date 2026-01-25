# Place Details

If you already have a `place_id` or other identifiers, you can fetch the most detailed information available for that specific point.

## Usage

=== "PHP Model"

    ```php
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

=== "Raw API Response"

    ```json
    {
      "place_id": 123456,
      "osm_type": "W",
      "osm_id": 98765,
      "category": "highway",
      "type": "residential",
      "admin_level": 15,
      "localname": "Khreshchatyk St",
      "importance": 0.1,
      "rank_address": 26
    }
    ```

## Identifier Types

The `details` method supports various parameters defined by Nominatim:
- `place_id`
- `osm_type` & `osm_id`
- `class`