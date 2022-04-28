<?php

namespace Models;

use Zephyrus\Application\Session;
use Zephyrus\Security\Cryptography;

class Encryption
{
    public static function encryptPassword($password): string
    {
        $cipher = Cryptography::encrypt($password, getenv(ENCRYPTION_KEY));
        return Cryptography::encrypt($cipher, Session::getInstance()->read("userKey"));
    }

    public static function decryptPassword($cipher): string
    {
        $halfCipher = Cryptography::decrypt($cipher, Session::getInstance()->read("userKey"));
        return Cryptography::decrypt($halfCipher, getenv(ENCRYPTION_KEY));
    }
}