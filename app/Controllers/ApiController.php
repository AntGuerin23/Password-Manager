<?php

namespace Controllers;

use Models\Brokers\PasswordBroker;
use Models\Brokers\UserBroker;

class ApiController extends Controller
{
    const ERROR_JSON = "{\"username\": \"\" , \"password\": \"\"}";

    public function initializeRoutes()
    {
        $this->post("/api/passwords", "getJson");
        // /api/authentication
    }

    public function getJson() {
        $post = $this->buildForm()->buildObject();
        $broker = new UserBroker();
        $id = $broker->tryAuthenticating($post->username, $post->password);
        if ($id == null) {
            return $this->json(self::ERROR_JSON);
        }
        $result = (new PasswordBroker())->findByDomain($id, $post->domain);
        if ($result == null) {
            return $this->json(self::ERROR_JSON);
        }
        $response = $this->json(json_encode($result));
        $response->addHeader("Access-Control-Allow-Origin", "*");
        return $response;
    }
}