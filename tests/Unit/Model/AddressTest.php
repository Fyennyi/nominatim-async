<?php

namespace Tests\Unit\Model;

use Fyennyi\Nominatim\Model\Address;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    public function testAddressSpecifics()
    {
        $data = [
            'continent' => 'Europe',
            'village' => 'Velyki Mosty',
            'municipality' => 'Velykomostivska hromada',
            'district' => 'Chervonohradskyi district',
            'state' => 'Lviv Oblast',
            'country' => 'Ukraine',
            'city_block' => 'Block A',
            'residential' => 'Residential Zone',
            'commercial' => 'Business Park'
        ];

        $address = new Address($data);
        $this->assertEquals('Europe', $address->getContinent());
        $this->assertEquals('Velyki Mosty', $address->getVillage());
        $this->assertEquals('Velykomostivska hromada', $address->getMunicipality());
        $this->assertEquals('Chervonohradskyi district', $address->getDistrict());
        $this->assertEquals('Velyki Mosty', $address->getSettlement());
        $this->assertEquals('Block A', $address->getCityBlock());
        $this->assertEquals('Residential Zone', $address->getResidential());
        $this->assertEquals('Business Park', $address->getCommercial());
        $this->assertNull($address->getIndustrial());
    }

    public function testAllGetters()
    {
        $data = [
            'country' => 'Country',
            'country_code' => 'cc',
            'continent' => 'Continent',
            'state' => 'State',
            'region' => 'Region',
            'state_district' => 'State District',
            'county' => 'County',
            'municipality' => 'Municipality',
            'city' => 'City',
            'town' => 'Town',
            'village' => 'Village',
            'city_district' => 'City District',
            'district' => 'District',
            'borough' => 'Borough',
            'suburb' => 'Suburb',
            'subdivision' => 'Subdivision',
            'hamlet' => 'Hamlet',
            'croft' => 'Croft',
            'isolated_dwelling' => 'Isolated Dwelling',
            'neighbourhood' => 'Neighbourhood',
            'allotments' => 'Allotments',
            'quarter' => 'Quarter',
            'city_block' => 'City Block',
            'residential' => 'Residential',
            'farm' => 'Farm',
            'farmyard' => 'Farmyard',
            'industrial' => 'Industrial',
            'commercial' => 'Commercial',
            'retail' => 'Retail',
            'road' => 'Road',
            'house_number' => '123',
            'house_name' => 'Manor',
            'postcode' => '12345',
            'ISO3166-2-lvl4' => 'ISO-CODE',
            'emergency' => 'Hospital',
            'historic' => 'Castle',
            'military' => 'Base',
            'natural' => 'Hill',
            'landuse' => 'Park',
            'place' => 'Place',
            'railway' => 'Station',
            'man_made' => 'Tower',
            'aerialway' => 'Cable Car',
            'boundary' => 'Fence',
            'amenity' => 'School',
            'aeroway' => 'Airport',
            'club' => 'Club',
            'craft' => 'Workshop',
            'leisure' => 'Park',
            'office' => 'Office',
            'mountain_pass' => 'Pass',
            'shop' => 'Shop',
            'tourism' => 'Hotel',
            'bridge' => 'Bridge',
            'tunnel' => 'Tunnel',
            'waterway' => 'River',
        ];

        $address = new Address($data);

        $this->assertEquals('Country', $address->getCountry());
        $this->assertEquals('cc', $address->getCountryCode());
        $this->assertEquals('Continent', $address->getContinent());
        $this->assertEquals('State', $address->getState());
        $this->assertEquals('Region', $address->getRegion());
        $this->assertEquals('State District', $address->getStateDistrict());
        $this->assertEquals('County', $address->getCounty());
        $this->assertEquals('Municipality', $address->getMunicipality());
        $this->assertEquals('City', $address->getCity());
        $this->assertEquals('Town', $address->getTown());
        $this->assertEquals('Village', $address->getVillage());
        $this->assertEquals('City', $address->getSettlement()); // City takes precedence
        $this->assertEquals('City District', $address->getCityDistrict());
        $this->assertEquals('District', $address->getDistrict());
        $this->assertEquals('Borough', $address->getBorough());
        $this->assertEquals('Suburb', $address->getSuburb());
        $this->assertEquals('Subdivision', $address->getSubdivision());
        $this->assertEquals('Hamlet', $address->getHamlet());
        $this->assertEquals('Croft', $address->getCroft());
        $this->assertEquals('Isolated Dwelling', $address->getIsolatedDwelling());
        $this->assertEquals('Neighbourhood', $address->getNeighbourhood());
        $this->assertEquals('Allotments', $address->getAllotments());
        $this->assertEquals('Quarter', $address->getQuarter());
        $this->assertEquals('City Block', $address->getCityBlock());
        $this->assertEquals('Residential', $address->getResidential());
        $this->assertEquals('Farm', $address->getFarm());
        $this->assertEquals('Farmyard', $address->getFarmyard());
        $this->assertEquals('Industrial', $address->getIndustrial());
        $this->assertEquals('Commercial', $address->getCommercial());
        $this->assertEquals('Retail', $address->getRetail());
        $this->assertEquals('Road', $address->getRoad());
        $this->assertEquals('123', $address->getHouseNumber());
        $this->assertEquals('Manor', $address->getHouseName());
        $this->assertEquals('12345', $address->getPostcode());
        $this->assertEquals('ISO-CODE', $address->getIso31662Lvl4());
        $this->assertEquals('Hospital', $address->getEmergency());
        $this->assertEquals('Castle', $address->getHistoric());
        $this->assertEquals('Base', $address->getMilitary());
        $this->assertEquals('Hill', $address->getNatural());
        $this->assertEquals('Park', $address->getLanduse());
        $this->assertEquals('Place', $address->getPlace());
        $this->assertEquals('Station', $address->getRailway());
        $this->assertEquals('Tower', $address->getManMade());
        $this->assertEquals('Cable Car', $address->getAerialway());
        $this->assertEquals('Fence', $address->getBoundary());
        $this->assertEquals('School', $address->getAmenity());
        $this->assertEquals('Airport', $address->getAeroway());
        $this->assertEquals('Club', $address->getClub());
        $this->assertEquals('Workshop', $address->getCraft());
        $this->assertEquals('Park', $address->getLeisure());
        $this->assertEquals('Office', $address->getOffice());
        $this->assertEquals('Pass', $address->getMountainPass());
        $this->assertEquals('Shop', $address->getShop());
        $this->assertEquals('Hotel', $address->getTourism());
        $this->assertEquals('Bridge', $address->getBridge());
        $this->assertEquals('Tunnel', $address->getTunnel());
        $this->assertEquals('River', $address->getWaterway());

        $this->assertEquals($data, $address->toArray());
    }
}
