<?php

namespace Fuelrod;

use Fuelrod\Exceptions\FuelrodException;
use Fuelrod\Sms\SmsService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Fuelrod
{

    protected Client $httpClient;
    protected ?string $username;
    protected ?string $password;
    protected string $baseUrl;
    protected ?string $apiKey;

    public function __construct(string $baseUrl, ?string $username = null, ?string $password = null, ?string $apiKey = null)
    {
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->password = $password;
        $this->apiKey = $apiKey;

        $headers = ['Content-Type' => 'application/json'];
        if ($apiKey !== null) {
            $headers['x-api-key'] = $apiKey;
        }

        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => $headers,
        ]);
    }

    /**
     * @param array $message
     * @return array
     * @throws GuzzleException|Exceptions\FuelrodException
     */
    public function singleSms(array $message): array
    {
        $sms = new SmsService($this->username, $this->password, $this->apiKey);
        $sms->httpClient = $this->httpClient;
        return $sms->sendSingleSms($message);
    }

    /**
     * @param array $message
     * @return array
     * @throws FuelrodException|GuzzleException
     */
    public function plainSms(array $message): array
    {
        $sms = new SmsService($this->username, $this->password, $this->apiKey);
        $sms->httpClient = $this->httpClient;
        return $sms->sendPlainSms($message);
    }

    /**
     * @param array $message
     * @return void
     * @throws GuzzleException|FuelrodException
     */
    public function premiumSms(array $message): void
    {
        $sms = new SmsService($this->username, $this->password, $this->apiKey);
        $sms->httpClient = $this->httpClient;
        $sms->sendPremiumSms($message);
    }
}
