<?php

namespace Models;

use Zephyrus\Application\Session;

class Redirector
{
    public static function isForbidden($isInsideSite) : bool
    {
        if (!Session::getInstance()->has("currentUser") && $isInsideSite) {
            return true;
        }
        if (Session::getInstance()->has("currentUser") && !$isInsideSite) {
            return true;
        }
        return false;
    }
}