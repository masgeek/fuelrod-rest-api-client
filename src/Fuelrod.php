<?php

namespace Fuelrod;

use Fuelrod\Exceptions\FuelrodException;
use Fuelrod\Sms\SmsService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Fuelrod
{

    protected SmsService $smsService;
    protected string $baseUrl;


    public function __construct(string $username, string $password, string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);
        $this->smsService = new SmsService($username, $password, $httpClient);

    }

    /**
     * @param array $message
     * @return array
     * @throws GuzzleException|Exceptions\FuelrodException
     */
    public function singleSms(array $message): array
    {
        return $this->smsService->sendSingleSms($message);
    }

    /**
     * @param array $message
     * @return array
     * @throws FuelrodException
     */
    public function plainSms(array $message): array
    {
        return $this->smsService->sendPlainSms($message);
    }
}
