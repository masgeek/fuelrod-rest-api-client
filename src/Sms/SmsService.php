<?php

namespace Fuelrod\Sms;

use Fuelrod\Exceptions\FuelrodException;
use GuzzleHttp\Client;
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
        $messagePayload = [
            'password' => $this->password,
            'user' => $this->username
        ];

        if (!isset($payload['to'])) {
            throw new FuelrodException("Recipient phone number must be defined in array key `to`", 422);
        }

        if (!isset($payload['message'])) {
            throw new FuelrodException("SMS message must be defined in array key `message`", 422);
        }

        $numbers = is_array($payload['to']) ? $payload['to'] : [$payload['to']];

        foreach ($numbers as $key => $number) {
            $messagePayload['GSM'] = $number;
            $messagePayload['SMSText'] = $payload['message'];
        }

        if ($plainSms) {
            $messagePayload['to'] = $messagePayload['GSM'];
            $messagePayload['text'] = $messagePayload['SMSText'];
            unset($messagePayload['GSM']);
            unset($messagePayload['SMSText']);
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

    /***
     * @param array $messagePayload
     * @return array
     * @throws FuelrodException
     */
    public function sendPlainSms(array $messagePayload): array
    {
        $data = $this->processMessage($messagePayload, true);


        $content = http_build_query($data);

        $rand = (str_pad(rand(101, 456), '24', '101'));
        $params = [
            'http' => [
                'method' => 'POST',
                'header' =>
                    'Content-Type: application/x-www-form-urlencoded; boundary=---' . $rand,
                'content' => $content,
                'ignore_errors' => true,
            ]
        ];

        $context = stream_context_create($params);

        $resp = file_get_contents("{$this->baseUrl}/v1/sms/plain", false, $context);
        return json_decode($resp, true); //return JSON as associative array
    }

}
