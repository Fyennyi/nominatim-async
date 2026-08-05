<?php

namespace Tests\Unit\Model;

use Fyennyi\Nominatim\Model\AddressComponent;
use PHPUnit\Framework\TestCase;

class AddressComponentTest extends TestCase
{
    public function testMapsAllProvidedFields()
    {
        $data = [
            'localname' => 'Kyiv',
            'osm_id' => '12345',
            'osm_type' => 'node',
            'class' => 'place',
            'type' => 'city',
            'admin_level' => '8',
            'rank_address' => '10',
            'isaddress' => '1',
        ];

        $component = new AddressComponent($data);

        $this->assertSame('Kyiv', $component->getLocalName());
        $this->assertSame(12345, $component->getOsmId());
        $this->assertSame('node', $component->getOsmType());
        $this->assertSame('place', $component->getClass());
        $this->assertSame('city', $component->getType());
        $this->assertSame(8, $component->getAdminLevel());
        $this->assertSame(10, $component->getRankAddress());
        $this->assertTrue($component->isAddress());
    }

    public function testUsesDefaultsForMissingFields()
    {
        $component = new AddressComponent([]);

        $this->assertSame('', $component->getLocalName());
        $this->assertNull($component->getOsmId());
        $this->assertNull($component->getOsmType());
        $this->assertNull($component->getClass());
        $this->assertNull($component->getType());
        $this->assertNull($component->getAdminLevel());
        $this->assertSame(0, $component->getRankAddress());
        $this->assertFalse($component->isAddress());
    }

    public function testLocalNameDefaultsToEmptyString()
    {
        $component = new AddressComponent(['osm_id' => 1]);

        $this->assertSame('', $component->getLocalName());
    }

    public function testRankAddressDefaultsToZero()
    {
        $component = new AddressComponent(['localname' => 'Test']);

        $this->assertSame(0, $component->getRankAddress());
    }

    public function testIsAddressDefaultsToFalse()
    {
        $component = new AddressComponent(['localname' => 'Test']);

        $this->assertFalse($component->isAddress());
    }

    public function testIsAddressIsTruthy()
    {
        $component = new AddressComponent(['localname' => 'Test', 'isaddress' => 'yes']);

        $this->assertTrue($component->isAddress());
    }

    public function testOsmIdCastsStringToInt()
    {
        $component = new AddressComponent(['osm_id' => '42']);

        $this->assertSame(42, $component->getOsmId());
        $this->assertIsInt($component->getOsmId());
    }

    public function testAdminLevelCastsStringToInt()
    {
        $component = new AddressComponent(['admin_level' => '6']);

        $this->assertSame(6, $component->getAdminLevel());
        $this->assertIsInt($component->getAdminLevel());
    }
}
