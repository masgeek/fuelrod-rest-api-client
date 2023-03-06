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
    public function sendSingleSms(array $payload, bool $async, bool $legacy): array
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

            if ($legacy) {
                $resp[] = $this->processLegacy($messagePayload);
            } else {
                $resp[] = $this->processMessage($messagePayload, $async);
            }
        }
        return $resp;
    }

    /**
     * @param array $messagePayload
     * @param $async
     * @return array
     * @throws GuzzleException
     */
    private function processMessage(array $messagePayload, $async): array
    {
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

    /**
     * @param array $messagePayload
     * @return false|string
     */
    private function processLegacy(array $messagePayload)
    {
        $postData = http_build_query(
            $messagePayload
        );

        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $postData
            )
        );

        $context = stream_context_create($opts);

        return file_get_contents('https://api.tsobu.co.ke/v1/sms/single', false, $context);

    }

}
