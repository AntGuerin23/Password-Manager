<?php

namespace Controllers;

use Models\Brokers\KeyBroker;
use Models\Brokers\PasswordBroker;
use Models\Brokers\UserBroker;
use Models\Validators\ApiValidator;
use Zephyrus\Application\Rule;
use Zephyrus\Security\Cryptography;

class ApiController extends Controller
{

    public function initializeRoutes()
    {
        $this->post("/api/passwords", "getJson");
        $this->post("/api/authenticate", "authenticate");
    }

    public function getJson()
    {
        if ($result = ApiValidator::validatePasswordJson($this->buildForm())) {
            return $this->json($result);
        }
        return null;
    }

    public function authenticate()
    {
        if (!$formObject = ApiValidator::validateAuthentication($this->buildForm())) {
            return $this->json(array('valid' => false));
        }
        $encryptionKey = Cryptography::deriveEncryptionKey($formObject->password, (new UserBroker())->getSalt(8));
        $apiKey = Cryptography::hash(Cryptography::randomHex());
        (new KeyBroker())->insert($this->getUser($formObject), $apiKey);
        return $this->json(array('valid' => true, 'apiKey' => $apiKey, "encryptionKey" => $encryptionKey));
    }

    private function getUser($loginInfo) : int
    {
        $broker = new UserBroker();
        return $broker->tryAuthenticating($loginInfo->username, $loginInfo->password);
    }
}