<?php

namespace Tests\Unit\Client;

use Fyennyi\Nominatim\Client;
use GuzzleHttp\ClientInterface;
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

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'status',
                $this->callback(function ($options) {
                    return isset($options['query']['format']) && 'json' === $options['query']['format']
                        && isset($options['headers']['Accept']) && 'application/json' === $options['headers']['Accept'];
                })
            )
            ->willReturn($response);

        $client = new Client($mockHttpClient);
        $result = \React\Async\await($client->status('json'));

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['status']);
        $this->assertEquals('OK', $result['message']);
    }

    public function testStatusDefaultJson()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);

        $statusResponse = ['status' => 0, 'message' => 'OK'];
        $response = new Response(200, [], json_encode($statusResponse));

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'status',
                $this->callback(function ($options) {
                    return isset($options['query']['format']) && 'json' === $options['query']['format'];
                })
            )
            ->willReturn($response);

        $client = new Client($mockHttpClient);
        $result = \React\Async\await($client->status());

        $this->assertIsArray($result);
    }

    public function testStatusText()
    {
        $mockHttpClient = $this->createMock(ClientInterface::class);

        $statusResponse = 'OK';
        $response = new Response(200, [], $statusResponse);

        $mockHttpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'status',
                $this->callback(function ($options) {
                    return isset($options['query']['format']) && 'text' === $options['query']['format']
                        && isset($options['headers']['Accept']) && 'text/plain' === $options['headers']['Accept'];
                })
            )
            ->willReturn($response);

        $client = new Client($mockHttpClient);
        $result = \React\Async\await($client->status('text'));

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
