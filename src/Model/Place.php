<?php

namespace Fyennyi\Nominatim\Model;

class Place
{
    private int $placeId;
    private ?string $osmType;
    private ?int $osmId;
    private float $lat;
    private float $lon;
    private string $displayName;
    private ?Address $address;
    private array $extraTags;

    public function __construct(array $data)
    {
        $this->placeId = (int) ($data['place_id'] ?? 0);
        $this->osmType = $data['osm_type'] ?? null;
        $this->osmId = isset($data['osm_id']) ? (int) $data['osm_id'] : null;
        $this->lat = (float) ($data['lat'] ?? 0.0);
        $this->lon = (float) ($data['lon'] ?? 0.0);
        $this->displayName = $data['display_name'] ?? '';
        $this->extraTags = $data['extratags'] ?? [];

        if (isset($data['address']) && is_array($data['address'])) {
            $this->address = new Address($data['address']);
        } else {
            $this->address = null;
        }
    }

    public function getPlaceId(): int
    {
        return $this->placeId;
    }

    public function getOsmType(): ?string
    {
        return $this->osmType;
    }

    public function getOsmId(): ?int
    {
        return $this->osmId;
    }

    public function getLat(): float
    {
        return $this->lat;
    }

    public function getLon(): float
    {
        return $this->lon;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function getExtraTags(): array
    {
        return $this->extraTags;
    }
}
