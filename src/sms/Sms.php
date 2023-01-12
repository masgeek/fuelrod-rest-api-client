<?php

namespace Fuelrod\Sms;
class Sms
{
    /**
     * @param string $greet
     * @return string
     */
    public function great(string $greet = "Hello world"): string
    {
        return $greet;
    }
}
