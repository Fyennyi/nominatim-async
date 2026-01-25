# Address Lookup

The `lookup` method allows you to fetch details for multiple OpenStreetMap objects simultaneously using their OSM IDs.

## Usage

=== "PHP Model"

    ```php
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

=== "Raw API Response"

    ```json
    [
      {
        "place_id": 123456,
        "osm_type": "relation",
        "osm_id": 146656,
        "display_name": "Kyiv, Ukraine",
        "category": "boundary",
        "type": "administrative"
      },
      {
        "place_id": 789012,
        "osm_type": "node",
        "osm_id": 240109189,
        "display_name": "Some Point, Kyiv, Ukraine"
      }
    ]
    ```

## OSM ID Format

OSM IDs should be prefixed with the type character:
- **N**: Node
- **W**: Way
- **R**: Relation