<?php

namespace Fuelrod\Sms;

use GuzzleHttp\Client;

abstract class RestService
{
    public Client $httpClient;
    public string $baseUrl;
    protected string $username;
    protected string $password;
    protected string $accessToken;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
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
