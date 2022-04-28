<?php

namespace Models;

use Models\Brokers\ConnectionBroker;
use Zephyrus\Application\Session;

class ConnectionUpdater
{
    public static function update()
    {
        $broker = new ConnectionBroker();
        $connection = $broker->getActiveConnectionId();
        if (!is_null($connection)) {
            $broker->updateLastLogin($connection);
        }
    }

    public static function disconnect() {
        $broker = new ConnectionBroker();
        $connection = $broker->getActiveConnectionId();
        if ($connection != null) {
            $broker->delete($connection);
        }
    }
}