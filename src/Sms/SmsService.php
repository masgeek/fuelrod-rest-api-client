<?php

namespace Fuelrod\Sms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class SmsService extends RestService
{

    /**
     * @param array $payload
     * @param bool $plainSms
     * @return array
     */
    public function processMessage(array $payload, bool $plainSms = false): array
    {
        $messagePayload = [
            'password' => $this->password,
            'user' => $this->username
        ];

        if (!isset($payload['to'])) {
            return $this->error("Recipient phone number must be defined");
        }

        if (!isset($payload['message'])) {
            return $this->error("SMS message must be defined");
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
     * @param $async
     * @return array
     * @throws GuzzleException
     */
    public function sendSingleSms(array $messagePayload, $async): array
    {
        /* @var $httpClient Client */
        try {
            $response = $this->httpClient->post('v1/sms/single', [
                'future' => $async,
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
     * @return mixed
     */
    public function sendPlainSms(array $messagePayload)
    {
        $data = $this->processMessage($messagePayload, true);


        $params = [
            'http' => [
                'method' => 'POST',
                'header' =>
                    "Content-Type: application/json\r\n",
                'content' => json_encode($data),
                'ignore_errors' => true,
            ]
        ];

        $context = stream_context_create($params);

        $resp = file_get_contents("{$this->baseUrl}/v1/sms/plain", false, $context);
        return json_decode($resp);
    }

}
