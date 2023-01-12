<?php

namespace Fuelrod\Sms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class SmsService extends RestService
{
    protected Client $httpClient;

    public function __construct($httpClient, $username, $password)
    {
        parent::__construct($httpClient, $username, $password);
        $this->httpClient = $httpClient;
    }

    /**
     * @param array $payload
     * @return array
     * @throws GuzzleException
     */
    public function sendSingleSms(array $payload): array
    {
        $messagePayload = [];
        if (!isset($payload['to'])) {
            return $this->error("Recipient phone number must be defined");
        }

        if (!isset($payload['message'])) {
            return $this->error("SMS message must be defined");
        }

        if (is_array($payload['to'])) {
            $messagePayload['GSM'] = implode(",", $payload['to']);
        } else {
            $messagePayload['GSM'] = $payload['to'];
        }

        $messagePayload[] = [
            "SMSText" => $payload['message'],
            "password" => $this->password,
            "user" => $this->username
        ];

        /* @var $httpClient Client */
        $response = $this->httpClient->post('v1/sms/single', [
            'verify' => false,
            'json' => $messagePayload
        ]);

        return $this->success($response);
    }

}
