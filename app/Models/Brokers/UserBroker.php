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

    public function findUsernameById($id) : null | string
    {
        $result = $this->findById($id);
        if (is_null($result)) {
            return null;
        }
        return $result->username;
    }

    public function tryAuthenticating($email, $password): int | null
    {
        $user = $this->selectSingle("SELECT * FROM \"user\" WHERE username  = ?", [$email]);
        if ($user == null) return null;
        if (Cryptography::verifyHashedPassword($password, $user->password)) {
            return $user->id;
        } else {
            return null;
        }
    }

    public function updatePassword($newPassword)
    {
        $this->query("UPDATE \"user\" SET password = ? WHERE id = ?", [Cryptography::hashPassword($newPassword), Session::getInstance()->read("currentUser")]);
    }

    public function getPassword() : string
    {
        return $this->selectSingle("SELECT password FROM \"user\" WHERE id = ?", [Session::getInstance()->read("currentUser")])->password;
    }
}