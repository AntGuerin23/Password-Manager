<?php

namespace Models\Brokers;

use Models\Encryption;
use Models\SessionHelper;
use Zephyrus\Application\Session;

class PasswordBroker extends Broker
{
    public function findAllForUser($id)
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

    public function findByDomain($user_id, $domain, $encryptionKey = null)
    {
        $result =  $this->selectSingle("SELECT username, password FROM password WHERE user_id = ? AND domain LIKE '%' || ? || '%' ORDER BY id", [$user_id, $domain]);
        if ($result == null) {
            return null;
        }
        $result->password = Encryption::decryptPassword($result->password, $encryptionKey);
        return $result;
    }

    public function deleteForUser($id)
    {
        $this->query("DELETE FROM password WHERE user_id = ?", [$id]);
    }

    public function passwordBelongsToUser($id): bool
    {
        $result =  $this->selectSingle("SELECT * FROM password WHERE user_id = ? AND id = ?", [SessionHelper::getUserId(), $id]);
        return $result != null;
    }

    private function decryptResult($result)
    {
        foreach ($result as $password) {
            $password->password = Encryption::decryptPassword($password->password);
        }
        return $result;
    }
}