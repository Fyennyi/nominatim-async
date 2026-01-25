<?php

namespace Fyennyi\Nominatim\Model;

class Place
{
    private int $place_id;
    private ?string $licence;
    private ?string $osm_type;
    private ?int $osm_id;
    private float $lat;
    private float $lon;
    private string $display_name;
    private ?string $category;
    private ?string $type;
    private ?float $importance;
    private ?int $place_rank;
    private ?int $address_rank;
    private ?array $bounding_box;
    private ?string $icon;
    private ?Address $address;
    private array $extra_tags;
    private array $name_details;
    private ?array $geometry;
    private array $entrances;
    private ?int $parent_place_id;
    private ?string $admin_level;
    private ?string $local_name;
    private array $address_tags;
    private ?string $house_number;
    private ?string $calculated_postcode;
    private ?string $indexed_date;
    private ?float $calculated_importance;
    private ?string $calculated_wikipedia;
    private bool $is_area;
    private ?array $centroid;
    private ?string $address_type;
    private ?string $name;
    private ?string $label;
    private array $admin_levels;
    /** @var array<int, AddressComponent> */
    private array $address_components = [];

    /**
     * Constructor for Place
     *
     * @param  array  $data  Raw API response data
     */
    public function __construct(array $data)
    {
        $this->place_id = (int) ($data['place_id'] ?? 0);
        $this->licence = $data['licence'] ?? null;
        $this->osm_type = $data['osm_type'] ?? null;
        $this->osm_id = isset($data['osm_id']) ? (int) $data['osm_id'] : null;

        // Handle coordinates from either top-level lat/lon or centroid object
        if (isset($data['centroid']['coordinates']) && is_array($data['centroid']['coordinates'])) {
            $this->lon = (float) $data['centroid']['coordinates'][0];
            $this->lat = (float) $data['centroid']['coordinates'][1];
        } else {
            $this->lat = (float) ($data['lat'] ?? 0.0);
            $this->lon = (float) ($data['lon'] ?? 0.0);
        }

        $this->display_name = $data['display_name'] ?? $data['label'] ?? '';

        // Map category/type from jsonv2 (category/type), json (class/type) or geocodejson (osm_key/osm_value)
        $this->category = $data['category'] ?? $data['class'] ?? $data['osm_key'] ?? null;
        $this->type = $data['type'] ?? $data['osm_value'] ?? null;

        $this->importance = isset($data['importance']) ? (float) $data['importance'] : null;

        // Handle rank fields which might have different keys in details response
        $this->place_rank = isset($data['place_rank']) ? (int) $data['place_rank'] : (isset($data['rank_search']) ? (int) $data['rank_search'] : null);
        $this->address_rank = isset($data['address_rank']) ? (int) $data['address_rank'] : (isset($data['rank_address']) ? (int) $data['rank_address'] : null);

        $this->bounding_box = isset($data['boundingbox']) ? array_map('floatval', (array) $data['boundingbox']) : null;
        $this->icon = $data['icon'] ?? null;
        $this->extra_tags = $data['extratags'] ?? $data['extra'] ?? []; // geocodejson uses 'extra'

        // Map names from either namedetails or names
        $this->name_details = $data['namedetails'] ?? $data['names'] ?? [];

        // Map geometry
        $this->geometry = $data['geojson'] ?? $data['geometry'] ?? $data['svg'] ?? $data['geotext'] ?? $data['geokml'] ?? null;
        $this->entrances = $data['entrances'] ?? [];

        // New fields for details endpoint
        $this->parent_place_id = isset($data['parent_place_id']) ? (int) $data['parent_place_id'] : null;
        $this->admin_level = isset($data['admin_level']) ? (string) $data['admin_level'] : null;
        $this->local_name = $data['localname'] ?? null;
        $this->address_tags = $data['addresstags'] ?? [];
        $this->house_number = $data['housenumber'] ?? null;
        $this->calculated_postcode = $data['calculated_postcode'] ?? null;
        $this->indexed_date = $data['indexed_date'] ?? null;
        $this->calculated_importance = isset($data['calculated_importance']) ? (float) $data['calculated_importance'] : null;
        $this->calculated_wikipedia = $data['calculated_wikipedia'] ?? null;
        $this->is_area = (bool) ($data['isarea'] ?? false);
        $this->centroid = $data['centroid'] ?? null;
        $this->address_type = $data['addresstype'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->label = $data['label'] ?? null;
        $this->admin_levels = $data['admin'] ?? [];

        if (isset($data['address']) && is_array($data['address'])) {
            if (array_is_list($data['address'])) {
                $this->address_components = array_map(
                    fn(array $component) => new AddressComponent($component),
                    $data['address']
                );
                $this->address = null;
            } else {
                $this->address = new Address($data['address']);
            }
        } else {
            // Try to build address from flat GeocodeJSON properties
            $address_params = [];
            $potential_keys = ['housenumber', 'street', 'locality', 'district', 'postcode', 'city', 'county', 'state', 'country'];
            foreach ($potential_keys as $key) {
                if (isset($data[$key])) {
                    // Map 'housenumber' to 'house_number' for consistency with Address model keys if needed
                    // Address model uses 'house_number', but constructs from array keys.
                    // Let's check Address model logic. It looks for 'house_number'.
                    $target_key = ($key === 'housenumber') ? 'house_number' : $key;
                    $address_params[$target_key] = $data[$key];
                }
            }
            if (! empty($address_params)) {
                $this->address = new Address($address_params);
            } else {
                $this->address = null;
            }
        }
    }

    /**
     * Get the Nominatim Place ID
     *
     * @return int The place ID
     */
    public function getPlaceId() : int
    {
        return $this->place_id;
    }

    /**
     * Get the license string
     *
     * @return string|null The license string
     */
    public function getLicence() : ?string
    {
        return $this->licence;
    }

    /**
     * Get the OSM type (node, way, relation)
     *
     * @return string|null The OSM type
     */
    public function getOsmType() : ?string
    {
        return $this->osm_type;
    }

    /**
     * Get the OSM ID
     *
     * @return int|null The OSM ID
     */
    public function getOsmId() : ?int
    {
        return $this->osm_id;
    }

    /**
     * Get the latitude
     *
     * @return float The latitude
     */
    public function getLat() : float
    {
        return $this->lat;
    }

    /**
     * Get the longitude
     *
     * @return float The longitude
     */
    public function getLon() : float
    {
        return $this->lon;
    }

    /**
     * Get the display name (full address)
     *
     * @return string The full address
     */
    public function getDisplayName() : string
    {
        return $this->display_name;
    }

    /**
     * Get the category (formerly class)
     *
     * @return string|null The category
     */
    public function getCategory() : ?string
    {
        return $this->category;
    }

    /**
     * Get the type (value of the main tag)
     *
     * @return string|null The type
     */
    public function getType() : ?string
    {
        return $this->type;
    }

    /**
     * Get the importance score
     *
     * @return float|null The importance score
     */
    public function getImportance() : ?float
    {
        return $this->importance;
    }

    /**
     * Get the search rank
     *
     * @return int|null The search rank
     */
    public function getPlaceRank() : ?int
    {
        return $this->place_rank;
    }

    /**
     * Get the address rank
     *
     * @return int|null The address rank
     */
    public function getAddressRank() : ?int
    {
        return $this->address_rank;
    }

    /**
     * Get the bounding box [min_lat, max_lat, min_lon, max_lon]
     *
     * @return float[]|null The bounding box
     */
    public function getBoundingBox() : ?array
    {
        return $this->bounding_box;
    }

    /**
     * Get the icon URL
     *
     * @return string|null The icon URL
     */
    public function getIcon() : ?string
    {
        return $this->icon;
    }

    /**
     * Get the Address object
     *
     * @return Address|null The address object
     */
    public function getAddress() : ?Address
    {
        return $this->address;
    }

    /**
     * Get extra tags (e.g. wikidata, website)
     *
     * @return array The extra tags
     */
    public function getExtraTags() : array
    {
        return $this->extra_tags;
    }

    /**
     * Get localized names
     *
     * @return array The name details
     */
    public function getNameDetails() : array
    {
        return $this->name_details;
    }

    /**
     * Get geometry (GeoJSON, SVG, KML, etc.)
     *
     * @return array|string|null The geometry
     */
    public function getGeometry() : array|string|null
    {
        return $this->geometry;
    }

    /**
     * Get entrances
     *
     * @return array The entrances
     */
    public function getEntrances() : array
    {
        return $this->entrances;
    }

    /**
     * Get parent place ID
     *
     * @return int|null The parent place ID
     */
    public function getParentPlaceId() : ?int
    {
        return $this->parent_place_id;
    }

    /**
     * Get administrative level
     *
     * @return string|null The administrative level
     */
    public function getAdminLevel() : ?string
    {
        return $this->admin_level;
    }

    /**
     * Get local name
     *
     * @return string|null The local name
     */
    public function getLocalName() : ?string
    {
        return $this->local_name;
    }

    /**
     * Get address tags
     *
     * @return array The address tags
     */
    public function getAddressTags() : array
    {
        return $this->address_tags;
    }

    /**
     * Get house number
     *
     * @return string|null The house number
     */
    public function getHouseNumber() : ?string
    {
        return $this->house_number;
    }

    /**
     * Get calculated postcode
     *
     * @return string|null The calculated postcode
     */
    public function getCalculatedPostcode() : ?string
    {
        return $this->calculated_postcode;
    }

    /**
     * Get indexed date
     *
     * @return string|null The indexed date
     */
    public function getIndexedDate() : ?string
    {
        return $this->indexed_date;
    }

    /**
     * Get calculated importance
     *
     * @return float|null The calculated importance score
     */
    public function getCalculatedImportance() : ?float
    {
        return $this->calculated_importance;
    }

    /**
     * Get calculated Wikipedia tag
     *
     * @return string|null The Wikipedia tag
     */
    public function getCalculatedWikipedia() : ?string
    {
        return $this->calculated_wikipedia;
    }

    /**
     * Check if place is an area
     *
     * @return bool True if it's an area
     */
    public function isArea() : bool
    {
        return $this->is_area;
    }

    /**
     * Get centroid coordinates
     *
     * @return array|null The centroid object
     */
    public function getCentroid() : ?array
    {
        return $this->centroid;
    }

    /**
     * Get address type
     *
     * @return string|null The address type
     */
    public function getAddressType() : ?string
    {
        return $this->address_type;
    }

    /**
     * Get name
     *
     * @return string|null The name
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * Get label
     *
     * @return string|null The label
     */
    public function getLabel() : ?string
    {
        return $this->label;
    }

    /**
     * Get administrative levels hierarchy
     *
     * @return array The admin levels
     */
    public function getAdminLevels() : array
    {
        return $this->admin_levels;
    }

    /**
     * Get address components list (from details endpoint)
     *
     * @return array<int, AddressComponent> List of address components
     */
    public function getAddressComponents() : array
    {
        return $this->address_components;
    }
}
