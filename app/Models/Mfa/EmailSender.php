<?php

namespace Models\Mfa;

use Zephyrus\Application\Session;

class EmailSender extends Sender
{
    public static function verifySentCode($input): bool
    {
        return $input == Session::getInstance()->read("emailCode");
    }

    public function sendCode($to)
    {
        parent::sendWithCode($to, "emailCode");
    }

    public function send($to, $text)
    {
        //save as emailCode in session
        // TODO: Implement send() method.
    }
}