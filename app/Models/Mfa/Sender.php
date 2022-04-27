<?php

namespace Models\Mfa;

use Models\RandomCodeGenerator;
use Zephyrus\Application\Session;

abstract class Sender
{

    protected function sendWithCode($to, $name, $text)
    {
        $code = RandomCodeGenerator::generateInt();
        Session::getInstance()->set($name, $code);
        $this->send($to, $text, $code);
    }

    abstract public function send($to, $text, $code);
}