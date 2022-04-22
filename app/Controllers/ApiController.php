<?php

namespace Controllers;

use Models\Brokers\PasswordBroker;

class ApiController extends Controller
{

    public function initializeRoutes()
    {
        $this->get("/api/{id}", "getJson");
    }

    public function getJson($id) {
        $broker = new PasswordBroker();
        $response = $this->json(json_encode($broker->findAllById($id)));
        $response->addHeader("Access-Control-Allow-Origin", "*");
        return $response;
    }
}