<?php

namespace Fuelrod\Sms;

use GuzzleHttp\Client;

abstract class RestService
{
    protected Client $httpClient;
    public string $baseUrl;
    protected string $username;
    protected string $password;
    protected string $accessToken;

    public function __construct($username, $password, Client $httpClient)
    {
        $this->username = $username;
        $this->password = $password;
        $this->httpClient = $httpClient;
    }

    abstract function sendSingleSms(array $messagePayload): array;

    abstract function sendPlainSms(array $messagePayload): array;

    protected function error($data): array
    {
        return [
            'status' => 'error',
            'data' => json_decode($data->getBody()->getContents())
        ];
    }


    protected function success($data): array
    {
        return [
            'status' => 'success',
            'data' => json_decode($data->getBody()->getContents())
        ];
    }
}
