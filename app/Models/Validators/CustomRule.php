<?php namespace Models\Validators;

use Models\Authenticator;
use Models\Brokers\UserBroker;
use Models\Mfa\EmailSender;
use Models\Mfa\GoogleAuthenticator;
use Models\Mfa\SmsSender;
use Zephyrus\Application\Rule;
use Zephyrus\Security\Cryptography;
use function PHPUnit\Framework\isNull;

class CustomRule
{
    public static function passwordValid(string $errorMessage = "The provided current password is not valid"): Rule
    {
        return new Rule(function ($data) {
            $broker = new UserBroker();
            return Cryptography::verifyHashedPassword($data, $broker->getPassword());
        }, $errorMessage);
    }

    public static function googleCodeValid(string $key, string $errorMessage = "The Google authentication code is incorrect"): Rule
    {
        return new Rule(function ($data) use ($key) {
            return (new GoogleAuthenticator)->validateCode(str_replace(" ", "", $data), $key);
        }, $errorMessage);
    }

    public static function emailCodeValid(string $errorMessage = "The email authentication code is incorrect"): Rule
    {
        return new Rule(function ($data) {
            return EmailSender::verifySentCode($data);
        }, $errorMessage);
    }

    public static function phoneCodeValid(string $errorMessage = "The phone authentication code is incorrect"): Rule
    {
        return new Rule(function ($data) {
            return SmsSender::verifySentCode($data);
        }, $errorMessage);
    }

    public static function usernameDoesntExist(string $errorMessage = "This username is already taken")
    {
        return new Rule(function ($data) {
            return !(new UserBroker())->isUsernameTaken($data);
        }, $errorMessage);
    }

    public static function emailDoesntExist(string $errorMessage = "This email is already taken")
    {
        return new Rule(function ($data) {
            return !(new UserBroker())->isEmailTaken($data);
        }, $errorMessage);
    }
}
