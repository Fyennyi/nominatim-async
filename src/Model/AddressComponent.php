<?php

namespace Fyennyi\Nominatim\Model;

class AddressComponent
{
    private string $local_name;
    private ?int $osm_id;
    private ?string $osm_type;
    private ?string $class;
    private ?string $type;
    private ?int $admin_level;
    private int $rank_address;
    private bool $is_address;

    /**
     * Constructor for AddressComponent
     *
     * @param  array<string, mixed>  $data  Raw component data
     */
    public function __construct(array $data)
    {
        $this->local_name = (string) ($data['localname'] ?? '');
        $this->osm_id = isset($data['osm_id']) ? (int) $data['osm_id'] : null;
        $this->osm_type = isset($data['osm_type']) ? (string) $data['osm_type'] : null;
        $this->class = isset($data['class']) ? (string) $data['class'] : null;
        $this->type = isset($data['type']) ? (string) $data['type'] : null;
        $this->admin_level = isset($data['admin_level']) ? (int) $data['admin_level'] : null;
        $this->rank_address = (int) ($data['rank_address'] ?? 0);
        $this->is_address = (bool) ($data['isaddress'] ?? false);
    }

    public function getLocalName() : string
    {
        return $this->local_name;
    }

    public function getOsmId() : ?int
    {
        return $this->osm_id;
    }

    public function getOsmType() : ?string
    {
        return $this->osm_type;
    }

    public function getClass() : ?string
    {
        return $this->class;
    }

    public function getType() : ?string
    {
        return $this->type;
    }

    public function getAdminLevel() : ?int
    {
        return $this->admin_level;
    }

    public function getRankAddress() : int
    {
        return $this->rank_address;
    }

    public function isAddress() : bool
    {
        return $this->is_address;
    }
}
