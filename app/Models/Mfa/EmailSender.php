<?php

namespace Models\Mfa;

use Models\RandomCodeGenerator;
use Twilio\Rest\Client;

class EmailSender extends Sender
{
    public function send($to, $text)
    {
        // TODO: Implement send() method.
    }
}