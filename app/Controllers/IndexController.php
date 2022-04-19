<?php

namespace Controllers;

use Models\Brokers\PasswordBroker;
use Models\Brokers\UserBroker;
use Models\Redirector;
use Zephyrus\Application\Session;
use Zephyrus\Network\Response;

class IndexController extends Controller
{

    public function before(): ?Response
    {
        if (Redirector::isForbidden(true)) {
            return $this->redirect("login");
        }
        return parent::before();
    }

    public function initializeRoutes()
    {
        $this->get("/", "index");
        $this->delete("/login", "logout");
    }

    public function index()
    {
        $broker = new PasswordBroker();
        $passwords = $broker->findAllById(Session::getInstance()->read("currentUser"));
        foreach ($passwords as $password) {
            $password->{"imgPath"} = getImagePath($password->name);
        }
        $userBroker = new UserBroker();
        return $this->render("index", [
            "title" => "See your passwords",
            "location" => "/",
            "username" => $userBroker->findUsernameById(Session::getInstance()->read("currentUser")),
            "passwords" => $passwords
        ]);
    }

    public function logout()
    {
        Session::getInstance()->remove("currentUser");
        return $this->redirect("/");
    }
}