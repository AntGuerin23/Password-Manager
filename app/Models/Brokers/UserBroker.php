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
        $this->query("INSERT INTO \"user\"(username, email, password, email_mfa) VALUES (?, ?, ?, false)", [
            $client->username,
            $client->email,
            Cryptography::hashPassword($client->password)
        ]);
        return $this->getDatabase()->getLastInsertedId();
    }

    public function tryAuthenticating($email, $password): int|null
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

    public function updateAuthKey($key)
    {
        $this->query("UPDATE \"user\" SET google_auth_key = ? WHERE id = ?", [$key, Session::getInstance()->read("currentUser")]);
    }

    public function updateEmailMfa($isActivated)
    {
        $this->query("UPDATE \"user\" SET email_mfa = ? WHERE id = ?", [$isActivated, Session::getInstance()->read("currentUser")]);
    }

    public function updatePhoneNb($phoneNb)
    {
        $this->query("UPDATE \"user\" SET phone_nb = ? WHERE id = ?", [$phoneNb, Session::getInstance()->read("currentUser")]);
    }

    public function isPhoneMfaSet($id): bool
    {
        $result = $this->selectSingle("SELECT phone_nb FROM \"user\" WHERE id = ?", [$id]);
        return (!is_null($result->phone_nb));
    }

    public function isEmailMfaSet($id): bool
    {
        $result = $this->selectSingle("SELECT email_mfa FROM \"user\" WHERE id = ?", [$id]);
        return $result->email_mfa;
    }

    public function isGoogleMfaSet($id): bool
    {
        $result = $this->selectSingle("SELECT google_auth_key FROM \"user\" WHERE id = ?", [$id]);
        return !is_null($result->google_auth_key);
    }

    public function getPassword(): string
    {
        return $this->selectSingle("SELECT password FROM \"user\" WHERE id = ?", [Session::getInstance()->read("currentUser")])->password;
    }

    public function getUsername(): null|string
    {
        $result = $this->findById(Session::getInstance()->read("currentUser"));
        return $result->username;
    }

    public function getAuthKey($id)
    {
        $result = $this->findById($id);
        return $result->google_auth_key;
    }

    public function getEmail()
    {
        $result = $this->findById(Session::getInstance()->read("currentUser"));
        return $result->email;
    }

    public function getPhoneNb($id)
    {
        $result = $this->findById($id);
        return $result->phone_nb;
    }
}