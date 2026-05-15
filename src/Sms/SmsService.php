<?php

namespace Fuelrod\Sms;

use Fuelrod\Exceptions\FuelrodException;
use GuzzleHttp\Exception\GuzzleException;

class SmsService extends RestService
{

    /**
     * @throws FuelrodException
     */
    public function processMessage(array $payload, bool $plainSms = false): array
    {
        if (empty($payload['to']) || !is_string($payload['to'])) {
            throw new FuelrodException("Recipient must be a single phone number string in array key `to`", 422);
        }

        if (empty($payload['message'])) {
            throw new FuelrodException("SMS message must be defined in array key `message`", 422);
        }

        $number = trim($payload['to']);
        if (!preg_match('/^\+?[0-9]{7,15}$/', $number)) {
            throw new FuelrodException("Invalid phone number format: {$number}", 422);
        }

        $messagePayload = $this->apiKey === null
            ? ['user' => $this->username, 'password' => $this->password]
            : [];

        $messagePayload['GSM'] = $number;
        $messagePayload['SMSText'] = $payload['message'];

        if ($plainSms) {
            $messagePayload['to'] = $messagePayload['GSM'];
            $messagePayload['text'] = $messagePayload['SMSText'];
            unset($messagePayload['GSM'], $messagePayload['SMSText']);
        }

        return $messagePayload;
    }

    /**
     * @throws GuzzleException|FuelrodException
     */
    public function sendSingleSms(array $messagePayload): array
    {
        try {
            $response = $this->httpClient->post('v1/sms/single', [
                'json' => $this->processMessage($messagePayload),
            ]);

            return $this->success($response);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->error($e->getResponse());
        }
    }

    /**
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
     * @throws FuelrodException|GuzzleException
     */
    public function sendPremiumSms(array $messagePayload): array
    {
        try {
            $response = $this->httpClient->post('v1/sms/premium', [
                'json' => $this->processMessage($messagePayload),
            ]);

            return $this->success($response);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return $this->error($e->getResponse());
        }
    }
}
