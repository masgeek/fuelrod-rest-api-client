<?php

namespace Fuelrod\Sms;
class Rest extends RestService
{
    protected $httpClient;

    public function __construct($httpClient, $username, $password)
    {
        parent::__construct($username, $password);
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $greet
     * @return string
     */
    public function greet(string $greet = "Hello world"): string
    {
        return $greet;
    }
}
