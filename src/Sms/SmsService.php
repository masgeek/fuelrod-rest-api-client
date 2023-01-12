<?php

namespace Fuelrod\Sms;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yiisoft\Arrays\ArrayHelper;

class SmsService extends RestService
{
    protected $httpClient;

    public function __construct($client, $username, $content)
    {
        parent::__construct($client, $username);
        $this->httpClient = $content;
    }

    /**
     * @param array $payload
     * @return array
     * @throws GuzzleException
     */
    public function sendSingleSms(array $payload): array
    {
        $messagePayload = [];
        if (!ArrayHelper::keyExists($payload, 'to')) {
            return $this->error("Recipient phone number must be defined");
        }

        if (!ArrayHelper::keyExists($payload, 'message')) {
            return $this->error("SMS message must be defined");
        }

        if (is_array(ArrayHelper::getColumn($payload, 'to'))) {
            $numbers = ArrayHelper::getValue($payload, 'to');
            $messagePayload['GSM'] = implode(",", $numbers);
        } else {
            $messagePayload['GSM'] = ArrayHelper::getValue($payload, 'to');
        }

        $messagePayload[] = [
            "SMSText" => ArrayHelper::getValue($payload, 'message'),
            "password" => $this->password,
            "user" => $this->username
        ];

        /* @var $httpClient Client */
        $response = $this->httpClient->post('v1/sms/single', ['json' => $messagePayload]);

        return $this->success($response);
    }

}
