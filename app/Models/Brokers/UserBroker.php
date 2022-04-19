<?php

namespace Models\Brokers;

use stdClass;
use Zephyrus\Application\Session;
use Zephyrus\Security\Cryptography;

class UserBroker extends Broker
{

    public function findById($id)
    {
        return $this->selectSingle("SELECT * FROM \"user\" WHERE id  = ?", [$id]);
    }

    public function insert(stdClass $client): int
    {
        $this->query("INSERT INTO \"user\"(username, email, password) VALUES (?, ?, ?)", [
            $client->username,
            $client->email,
            Cryptography::hashPassword($client->password)
        ]);
        return $this->getDatabase()->getLastInsertedId();
    }

    public function findUsernameById($id)
    {
        $result = $this->findById($id);
        return $result->username;
    }

    public function tryAuthenticating($email, $password) : bool
    {
        $user = $this->selectSingle("SELECT * FROM \"user\" WHERE username  = ?", [$email]);

        if ($user == null) return false;
        if (Cryptography::verifyHashedPassword($password, $user->password)) {
            Session::getInstance()->refresh();
            Session::getInstance()->set("currentUser", $user->id);
            return true;
        } else {
            return false;
        }
    }
}