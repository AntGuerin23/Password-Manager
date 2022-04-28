<?php

namespace Models\Brokers;

use Models\Encryption;
use Zephyrus\Application\Session;

class PasswordBroker extends Broker
{
    public function findAllById($id)
    {
        $result = $this->select("SELECT * FROM password WHERE user_id = ? ORDER BY id", [$id]);
        if ($result != null) {
            return $this->decryptResult($result);
        }
        return $result;
    }

    public function insert($form)
    {
        $cipher = Encryption::encryptPassword($form->sitePassword);
        $this->query("INSERT INTO password (user_id, domain, username, password) VALUES (?, ?, ?, ?)", [Session::getInstance()->read("currentUser"), $form->appName, $form->siteUsername, $cipher]);
    }

    public function delete($id)
    {
        $this->query("DELETE FROM password WHERE id = ?", [$id]);
    }

    public function modify($password, $id)
    {
        $cipher = Encryption::encryptPassword($password);
        $this->query("UPDATE password SET password = ? WHERE id = ?", [$cipher, $id]);
    }

    public function findByDomain($user_id, $domain)
    {
        $result =  $this->selectSingle("SELECT username, password FROM password WHERE user_id = ? AND domain LIKE '%' || ? || '%' ORDER BY id", [$user_id, $domain]);
        return $result->password = Encryption::decryptPassword($result->password);
    }

    public function deleteForUser($id)
    {
        $this->query("DELETE FROM password WHERE user_id = ?", [$id]);
    }

    private function decryptResult($result)
    {
        foreach ($result as $password) {
            $password->password = Encryption::decryptPassword($password->password);
        }
        return $result;
    }
}