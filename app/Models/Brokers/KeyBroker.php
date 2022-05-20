<?php

namespace Models\Brokers;

use Models\SessionHelper;
use MongoDB\Driver\Session;
use Zephyrus\Database\Core\Database;

class KeyBroker extends Broker
{
    public function getUserFromKey($key) : int | null
    {
        $result = $this->selectSingle("SELECT user_id FROM \"apiKey\" WHERE key = ?", [$key]);
        if ($result != null) {
            return $result->user_id;
        }
        return null;
    }

    public function insert($id, $key) {
        $this->query("INSERT INTO \"apiKey\" (user_id, key) VALUES (?, ?)", [$id, $key]);
    }

    public function updateKey($key) {
        $this->query("UPDATE \"apiKey\" set key = ? WHERE user_id = ?", [$key, SessionHelper::getUserId()]);
    }
}