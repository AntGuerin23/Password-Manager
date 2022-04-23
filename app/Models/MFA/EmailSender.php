<?php

namespace Models\MFA;

use Models\RandomCodeGenerator;
use Twilio\Rest\Client;

class EmailSender extends Sender
{
    public function send($to, $text)
    {
        // TODO: Implement send() method.
    }
}