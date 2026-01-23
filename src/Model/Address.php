<?php

namespace Fyennyi\Nominatim\Model;

class Address
{
    private array $details;

    /**
     * Constructor for Address
     *
     * @param  array  $details  Raw address details array
     */
    public function __construct(array $details)
    {
        $this->details = $details;
    }

    /**
     * Get a specific address detail by key
     *
     * @param  string  $key  The key to look up
     * @return string|null The value or null if not found
     */
    public function get(string $key): ?string
    {
        return $this->details[$key] ?? null;
    }

    /**
     * Get country name
     *
     * @return string|null The country name
     */
    public function getCountry(): ?string
    {
        return $this->get('country');
    }

    /**
     * Get country code (iso-2)
     *
     * @return string|null The country code
     */
    public function getCountryCode(): ?string
    {
        return $this->get('country_code');
    }

    /**
     * Get continent
     *
     * @return string|null The continent
     */
    public function getContinent(): ?string
    {
        return $this->get('continent');
    }

    /**
     * Get state
     *
     * @return string|null The state
     */
    public function getState(): ?string
    {
        return $this->get('state');
    }

    /**
     * Get region
     *
     * @return string|null The region
     */
    public function getRegion(): ?string
    {
        return $this->get('region');
    }

    /**
     * Get state district
     *
     * @return string|null The state district
     */
    public function getStateDistrict(): ?string
    {
        return $this->get('state_district');
    }

    /**
     * Get county
     *
     * @return string|null The county
     */
    public function getCounty(): ?string
    {
        return $this->get('county');
    }

    /**
     * Get municipality
     *
     * @return string|null The municipality
     */
    public function getMunicipality(): ?string
    {
        return $this->get('municipality');
    }

    /**
     * Get city
     *
     * @return string|null The city name
     */
    public function getCity(): ?string
    {
        return $this->get('city');
    }

    /**
     * Get town
     *
     * @return string|null The town name
     */
    public function getTown(): ?string
    {
        return $this->get('town');
    }

    /**
     * Get village
     *
     * @return string|null The village name
     */
    public function getVillage(): ?string
    {
        return $this->get('village');
    }

    /**
     * Get settlement name (checks city, town, village, hamlet)
     *
     * @return string|null The settlement name
     */
    public function getSettlement(): ?string
    {
        return $this->getCity() ?? $this->getTown() ?? $this->getVillage() ?? $this->getHamlet();
    }

    /**
     * Get city district
     *
     * @return string|null The city district
     */
    public function getCityDistrict(): ?string
    {
        return $this->get('city_district');
    }

    /**
     * Get district
     *
     * @return string|null The district name
     */
    public function getDistrict(): ?string
    {
        return $this->get('district');
    }

    /**
     * Get borough
     *
     * @return string|null The borough name
     */
    public function getBorough(): ?string
    {
        return $this->get('borough');
    }

    /**
     * Get suburb
     *
     * @return string|null The suburb name
     */
    public function getSuburb(): ?string
    {
        return $this->get('suburb');
    }

    /**
     * Get subdivision
     *
     * @return string|null The subdivision name
     */
    public function getSubdivision(): ?string
    {
        return $this->get('subdivision');
    }

    /**
     * Get hamlet
     *
     * @return string|null The hamlet name
     */
    public function getHamlet(): ?string
    {
        return $this->get('hamlet');
    }

    /**
     * Get croft
     *
     * @return string|null The croft name
     */
    public function getCroft(): ?string
    {
        return $this->get('croft');
    }

    /**
     * Get isolated dwelling
     *
     * @return string|null The isolated dwelling name
     */
    public function getIsolatedDwelling(): ?string
    {
        return $this->get('isolated_dwelling');
    }

    /**
     * Get neighbourhood
     *
     * @return string|null The neighbourhood name
     */
    public function getNeighbourhood(): ?string
    {
        return $this->get('neighbourhood');
    }

    /**
     * Get allotments
     *
     * @return string|null The allotments name
     */
    public function getAllotments(): ?string
    {
        return $this->get('allotments');
    }

    /**
     * Get quarter
     *
     * @return string|null The quarter name
     */
    public function getQuarter(): ?string
    {
        return $this->get('quarter');
    }

    /**
     * Get city block
     *
     * @return string|null The city block name
     */
    public function getCityBlock(): ?string
    {
        return $this->get('city_block');
    }

    /**
     * Get residential area
     *
     * @return string|null The residential area name
     */
    public function getResidential(): ?string
    {
        return $this->get('residential');
    }

    /**
     * Get farm
     *
     * @return string|null The farm name
     */
    public function getFarm(): ?string
    {
        return $this->get('farm');
    }

    /**
     * Get farmyard
     *
     * @return string|null The farmyard name
     */
    public function getFarmyard(): ?string
    {
        return $this->get('farmyard');
    }

