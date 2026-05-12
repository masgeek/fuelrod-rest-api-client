<?php

namespace Fuelrod\Sms;

use GuzzleHttp\Client;

abstract class RestService
{
    public Client $httpClient;
    public string $baseUrl;
    protected ?string $username;
    protected ?string $password;
    protected ?string $apiKey;

    public function __construct(?string $username, ?string $password, ?string $apiKey = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->apiKey = $apiKey;
    }

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
