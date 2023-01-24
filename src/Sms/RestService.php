<?php

namespace Fuelrod\Sms;

use GuzzleHttp\Client;

abstract class RestService
{
    protected Client $httpClient;
    protected string $username;
    protected string $password;
    protected string $accessToken;

    public function __construct($httpClient, $username, $password)
    {
        $this->httpClient = $httpClient;
        $this->username = $username;
        $this->password = $password;
    }

    protected function error($data): array
    {
        return [
            'status' => 'error',
            'data' => json_decode($data->response->getBody()->getContents())
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
