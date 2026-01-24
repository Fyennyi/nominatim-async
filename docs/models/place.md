# Place Model

The `Place` object represents a location returned by the Nominatim API.

## Methods

### Standard Fields
- **`getPlaceId()`**: Internal Nominatim ID.
- **`getOsmId()`**: OpenStreetMap ID.
- **`getOsmType()`**: Type of OSM object (Node, Way, Relation).
- **`getLat()` / `getLon()`**: Coordinates.
- **`getDisplayName()`**: Full formatted address string.

### Metadata
- **`getCategory()`**: e.g., 'boundary', 'place'.
- **`getType()`**: e.g., 'administrative', 'city'.
- **`getImportance()`**: Search relevance score.
- **`getPlaceRank()`**: Administrative hierarchy level.

### Structured Data
- **`getAddress()`**: Returns an [`Address`](address.md) object.
- **`getBoundingBox()`**: Array of coordinates defining the area.
- **`getCentroid()`**: Geometry of the center point.
- **`getGeometry()`**: Full GeoJSON geometry (if requested).
