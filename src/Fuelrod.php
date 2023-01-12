<?php

namespace Fuelrod;

use Fuelrod\Sms\SmsService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Fuelrod
{

    protected $httpClient;
    protected $legacyClient;
    protected $baseDomain;
    protected $username;
    protected $password;

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
                'Accept' => 'application/json'
            ]
        ]);
    }

    /**
     * @param array $message
     * @return array
     * @throws GuzzleException
     */
    public function sms(array $message): array
    {
        $content = new SmsService($this->httpClient, $this->username, $this->password);
        return $content->sendSingleSms($message);
    }
}
