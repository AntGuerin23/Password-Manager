<?php

namespace Models;

use Zephyrus\Security\Cryptography;

class RandomCodeGenerator
{
    public static function generateInt(): int
    {
        return Cryptography::randomInt("100000", "999999");
    }

    public static function generateApiKey() : string
    {
        //TODO : Generate key
        return "";
    }
}