    /**
     * Get industrial area
     *
     * @return string|null The industrial area name
     */
    public function getIndustrial(): ?string
    {
        return $this->get('industrial');
    }

    /**
     * Get commercial area
     *
     * @return string|null The commercial area name
     */
    public function getCommercial(): ?string
    {
        return $this->get('commercial');
    }

    /**
     * Get retail area
     *
     * @return string|null The retail area name
     */
    public function getRetail(): ?string
    {
        return $this->get('retail');
    }

    /**
     * Get road name
     *
     * @return string|null The road name
     */
    public function getRoad(): ?string
    {
        return $this->get('road');
    }

    /**
     * Get house number
     *
     * @return string|null The house number
     */
    public function getHouseNumber(): ?string
    {
        return $this->get('house_number');
    }

    /**
     * Get house name
     *
     * @return string|null The house name
     */
    public function getHouseName(): ?string
    {
        return $this->get('house_name');
    }

    /**
     * Get postcode
     *
     * @return string|null The postcode
     */
    public function getPostcode(): ?string
    {
        return $this->get('postcode');
    }

    /**
     * Get ISO 3166-2 Level 4 code
     *
     * @return string|null The ISO code
     */
    public function getIso31662Lvl4(): ?string
    {
        return $this->get('ISO3166-2-lvl4');
    }

    /**
     * Get emergency facility
     *
     * @return string|null The emergency facility
     */
    public function getEmergency(): ?string
    {
        return $this->get('emergency');
    }

    /**
     * Get historic site
     *
     * @return string|null The historic site
     */
    public function getHistoric(): ?string
    {
        return $this->get('historic');
    }

    /**
     * Get military area
     *
     * @return string|null The military area
     */
    public function getMilitary(): ?string
    {
        return $this->get('military');
    }

    /**
     * Get natural feature
     *
     * @return string|null The natural feature
     */
    public function getNatural(): ?string
    {
        return $this->get('natural');
    }

    /**
     * Get landuse type
     *
     * @return string|null The landuse type
     */
    public function getLanduse(): ?string
    {
        return $this->get('landuse');
    }

    /**
     * Get place type
     *
     * @return string|null The place type
     */
    public function getPlace(): ?string
    {
        return $this->get('place');
    }

    /**
     * Get railway feature
     *
     * @return string|null The railway feature
     */
    public function getRailway(): ?string
    {
        return $this->get('railway');
    }

    /**
     * Get man-made structure
     *
     * @return string|null The man-made structure
     */
    public function getManMade(): ?string
    {
        return $this->get('man_made');
    }

    /**
     * Get aerialway feature
     *
     * @return string|null The aerialway feature
     */
    public function getAerialway(): ?string
    {
        return $this->get('aerialway');
    }

    /**
     * Get boundary
     *
     * @return string|null The boundary
     */
    public function getBoundary(): ?string
    {
        return $this->get('boundary');
    }

    /**
     * Get amenity
     *
     * @return string|null The amenity
     */
    public function getAmenity(): ?string
    {
        return $this->get('amenity');
    }

    /**
     * Get aeroway feature
     *
     * @return string|null The aeroway feature
     */
    public function getAeroway(): ?string
    {
        return $this->get('aeroway');
    }

    /**
     * Get club
     *
     * @return string|null The club
     */
    public function getClub(): ?string
    {
        return $this->get('club');
    }

    /**
     * Get craft
     *
     * @return string|null The craft
     */
    public function getCraft(): ?string
    {
        return $this->get('craft');
    }

    /**
     * Get leisure facility
     *
     * @return string|null The leisure facility
     */
    public function getLeisure(): ?string
    {
        return $this->get('leisure');
    }

    /**
     * Get office
     *
     * @return string|null The office
     */
    public function getOffice(): ?string
    {
        return $this->get('office');
    }

    /**
     * Get mountain pass
     *
     * @return string|null The mountain pass
     */
    public function getMountainPass(): ?string
    {
        return $this->get('mountain_pass');
    }

    /**
     * Get shop
     *
     * @return string|null The shop
     */
    public function getShop(): ?string
    {
        return $this->get('shop');
    }

    /**
     * Get tourism spot
     *
     * @return string|null The tourism spot
     */
    public function getTourism(): ?string
    {
        return $this->get('tourism');
    }

    /**
     * Get bridge
     *
     * @return string|null The bridge
     */
    public function getBridge(): ?string
    {
        return $this->get('bridge');
    }

    /**
     * Get tunnel
     *
     * @return string|null The tunnel
     */
    public function getTunnel(): ?string
    {
        return $this->get('tunnel');
    }

    /**
     * Get waterway
     *
     * @return string|null The waterway
     */
    public function getWaterway(): ?string
    {
        return $this->get('waterway');
    }

    /**
     * Get raw details array
     *
     * @return array The raw details array
     */
    public function toArray(): array
    {
        return $this->details;
    }
}
