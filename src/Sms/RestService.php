<?php

namespace Fuelrod\Sms;

abstract class RestService
{
    protected $httpClient;
    protected $username;
    protected $password;
    protected $accessToken;

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
            'data' => $data
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
