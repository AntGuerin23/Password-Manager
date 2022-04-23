<?php

namespace Models\MFA;

use chillerlan\QRCode\QRCode;
use Models\Brokers\UserBroker;
use PragmaRX\Google2FA\Google2FA;

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
            $broker->getEmail(),
            $key));
    }

    public function generateKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function validateCode($input): bool
    {
        $broker = new UserBroker();
        return $this->google2fa->verifyKey($broker->getAuthKey(), $input);
    }
}