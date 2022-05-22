<?php

namespace Models;

use Models\Brokers\ConnectionBroker;
use Models\Brokers\UserBroker;
use Zephyrus\Application\Session;
use Zephyrus\Network\Cookie;
use Zephyrus\Security\Cryptography;

class UserMemory
{
    public static function checkMemory($remember, $id, $cookie)
    {
        if ($remember) {
            self::remember($id, $cookie);
        } else {
            self::forget($cookie);
        }
        return $cookie;
    }

    private static function remember($id, $cookie)
    {
        $broker = new ConnectionBroker();
        Session::getInstance()->destroy();
        $cookie->setLifetime(Cookie::DURATION_MONTH);
        session_set_cookie_params(Cookie::DURATION_MONTH);
        session_start();
        $broker->insert($id);
    }

    private static function forget($cookie)
    {
        $cookie->setLifetime(1800);
        Session::getInstance()->destroy();
        session_set_cookie_params(1800);
        session_start();
    }
}