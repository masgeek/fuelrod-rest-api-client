<?php

namespace Fuelrod;

use Fuelrod\Sms\SmsService;
use GuzzleHttp\Client;

class FuelrodNoCurl
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
    }

    /**
     * @param array $message
     * @return array
     */
    public function sms(array $message): array
    {
        $content = new SmsService($this->username, $this->password);
        $content->baseUrl = $this->baseUrl;
        return $content->sendPlainSms($message);
    }
}
