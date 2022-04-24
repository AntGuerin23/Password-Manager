<?php

namespace Models\Brokers;

use Zephyrus\Application\Session;

class ConnectionBroker extends Broker
{
    //Get every connection for user

    //Calculate time remaining,  (connection_time + 30) - NOW

    //

    public function insert($user_id)
    {
        $this->query("INSERT INTO connection (user_id, ip, browser, last_login, connection_time, session_id) VALUES (?, ?, ?, ?, ?, ?)", [
            $user_id,
            $_SERVER["REMOTE_ADDR"],
            get_browser($_SERVER["HTTP_USER_AGENT"])->browser,
            getCurrentDate(),
            getCurrentDate(),
            Session::getInstance()->getId()
        ]);
    }

    public function delete($id)
    {
        $this->query("DELETE FROM connection WHERE id = ?", [$id]);
    }

    public function updateLastLogin($id)
    {
        $this->query("UPDATE connection SET last_login = ? WHERE user_id = ? AND id = ?", [getCurrentDate(), Session::getInstance()->read("currentUser"), $id]);
    }

    public function getConnectionsForUser()
    {
        return $this->select("SELECT ip, browser, last_login, DATE_PART('day', (connection_time + interval '30' day) - NOW()) as days_remaining  FROM connection WHERE user_id = ?", [Session::getInstance()->read("currentUser")]);
    }

    public function getActiveConnection(): int | null
    {
        $result = $this->selectSingle("SELECT id FROM connection WHERE session_id = ?", [Session::getInstance()->getId()]);
        if (is_null($result)) {
            return null;
        }
        return $result->id;
    }
}