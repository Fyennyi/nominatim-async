<?php

namespace Tests\Unit\Client;

use Fyennyi\Nominatim\Client;
use Fyennyi\Nominatim\Model\Place;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testSearchGeoJson()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);
        
        $geojsonResponse = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'place_id' => 123,
                        'display_name' => 'Test Place'
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [10.0, 20.0]
                    ]
                ]
            ]
        ];

        $response = new Response(200, [], json_encode($geojsonResponse));
        $promise = new FulfilledPromise($response);

        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->with(
                'GET',
                'search',
                $this->callback(function ($options) {
                    return isset($options['query']['format']) && $options['query']['format'] === 'geojson';
                })
            )
            ->willReturn($promise);

        $client = new Client($mockHttpClient);
        $promise = $client->search('test', ['format' => 'geojson']);
        $places = $promise->wait();

        $this->assertCount(1, $places);
        $this->assertInstanceOf(Place::class, $places[0]);
        $this->assertEquals(123, $places[0]->getPlaceId());
        $this->assertEquals(['type' => 'Point', 'coordinates' => [10.0, 20.0]], $places[0]->getGeometry());
    }

    public function testReverseGeocodeJson()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);
        
        $geocodeJsonResponse = [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'geocoding' => [
                            'place_id' => 456,
                            'label' => 'Test Reverse',
                            'admin' => [
                                'level4' => 'Akershus',
                                'level2' => 'Norway'
                            ]
                        ]
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [30.0, 40.0]
                    ]
                ]
            ]
        ];

        $response = new Response(200, [], json_encode($geocodeJsonResponse));
        $promise = new FulfilledPromise($response);

        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->with(
                'GET',
                'reverse',
                $this->callback(function ($options) {
                    return isset($options['query']['format']) && $options['query']['format'] === 'geocodejson';
                })
            )
            ->willReturn($promise);

        $client = new Client($mockHttpClient);
        $promise = $client->reverse(40.0, 30.0, ['format' => 'geocodejson']);
        $place = $promise->wait();

        $this->assertInstanceOf(Place::class, $place);
        $this->assertEquals(456, $place->getPlaceId());
        $this->assertEquals('Test Reverse', $place->getLabel());
        $this->assertEquals('Test Reverse', $place->getDisplayName()); // Should fallback to label
        $this->assertEquals('Norway', $place->getAdminLevels()['level2']);
        $this->assertEquals(['type' => 'Point', 'coordinates' => [30.0, 40.0]], $place->getGeometry());
    }
}
