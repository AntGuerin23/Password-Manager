<?php

namespace Models;

use Models\Brokers\ConnectionBroker;
use Zephyrus\Application\Session;

class ConnectionUpdater
{
    public static function update()
    {
        $session_id = Session::getInstance()->getId();
        $broker = new ConnectionBroker();
        $connection = $broker->getActiveConnection();
        if (!is_null($connection)) {
            $broker->updateLastLogin($connection);
        }
    }

    public static function disconnect() {
        $broker = new ConnectionBroker();
        $connection = $broker->getActiveConnection();
        if ($connection != null) {
            $broker->delete($connection);
        }
    }
}