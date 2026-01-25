# Reverse Geocoding

Reverse geocoding allows you to find an address based on latitude and longitude coordinates.

## Basic Usage

=== "PHP Model"

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

=== "Raw API Response"

    ```json
    {
      "place_id": 123456789,
      "osm_type": "node",
      "osm_id": 987654321,
      "lat": "50.4501000",
      "lon": "30.5234000",
      "display_name": "Maidan Nezalezhnosti, Kyiv, 01001, Ukraine",
      "address": {
        "road": "Maidan Nezalezhnosti",
        "city": "Kyiv",
        "postcode": "01001",
        "country": "Ukraine",
        "country_code": "ua"
      }
    }
    ```

## Options

The `reverse` method accepts a third argument for additional Nominatim parameters:

- **`zoom`**: Level of detail (0-18).
- **`addressdetails`**: Include address breakdown.
- **`accept-language`**: Preferred language for the result.
