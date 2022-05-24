<?php

namespace Models;

use Exception;
use Zephyrus\Application\Session;
use Zephyrus\Network\Cookie;
use Zephyrus\Security\Cryptography;

class Encryption
{
    public static function encryptPassword($password): string
    {
        $cipher = Cryptography::encrypt($password, getenv(ENCRYPTION_KEY));
        if (!$cookie = Cookie::read("userKey")) {
            throw new Exception();
        }
        return Cryptography::encrypt($cipher, $cookie);
    }

    public static function decryptPassword($cipher, $encryptionKey = null): string
    {
        if ($encryptionKey != null) {
            $halfCipher = Cryptography::decrypt($cipher, $encryptionKey); //For extension, js will deal with null check
        } else {
            if (!$cookie = Cookie::read("userKey")) {
                throw new Exception();
            }
            if (!$halfCipher = Cryptography::decrypt($cipher, $cookie)) {
                throw new Exception();
            }
        }
        return Cryptography::decrypt($halfCipher, getenv(ENCRYPTION_KEY));
    }
}