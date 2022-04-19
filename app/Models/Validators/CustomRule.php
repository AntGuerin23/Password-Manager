<?php namespace Models\Validators;

use Models\Brokers\UserBroker;
use Zephyrus\Application\Rule;
use Zephyrus\Security\Cryptography;

class CustomRule
{
    public static function passwordValid(string $errorMessage = "The provided current password is not valid"): Rule
    {
        return new Rule(function ($data) {
            $broker = new UserBroker();
            return Cryptography::verifyHashedPassword($data, $broker->getPassword());
        });
    }
}
