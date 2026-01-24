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

class ClientCoverageTest extends TestCase
{
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
        // This covers the array_map in processResponse (Line 185)
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

    public function testErrorResponse()
    {
        // Covers Lines 175-181
        $mockHttpClient = $this->createMock(ClientInterface::class);
        
        $responseBody = json_encode([
            'error' => 'Unable to geocode'
        ]);
        $response = new Response(200, [], $responseBody);
        
        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->willReturn(new FulfilledPromise($response));

        $client = new Client($mockHttpClient);
        $promise = $client->search('invalid'); // Although search usually returns [], sometimes API might return error obj
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
        
        // Valid JSON but not an array/object expected (e.g. null or simple string)
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
