<?php

namespace Fuelrod\Tests;

use Fuelrod\Exceptions\FuelrodException;
use Fuelrod\Sms\SmsService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class SmsServiceTest extends TestCase
{
    private SmsService $service;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handlerStack]);
        $this->service = new SmsService('testuser', 'testpass', 'https://api.fuelrod.test', $client);
    }

    private function makeServiceWithApiKey(string $apiKey): SmsService
    {
        $handler = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handler]);
        return new SmsService('testuser', 'testpass', 'https://api.fuelrod.test', $client, $apiKey);
    }

    // --- processMessage: missing fields ---

    public function testProcessMessageThrowsWhenToIsMissing(): void
    {
        $this->expectException(FuelrodException::class);
        $this->expectExceptionCode(422);
        $this->service->processMessage(['message' => 'Hello']);
    }

    public function testProcessMessageThrowsWhenToIsArray(): void
    {
        $this->expectException(FuelrodException::class);
        $this->expectExceptionCode(422);
        $this->service->processMessage(['to' => ['0712345678'], 'message' => 'Hello']);
    }

    public function testProcessMessageThrowsWhenMessageIsMissing(): void
    {
        $this->expectException(FuelrodException::class);
        $this->expectExceptionCode(422);
        $this->service->processMessage(['to' => '0712345678']);
    }

    // --- processMessage: phone number validation ---

    public function testProcessMessageThrowsOnInvalidPhoneNumber(): void
    {
        $this->expectException(FuelrodException::class);
        $this->expectExceptionCode(422);
        $this->service->processMessage(['to' => 'not-a-number', 'message' => 'Hello']);
    }

    public function testProcessMessageThrowsOnTooShortNumber(): void
    {
        $this->expectException(FuelrodException::class);
        $this->expectExceptionCode(422);
        $this->service->processMessage(['to' => '123', 'message' => 'Hello']);
    }

    public function testProcessMessageAcceptsLocalFormat(): void
    {
        $result = $this->service->processMessage(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertSame('0712345678', $result['GSM']);
    }

    public function testProcessMessageAcceptsE164Format(): void
    {
        $result = $this->service->processMessage(['to' => '+254712345678', 'message' => 'Hello']);

        $this->assertSame('+254712345678', $result['GSM']);
    }

    public function testProcessMessageTrimsWhitespaceFromNumber(): void
    {
        $result = $this->service->processMessage(['to' => '  0712345678  ', 'message' => 'Hello']);

        $this->assertSame('0712345678', $result['GSM']);
    }

    // --- processMessage: auth ---

    public function testProcessMessageIncludesCredentialsWhenNoApiKey(): void
    {
        $result = $this->service->processMessage(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertSame('testuser', $result['user']);
        $this->assertSame('testpass', $result['password']);
    }

    public function testProcessMessageOmitsCredentialsWhenApiKeyProvided(): void
    {
        $service = $this->makeServiceWithApiKey('my-api-key');
        $result = $service->processMessage(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertArrayNotHasKey('user', $result);
        $this->assertArrayNotHasKey('password', $result);
    }

    // --- processMessage: field mapping ---

    public function testProcessMessageMapsFieldsForSingleSms(): void
    {
        $result = $this->service->processMessage(['to' => '0712345678', 'message' => 'Hello World']);

        $this->assertSame('0712345678', $result['GSM']);
        $this->assertSame('Hello World', $result['SMSText']);
        $this->assertArrayNotHasKey('to', $result);
        $this->assertArrayNotHasKey('text', $result);
    }

    public function testProcessMessageRemapsFieldsForPlainSms(): void
    {
        $result = $this->service->processMessage(['to' => '0712345678', 'message' => 'Hello'], true);

        $this->assertSame('0712345678', $result['to']);
        $this->assertSame('Hello', $result['text']);
        $this->assertArrayNotHasKey('GSM', $result);
        $this->assertArrayNotHasKey('SMSText', $result);
    }

    // --- sendSingleSms ---

    public function testSendSingleSmsReturnsSuccessOnOk(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode(['message' => 'sent', 'id' => 42])));

        $result = $this->service->sendSingleSms(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertSame('success', $result['status']);
        $this->assertSame('sent', $result['data']->message);
        $this->assertSame(42, $result['data']->id);
    }

    public function testSendSingleSmsReturnsErrorOnClientException(): void
    {
        $this->mockHandler->append(new ClientException(
            'Bad Request',
            new Request('POST', 'v1/sms/single'),
            new Response(400, [], json_encode(['error' => 'invalid number']))
        ));

        $result = $this->service->sendSingleSms(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertSame('error', $result['status']);
        $this->assertSame('invalid number', $result['data']->error);
    }

    public function testSendSingleSmsReturnsRawBodyWhenResponseIsNotJson(): void
    {
        $this->mockHandler->append(new ClientException(
            'Server Error',
            new Request('POST', 'v1/sms/single'),
            new Response(500, [], '<html>Internal Server Error</html>')
        ));

        $result = $this->service->sendSingleSms(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertSame('error', $result['status']);
        $this->assertSame('<html>Internal Server Error</html>', $result['data']);
    }

    public function testSendSingleSmsThrowsWhenPayloadInvalid(): void
    {
        $this->expectException(FuelrodException::class);

        $this->service->sendSingleSms(['message' => 'Hello']);
    }

    // --- sendPlainSms ---

    public function testSendPlainSmsReturnsSuccessOnOk(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode(['status' => 'queued'])));

        $result = $this->service->sendPlainSms(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertSame('success', $result['status']);
        $this->assertSame('queued', $result['data']->status);
    }

    public function testSendPlainSmsReturnsErrorOnClientException(): void
    {
        $this->mockHandler->append(new ClientException(
            'Unauthorized',
            new Request('POST', 'v1/sms/plain'),
            new Response(401, [], json_encode(['error' => 'unauthorized']))
        ));

        $result = $this->service->sendPlainSms(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertSame('error', $result['status']);
        $this->assertSame('unauthorized', $result['data']->error);
    }

    public function testSendPlainSmsThrowsWhenPayloadInvalid(): void
    {
        $this->expectException(FuelrodException::class);

        $this->service->sendPlainSms(['to' => '0712345678']);
    }

    // --- sendPremiumSms ---

    public function testSendPremiumSmsReturnsSuccessOnOk(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode(['message' => 'queued'])));

        $result = $this->service->sendPremiumSms(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertSame('success', $result['status']);
        $this->assertSame('queued', $result['data']->message);
    }

    public function testSendPremiumSmsReturnsErrorOnClientException(): void
    {
        $this->mockHandler->append(new ClientException(
            'Forbidden',
            new Request('POST', 'v1/sms/premium'),
            new Response(403, [], json_encode(['error' => 'not permitted']))
        ));

        $result = $this->service->sendPremiumSms(['to' => '0712345678', 'message' => 'Hello']);

        $this->assertSame('error', $result['status']);
        $this->assertSame('not permitted', $result['data']->error);
    }

    public function testSendPremiumSmsThrowsWhenPayloadInvalid(): void
    {
        $this->expectException(FuelrodException::class);

        $this->service->sendPremiumSms(['to' => '0712345678']);
    }
}
