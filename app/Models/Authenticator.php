<?php

namespace Models;

use Models\Brokers\UserBroker;
use Zephyrus\Application\Flash;

class Authenticator
{
    public static function tryAuthenticating($username, $password): int|null
    {
        $broker = new UserBroker();
        $userid = $broker->tryAuthenticating($username, $password);
        if (!is_null($userid)) {
            return $userid;
        } else {
            Sleep(3);
            return null;
        }
    }
}