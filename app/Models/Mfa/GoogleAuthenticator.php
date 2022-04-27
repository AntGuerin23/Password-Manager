<?php

namespace Models\Mfa;

use chillerlan\QRCode\QRCode;
use Models\Brokers\UserBroker;
use PragmaRX\Google2FA\Google2FA;
use Zephyrus\Application\Session;

class GoogleAuthenticator
{
    private $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function getQrCode($key): string
    {
        $broker = new UserBroker();
        return (new QRCode())->render($this->google2fa->getQRCodeUrl(
            'SosPass',
            $broker->getEmail(Session::getInstance()->read("currentUser")),
            $key));
    }

    public function generateKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function validateCode($input, $key): bool
    {
        $this->google2fa->setWindow(30);
        return $this->google2fa->verifyKey($key, $input);
    }
}