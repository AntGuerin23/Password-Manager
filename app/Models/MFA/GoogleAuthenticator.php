<?php

namespace Models\MFA;

use chillerlan\QRCode\QRCode;
use PragmaRX\Google2FA\Google2FA;

class GoogleAuthenticator
{
    private $google2fa;

    //Generate key
    //get qr code


    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function getQrCode($key): string
    {
        return (new QRCode())->render($this->google2fa->getQRCodeUrl(
            'SosPass',
            "Holder",
            $key));
    }

    public function generateKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function validateCode($key, $input)
    {
        //probably doesn't work
        $this->google2fa->verifyKey($input, $key);
    }
}