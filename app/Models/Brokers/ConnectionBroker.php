<?php

namespace Models\Brokers;

use Zephyrus\Application\Session;
use Zephyrus\Utilities\Formatter;

class ConnectionBroker extends Broker
{
    //Get every connection for user

    //Calculate time remaining, NOW - (connection_time + 30)

    //

    public function insert()
    {
        $this->query("INSERT INTO connection (user_id, ip, browser, last_login, connection_time) VALUES (?, ?, ?, ?, ?)", [
            Session::getInstance()->read("loginId"),
            $_SERVER["REMOTE_ADDR"],
            get_browser($_SERVER["HTTP_USER_AGENT"])->browser,
            getCurrentDate(),
            getCurrentDate()
        ]);
    }

    public function updateLastLogin() {
        $this->query("UPDATE connection SET last_login = ? WHERE user_id = ?", [getCurrentDate(), Session::getInstance()->read("currentUser")]);
    }
}