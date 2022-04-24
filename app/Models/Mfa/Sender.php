<?php

namespace Models\Mfa;

use Models\RandomCodeGenerator;
use Twilio\Rest\Client;

abstract class Sender
{
    private int $code = 0;

    public function verifySentCode($input) : bool
    {
        return $input == $this->code;
    }

    public function sendWithCode($to)
    {
        $this->code = RandomCodeGenerator::generateInt();
        $this->send($to, "Your one-time use code : " . $this->code);
    }

    abstract public function send($to, $text);
}