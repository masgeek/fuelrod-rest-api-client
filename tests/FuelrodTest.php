<?php

namespace Fuelrod\Tests;

use Fuelrod\Exceptions\FuelrodException;
use Fuelrod\Fuelrod;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class FuelrodTest extends TestCase
{
    private MockHandler $mockHandler;
    private Fuelrod $client;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $httpClient = new Client(['handler' => $handlerStack]);
        $this->client = new Fuelrod('user', 'pass', 'https://api.fuelrod.test', null, $httpClient);
    }

    public function testSingleSmsReturnsSuccessResponse(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode(['id' => 1, 'message' => 'sent'])));

        $result = $this->client->singleSms(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertSame('success', $result['status']);
        $this->assertSame(1, $result['data']->id);
    }

    public function testSingleSmsReturnsErrorOnClientException(): void
    {
        $this->mockHandler->append(new ClientException(
            'Bad Request',
            new Request('POST', 'v1/sms/single'),
            new Response(400, [], json_encode(['error' => 'invalid']))
        ));

        $result = $this->client->singleSms(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertSame('error', $result['status']);
    }

    public function testSingleSmsThrowsOnInvalidPayload(): void
    {
        $this->expectException(FuelrodException::class);

        $this->client->singleSms(['message' => 'Hello']);
    }

    public function testPlainSmsReturnsSuccessResponse(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode(['status' => 'queued'])));

        $result = $this->client->plainSms(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertSame('success', $result['status']);
    }

    public function testPremiumSmsReturnsSuccessResponse(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode(['status' => 'queued'])));

        $result = $this->client->premiumSms(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertSame('success', $result['status']);
    }

    public function testClientCanBeConstructedWithApiKey(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], json_encode(['id' => 99])),
        ]);
        $httpClient = new Client(['handler' => HandlerStack::create($mockHandler)]);
        $client = new Fuelrod('user', 'pass', 'https://api.fuelrod.test', 'my-api-key', $httpClient);

        $result = $client->singleSms(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertSame('success', $result['status']);
        $this->assertSame(99, $result['data']->id);
    }
}
