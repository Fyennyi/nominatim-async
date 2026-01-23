<?php

namespace Tests\Unit\Client;

use Fyennyi\Nominatim\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ClientStatusTest extends TestCase
{
    public function testStatusJson()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);

        $statusResponse = [
            'status' => 0,
            'message' => 'OK',
            'data_updated' => '2023-01-01T00:00:00+00:00',
            'software_version' => '4.0.0',
            'database_version' => '4.0.0'
        ];

        $response = new Response(200, [], json_encode($statusResponse));
        $promise = new FulfilledPromise($response);

        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->with(
                'GET',
                'status',
                $this->callback(function ($options) {
                    return isset($options['query']['format']) && $options['query']['format'] === 'json'
                        && isset($options['headers']['Accept']) && $options['headers']['Accept'] === 'application/json';
                })
            )
            ->willReturn($promise);

        $client = new Client($mockHttpClient);
        $promise = $client->status('json'); // Explicit json
        $result = $promise->wait();

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['status']);
        $this->assertEquals('OK', $result['message']);
    }

    public function testStatusDefaultJson()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);

        $statusResponse = ['status' => 0, 'message' => 'OK'];
        $response = new Response(200, [], json_encode($statusResponse));
        $promise = new FulfilledPromise($response);

        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->with(
                'GET',
                'status',
                $this->callback(function ($options) {
                    return isset($options['query']['format']) && $options['query']['format'] === 'json';
                })
            )
            ->willReturn($promise);

        $client = new Client($mockHttpClient);
        $promise = $client->status(); // Default
        $result = $promise->wait();

        $this->assertIsArray($result);
    }

    public function testStatusText()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);

        $statusResponse = 'OK';

        $response = new Response(200, [], $statusResponse);
        $promise = new FulfilledPromise($response);

        $mockHttpClient->expects($this->once())
            ->method('requestAsync')
            ->with(
                'GET',
                'status',
                $this->callback(function ($options) {
                    return isset($options['query']['format']) && $options['query']['format'] === 'text'
                        && isset($options['headers']['Accept']) && $options['headers']['Accept'] === 'text/plain';
                })
            )
            ->willReturn($promise);

        $client = new Client($mockHttpClient);
        $promise = $client->status('text');
        $result = $promise->wait();

        $this->assertIsString($result);
        $this->assertEquals('OK', $result);
    }

    public function testStatusInvalidFormat()
    {
        $client = new Client();
        $this->expectException(\InvalidArgumentException::class);
        $client->status('xml');
    }
}
