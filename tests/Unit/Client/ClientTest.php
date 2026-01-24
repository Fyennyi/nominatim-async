<?php

namespace Tests\Unit\Client;

use Fyennyi\Nominatim\Client;
use Fyennyi\Nominatim\Exception\TransportException;
use Fyennyi\Nominatim\Model\Place;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
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

    public function testSearchStructured()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);
        
        $responseBody = json_encode([
            ['place_id' => 101, 'display_name' => 'Structured Place']
        ]);
        $response = new Response(200, [], $responseBody);
        
        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->with(
                'GET',
                'search',
                $this->callback(function ($options) {
                    $query = $options['query'];
                    return isset($query['street']) && $query['street'] === 'Main St'
                        && isset($query['city']) && $query['city'] === 'Town'
                        && !isset($query['q']); // Ensure 'q' is not set
                })
            )
            ->willReturn(new FulfilledPromise($response));

        $client = new Client($mockHttpClient);
        $promise = $client->search(['street' => 'Main St', 'city' => 'Town']);
        $places = $promise->wait();

        $this->assertCount(1, $places);
        $this->assertEquals(101, $places[0]->getPlaceId());
    }

    public function testLookup()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);
        
        $responseBody = json_encode([
            ['place_id' => 201, 'osm_type' => 'W', 'osm_id' => 12345]
        ]);
        $response = new Response(200, [], $responseBody);
        
        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->with(
                'GET',
                'lookup',
                $this->callback(function ($options) {
                    return isset($options['query']['osm_ids']) && $options['query']['osm_ids'] === 'R123,W456';
                })
            )
            ->willReturn(new FulfilledPromise($response));

        $client = new Client($mockHttpClient);
        $promise = $client->lookup(['R123', 'W456']);
        $places = $promise->wait();

        $this->assertCount(1, $places);
        $this->assertEquals(201, $places[0]->getPlaceId());
    }

    public function testDetails()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);
        
        // details returns a single object
        $responseBody = json_encode([
            'place_id' => 301,
            'category' => 'highway'
        ]);
        $response = new Response(200, [], $responseBody);
        
        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->with(
                'GET',
                'details',
                $this->callback(function ($options) {
                    return isset($options['query']['place_id']) && $options['query']['place_id'] == 12345;
                })
            )
            ->willReturn(new FulfilledPromise($response));

        $client = new Client($mockHttpClient);
        $promise = $client->details(['place_id' => 12345]);
        $place = $promise->wait();

        $this->assertInstanceOf(Place::class, $place);
        $this->assertEquals(301, $place->getPlaceId());
        $this->assertEquals('highway', $place->getCategory());
    }

    public function testSearchStandardJsonList()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);
        
        $responseBody = json_encode([
            ['place_id' => 401],
            ['place_id' => 402]
        ]);
        $response = new Response(200, [], $responseBody);
        
        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->willReturn(new FulfilledPromise($response));

        $client = new Client($mockHttpClient);
        $promise = $client->search('test');
        $places = $promise->wait();

        $this->assertCount(2, $places);
        $this->assertEquals(401, $places[0]->getPlaceId());
        $this->assertEquals(402, $places[1]->getPlaceId());
    }

    public function testSearchSingleObjectResponse()
    {
        // This covers the single object fallback in processResponse (Line 182)
        $mockHttpClient = $this->createMock(ClientInterface::class);
        
        // Response is a single object, NOT an array of objects
        $responseBody = json_encode([
            'place_id' => 501,
            'display_name' => 'Single Object Place'
        ]);
        $response = new Response(200, [], $responseBody);
        
        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->willReturn(new FulfilledPromise($response));

        $client = new Client($mockHttpClient);
        $promise = $client->search('test');
        $places = $promise->wait();

        $this->assertCount(1, $places);
        $this->assertEquals(501, $places[0]->getPlaceId());
    }

    public function testErrorResponse()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);
        
        $responseBody = json_encode([
            'error' => 'Unable to geocode'
        ]);
        $response = new Response(200, [], $responseBody);
        
        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->willReturn(new FulfilledPromise($response));

        $client = new Client($mockHttpClient);
        $promise = $client->search('invalid');
        $places = $promise->wait();

        $this->assertIsArray($places);
        $this->assertEmpty($places);
    }

    public function testInvalidJson()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);
        
        $response = new Response(200, [], 'invalid-json-{');
        
        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->willReturn(new FulfilledPromise($response));

        $client = new Client($mockHttpClient);
        
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('Failed to decode JSON response');
        
        $client->search('test')->wait();
    }

    public function testInvalidDataFormat()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);
        
        $response = new Response(200, [], 'null');
        
        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->willReturn(new FulfilledPromise($response));

        $client = new Client($mockHttpClient);
        
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('API returned invalid data format');
        
        $client->search('test')->wait();
    }

    public function testRequestException()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);
        
        $exception = new \Exception('Network Error');
        
        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->willReturn(new RejectedPromise($exception));

        $client = new Client($mockHttpClient);
        
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('HTTP request failed: Network Error');
        
        $client->search('test')->wait();
    }
}