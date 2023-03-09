<?php

namespace Fuelrod;

use Fuelrod\Exceptions\FuelrodException;
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
     * @throws GuzzleException|Exceptions\FuelrodException
     */
    public function singleSms(array $message, bool $async = false): array
    {
        $content = new SmsService($this->username, $this->password);
        $content->httpClient = $this->httpClient;
        return $content->sendSingleSms($message, $async);
    }

    /**
     * @param array $message
     * @return array
     * @throws FuelrodException
     */
    public function plainSms(array $message): array
    {
        $content = new SmsService($this->username, $this->password);
        $content->baseUrl = $this->baseUrl;
        return $content->sendPlainSms($message);
    }
}
