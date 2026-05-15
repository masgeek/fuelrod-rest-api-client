<?php

namespace Fuelrod\Sms;

use GuzzleHttp\Client;

abstract class RestService
{
    protected Client $httpClient;
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected ?string $apiKey;

    public function __construct(string $username, string $password, string $baseUrl, Client $httpClient, ?string $apiKey = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->baseUrl = $baseUrl;
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    abstract public function sendSingleSms(array $messagePayload): array;

    abstract public function sendPlainSms(array $messagePayload): array;

    protected function error($data): array
    {
        $body = $data->getBody()->getContents();
        return [
            'status' => 'error',
            'data' => json_decode($body) ?? $body,
        ];
    }

    protected function success($data): array
    {
        $body = $data->getBody()->getContents();
        return [
            'status' => 'success',
            'data' => json_decode($body) ?? $body,
        ];
    }
}
