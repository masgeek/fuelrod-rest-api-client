<?php

namespace Fuelrod\Sms;

use Fuelrod\Exceptions\FuelrodException;
use GuzzleHttp\Exception\GuzzleException;

class SmsService extends RestService
{

    /**
     * @param array $payload
     * @param bool $plainSms
     * @return array
     * @throws FuelrodException
     */
    public function processMessage(array $payload, bool $plainSms = false): array
    {
        if (!isset($payload['to'])) {
            throw new FuelrodException("Recipient phone number must be defined in array key `to`", 422);
        }

        if (!isset($payload['message'])) {
            throw new FuelrodException("SMS message must be defined in array key `message`", 422);
        }

        // Credentials are only included when not using API key auth
        $messagePayload = $this->apiKey === null
            ? ['user' => $this->username, 'password' => $this->password]
            : [];

        $numbers = is_array($payload['to']) ? $payload['to'] : [$payload['to']];
        if (empty($numbers)) {
            $numbers = ['0713000000'];
        }
        foreach ($numbers as $key => $number) {
            $messagePayload['GSM'] = $number;
            $messagePayload['SMSText'] = $payload['message'];
        }
        
        if ($plainSms) {
            $messagePayload['to'] = $messagePayload['GSM'];
            $messagePayload['text'] = $messagePayload['SMSText'];
            unset($messagePayload['GSM'], $messagePayload['SMSText']);
        }

        return $messagePayload;
    }

    /**
     * @param array $messagePayload
     * @return array
     * @throws GuzzleException|FuelrodException
     */
    public function sendSingleSms(array $messagePayload): array
    {
        /* @var $httpClient Client */

        try {
            $response = $this->httpClient->post('v1/sms/single', [
                'json' => $this->processMessage($messagePayload)
            ]);

            return $this->success($response);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            return $this->error($response);
        }
    }

    /**
     * @param array $messagePayload
     * @return array
     * @throws FuelrodException|GuzzleException
     */
    public function sendPlainSms(array $messagePayload): array
    {
        try {
            $response = $this->httpClient->post('v1/sms/plain', [
                'form_params' => $this->processMessage($messagePayload, true),
            ]);

            return $this->success($response);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->error($e->getResponse());
        }
    }

    /**
     * @param array $messagePayload
     * @return void
     * @throws FuelrodException|GuzzleException
     */
    public function sendPremiumSms(array $messagePayload): void
    {
        $this->httpClient->post('v1/sms/premium', [
            'json' => $this->processMessage($messagePayload),
        ]);
    }

}
