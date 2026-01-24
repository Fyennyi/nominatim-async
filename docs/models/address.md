# Address Model

The `Address` object provides structured access to address components.

## Methods

### Regional Data
- **`getCountry()`**: Full country name.
- **`getCountryCode()`**: 2-letter code (e.g., 'ua').
- **`getState()`**: Oblast or state name.
- **`getRegion()`**: Administrative region.
- **`getDistrict()`**: Raion or district.

### Local Data
- **`getCity()`** / **`getTown()`** / **`getVillage()`**: Settlement name.
- **`getMunicipality()`**: Community or hromada name.
- **`getSuburb()`**: City district or suburb.
- **`getNeighbourhood()`**: Local neighbourhood.

### Street Data
- **`getRoad()`**: Street name.
- **`getHouseNumber()`**: Building number.
- **`getPostcode()`**: Postal code.

### Generic Access
- **`get(string $key)`**: Access any field by name.
- **`toArray()`**: Get all address components as an associative array.
