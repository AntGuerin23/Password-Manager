<?php

namespace Models\MFA;

use Twilio\Rest\Client;
use Zephyrus\Utilities\Formatter;

class SmsSender
{
    public static function sendEmail($nb, $text)
    {
        //TODO : Make this env variables
        $account_sid = 'AC40cd4ad23a24a319aa786eda20a88a93';
        $auth_token = '8b72035ffa15670754b1145e0f2ff451';
        $twilio_number = "+12392563135";
        $client = new Client($account_sid, $auth_token);
        $client->messages->create(
            $nb,
            array(
                'from' => $twilio_number,
                'body' => $text
            )
        );
    }
}