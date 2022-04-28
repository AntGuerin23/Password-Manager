<?php

namespace Models\Mfa;

use SendGrid;
use SendGrid\Mail\Mail;
use Zephyrus\Application\Session;

class EmailSender extends Sender
{
    public static function verifySentCode($input): bool
    {
        return $input == Session::getInstance()->read("emailCode");
    }

    public function sendCode($to, $subject)
    {
        parent::sendWithCode($to, "emailCode", $subject);
    }

    public function send($to, $text, $body)
    {
        $key = getenv("SENDGRID_KEY");
        $email = new Mail();
        $email->setFrom("pigggy23@gmail.com", "SosPass");
        $email->setSubject($text);
        $email->addTo($to);
        $email->addContent("text/plain",  strval($body));
        $sendgrid = new SendGrid($key);
        try {
            $response = $sendgrid->send($email);
        } catch (Exception $e) {
            echo 'Caught exception: '. $e->getMessage() ."\n";
        }
    }
}