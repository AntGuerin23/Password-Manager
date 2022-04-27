<?php

namespace Models\Brokers;

use Zephyrus\Application\Session;
use Zephyrus\Security\Cryptography;

class PasswordBroker extends Broker
{
    public function findAllById($id)
    {
        return $this->select("SELECT * FROM password WHERE user_id = ? ORDER BY id", [$id]);
    }

    public function insert($form)
    {
        //TODO : Encrypt
        $this->query("INSERT INTO password (user_id, domain, username, password) VALUES (?, ?, ?, ?)", [Session::getInstance()->read("currentUser"), $form->appName, $form->siteUsername, $form->sitePassword]);
    }

    public function delete($id)
    {
        $this->query("DELETE FROM password WHERE id = ?", [$id]);
    }

    public function modify($password, $id)
    {
        //TODO : Encrypt
        $this->query("UPDATE password SET password = ? WHERE id = ?", [$password, $id]);
    }

    public function findByDomain($user_id, $domain) {
        //TODO :Decrypt
        return $this->selectSingle("SELECT username, password FROM password WHERE user_id = ? AND domain LIKE '%' || ? || '%' ORDER BY id", [$user_id, $domain]);
    }
}