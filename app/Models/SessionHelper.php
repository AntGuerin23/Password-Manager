<?php

namespace Models;

use Zephyrus\Application\Session;

class SessionHelper
{
    public static function getUserId() {
        $id = Session::getInstance()->read("currentUser");
        if ($id == null) {
            return Session::getInstance()->read("loginId");
        }
        return $id;
    }
}