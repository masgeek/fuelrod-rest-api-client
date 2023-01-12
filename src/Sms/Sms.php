<?php

namespace Fuelrod\Sms;
class Sms
{
    /**
     * @param string $greet
     * @return string
     */
    public function greet(string $greet = "Hello world"): string
    {
        return $greet;
    }
}
