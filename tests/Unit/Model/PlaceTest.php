<?php

namespace Tests\Unit\Model;

use Fyennyi\Nominatim\Model\Address;
use Fyennyi\Nominatim\Model\Place;
use PHPUnit\Framework\TestCase;

class PlaceTest extends TestCase
{
    public function testPlaceMapping()
    {
        $data = [
            'place_id' => 100149,
            'licence' => 'Data © OpenStreetMap contributors, ODbL 1.0. https://osm.org/copyright',
            'osm_type' => 'node',
            'osm_id' => '107775',
            'boundingbox' => ['51.3473219', '51.6673219', '-0.2876474', '0.0323526'],
            'lat' => '51.5073219',
            'lon' => '-0.1276474',
            'display_name' => 'London, Greater London, England, SW1A 2DU, United Kingdom',
            'category' => 'place',
            'type' => 'city',
            'importance' => 0.9654895765402,
            'icon' => 'https://nominatim.openstreetmap.org/images/mapicons/poi_place_city.p.20.png',
            'address' => [
                'city' => 'London',
                'state_district' => 'Greater London',
                'state' => 'England',
                'ISO3166-2-lvl4' => 'GB-ENG',
                'postcode' => 'SW1A 2DU',
                'country' => 'United Kingdom',
                'country_code' => 'gb',
                'municipality' => 'City of London'
            ],
            'extratags' => [
                'capital' => 'yes',
                'website' => 'http://www.london.gov.uk',
                'wikidata' => 'Q84',
                'wikipedia' => 'en:London',
                'population' => '8416535'
            ],
            'namedetails' => [
                'name' => 'London',
                'name:uk' => 'Лондон'
            ],
            'place_rank' => 15,
            'address_rank' => 15,
            'geojson' => [
                'type' => 'Point',
                'coordinates' => [-0.1276474, 51.5073219]
            ],
            'entrances' => [
                ['osm_id' => 123, 'type' => 'main', 'lat' => 51.507, 'lon' => -0.127]
            ]
        ];

        $place = new Place($data);

        $this->assertEquals(100149, $place->getPlaceId());
        $this->assertEquals('Data © OpenStreetMap contributors, ODbL 1.0. https://osm.org/copyright', $place->getLicence());
        $this->assertEquals('node', $place->getOsmType());
        $this->assertEquals(107775, $place->getOsmId());
        $this->assertEquals(51.5073219, $place->getLat());
        $this->assertEquals(-0.1276474, $place->getLon());
        $this->assertEquals('London, Greater London, England, SW1A 2DU, United Kingdom', $place->getDisplayName());
        $this->assertEquals('place', $place->getCategory());
        $this->assertEquals('city', $place->getType());
        $this->assertEquals(0.9654895765402, $place->getImportance());
        $this->assertEquals(15, $place->getPlaceRank());
        $this->assertEquals(15, $place->getAddressRank());
        $this->assertEquals([51.3473219, 51.6673219, -0.2876474, 0.0323526], $place->getBoundingBox());
        $this->assertEquals('https://nominatim.openstreetmap.org/images/mapicons/poi_place_city.p.20.png', $place->getIcon());
        $this->assertEquals('yes', $place->getExtraTags()['capital']);
        $this->assertEquals('Лондон', $place->getNameDetails()['name:uk']);
        $this->assertEquals('Point', $place->getGeometry()['type']);
        $this->assertCount(1, $place->getEntrances());
        $this->assertEquals(123, $place->getEntrances()[0]['osm_id']);

        $address = $place->getAddress();
        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals('London', $address->getCity());
        $this->assertEquals('United Kingdom', $address->getCountry());
        $this->assertEquals('gb', $address->getCountryCode());
        $this->assertEquals('City of London', $address->getMunicipality());
        $this->assertEquals('GB-ENG', $address->getIso31662Lvl4());
    }

    public function testDetailsMapping()
    {
        $data = [
            'place_id' => 85993608,
            'parent_place_id' => 72765313,
            'osm_type' => 'W',
            'osm_id' => 38210407,
            'category' => 'place',
            'type' => 'square',
            'admin_level' => '15',
            'localname' => 'Pariser Platz',
            'names' => [
                'name' => 'Pariser Platz',
                'name:uk' => 'Паризька площа'
            ],
            'addresstags' => [
                'postcode' => '10117'
            ],
            'housenumber' => null,
            'calculated_postcode' => '10117',
            'country_code' => 'de',
            'indexed_date' => '2018-08-18T17:02:45+00:00',
            'importance' => 0.339401620591472,
            'calculated_importance' => 0.339401620591472,
            'extratags' => [
                'wikidata' => 'Q156716',
                'wikipedia' => 'de:Pariser Platz'
            ],
            'calculated_wikipedia' => 'de:Pariser_Platz',
            'rank_address' => 30,
            'rank_search' => 30,
            'isarea' => true,
            'centroid' => [
                'type' => 'Point',
                'coordinates' => [13.3786822618517, 52.5163654]
            ],
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [13.3786822618517, 52.5163654]
            ],
            'addresstype' => 'road',
            'name' => 'Pariser Platz'
        ];

        $place = new Place($data);

        $this->assertEquals(85993608, $place->getPlaceId());
        $this->assertEquals(72765313, $place->getParentPlaceId());
        $this->assertEquals('W', $place->getOsmType());
        $this->assertEquals(38210407, $place->getOsmId());
        $this->assertEquals('place', $place->getCategory());
        $this->assertEquals('square', $place->getType());
        $this->assertEquals('15', $place->getAdminLevel());
        $this->assertEquals('Pariser Platz', $place->getLocalName());
        $this->assertEquals('road', $place->getAddressType());
        $this->assertEquals('Pariser Platz', $place->getName());
        $this->assertEquals('Паризька площа', $place->getNameDetails()['name:uk']);
        $this->assertEquals('10117', $place->getAddressTags()['postcode']);
        $this->assertNull($place->getHouseNumber());
        $this->assertEquals('10117', $place->getCalculatedPostcode());
        $this->assertEquals('2018-08-18T17:02:45+00:00', $place->getIndexedDate());
        $this->assertEquals(0.339401620591472, $place->getImportance());
        $this->assertEquals(0.339401620591472, $place->getCalculatedImportance());
        $this->assertEquals('de:Pariser_Platz', $place->getCalculatedWikipedia());
        $this->assertEquals(30, $place->getPlaceRank());
        $this->assertEquals(30, $place->getAddressRank());
        $this->assertTrue($place->isArea());
        $this->assertEquals(52.5163654, $place->getLat());
        $this->assertEquals(13.3786822618517, $place->getLon());
        $this->assertNotNull($place->getCentroid());
        $this->assertNotNull($place->getGeometry());
    }
}
