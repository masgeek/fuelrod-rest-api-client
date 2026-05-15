<?php

namespace Fuelrod;

use Fuelrod\Exceptions\FuelrodException;
use Fuelrod\Sms\SmsService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Fuelrod
{

    protected SmsService $smsService;

    public function __construct(string $username, string $password, string $baseUrl, ?string $apiKey = null, ?Client $httpClient = null)
    {
        $httpClient ??= new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->smsService = new SmsService($username, $password, $baseUrl, $httpClient, $apiKey);
    }

    /**
     * @param array $message
     * @return array
     * @throws GuzzleException|FuelrodException
     */
    public function singleSms(array $message): array
    {
        return $this->smsService->sendSingleSms($message);
    }

    /**
     * @param array $message
     * @return array
     * @throws FuelrodException|GuzzleException
     */
    public function plainSms(array $message): array
    {
        return $this->smsService->sendPlainSms($message);
    }

    /**
     * @param array $message
     * @return array
     * @throws FuelrodException|GuzzleException
     */
    public function premiumSms(array $message): array
    {
        return $this->smsService->sendPremiumSms($message);
    }
}
