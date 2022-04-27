<?php

namespace Models\Mfa;

use Models\RandomCodeGenerator;
use Twilio\Rest\Client;
use Zephyrus\Application\Session;
use Zephyrus\Security\Cryptography;

class SmsSender extends Sender
{
    public static function verifySentCode($input): bool
    {
        return $input == Session::getInstance()->read("phoneCode");
    }

    public function sendCode($to)
    {
        parent::sendWithCode($to, "phoneCode", "Your SosPass authentication code : ");
    }

    public function send($to, $text, $code)
    {
        $account_sid = TWILIO_ID;
        $auth_token = TWILIO_TOKEN;
        $twilio_number = TWILIO_NUMBER;
        $client = new Client($account_sid, $auth_token);
        $client->messages->create(
            $to,
            array(
                'from' => $twilio_number,
                'body' => $text + $code
            )
        );
    }
}