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
}
