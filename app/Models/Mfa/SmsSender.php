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
        parent::sendWithCode($to, "phoneCode");
    }

    public function send($to, $text)
    {
        //TODO : Make this env variables
        $account_sid = 'AC40cd4ad23a24a319aa786eda20a88a93';
        $auth_token = '8b72035ffa15670754b1145e0f2ff451';
        $twilio_number = "+12392563135";
        $client = new Client($account_sid, $auth_token);
        $client->messages->create(
            $to,
            array(
                'from' => $twilio_number,
                'body' => $text
            )
        );
    }
}