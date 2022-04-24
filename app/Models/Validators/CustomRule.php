<?php namespace Models\Validators;

use Models\Brokers\UserBroker;
use Models\Mfa\GoogleAuthenticator;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;
use Zephyrus\Security\Cryptography;

class CustomRule
{
    public static function passwordValid(string $errorMessage = "The provided current password is not valid"): Rule
    {
        return new Rule(function ($data) {
            $broker = new UserBroker();
            return Cryptography::verifyHashedPassword($data, $broker->getPassword());
        }, $errorMessage);
    }

    public static function googleCodeValid(string $key, string $errorMessage = "The provided authentication code is not valid"): Rule
    {
        return new Rule(function ($data) use ($key) {
            return (new GoogleAuthenticator)->validateCode($data, $key);
        }, $errorMessage);
    }
}
