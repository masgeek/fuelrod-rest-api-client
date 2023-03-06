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
     * @param bool $async
     * @return array
     * @throws GuzzleException
     */
    public function sendSingleSms(array $payload, bool $async = false): array
    {
        $messagePayload = [];
        if (!isset($payload['to'])) {
            return $this->error("Recipient phone number must be defined");
        }

        if (!isset($payload['message'])) {
            return $this->error("SMS message must be defined");
        }

        $numbers = is_array($payload['to']) ? $payload['to'] : [$payload['to']];

        $resp = [];
        foreach ($numbers as $key => $number) {
            $messagePayload['GSM'] = $number;
            $messagePayload['SMSText'] = $payload['message'];
            $messagePayload['password'] = $this->password;
            $messagePayload['user'] = $this->username;

            $resp[] = $this->processMessage($messagePayload, $async);
        }
        return $resp;
    }

    /**
     * @param array $messagePayload
     * @param $async
     * @return array
     * @throws GuzzleException
     */
    private function processMessage(array $messagePayload, $async, $useLegacy = false): array
    {
        if ($useLegacy) {
            return $this->processLegacy($messagePayload);
        }
        /* @var $httpClient Client */
        try {
            $response = $this->httpClient->post('v1/sms/single', [
                'future' => $async,
                'json' => $messagePayload
            ]);

            return $this->success($response);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            return $this->error($response);
        }
    }

    private function processLegacy(array $messagePayload): array
    {
        $postData = http_build_query(
            $messagePayload
        );

        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $postData
            )
        );

        $context = stream_context_create($opts);

        $result = file_get_contents('https://api.tsobu.co.ke', false, $context);

        return [$result];
    }

}
