<?php

namespace Fuelrod;

use Fuelrod\Sms\SmsService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Fuelrod
{

    protected Client $httpClient;
    protected Client $legacyClient;
    protected string $baseDomain;
    protected string $username;
    protected string $password;

    protected $baseUrl;


    public function __construct($username, $password, $baseUrl = "https://api.tsobu.co.ke")
    {
        $this->username = $username;
        $this->password = $password;
        $this->baseUrl = $baseUrl;

        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
//                'Accept' => 'application/json'
            ]
        ]);

        $this->legacyClient = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'multipart/form-data',
//                'Accept' => 'application/json'
            ]
        ]);
    }

    /**
     * @param array $message
     * @param bool $async
     * @return array
     * @throws GuzzleException
     */
    public function sms(array $message, bool $async = false): array
    {
        $content = new SmsService($this->username, $this->password);
        $content->httpClient = $this->httpClient;
        return $content->sendSingleSms($message, $async);
    }
}
