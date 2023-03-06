<?php

namespace Fuelrod;

use Fuelrod\Sms\SmsService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Fuelrod
{

    protected Client $httpClient;
    protected string $username;
    protected string $password;

    protected string $baseUrl;


    public function __construct(string $username, string $password, string $baseUrl)
    {
        $this->username = $username;
        $this->password = $password;
        $this->baseUrl = $baseUrl;

        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
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
