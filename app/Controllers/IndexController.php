<?php

namespace Controllers;

use Models\Brokers\ConnectionBroker;
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
        //TODO : if session id the same as session id in connection, update last login
        //new ConnectionBroker())->updateLastLogin();
        return parent::before();
    }

    public function initializeRoutes()
    {
        $this->get("/", "index");
        $this->delete("/login", "logout");
        $this->get("/passwords", "export");
    }

    public function index()
    {
        $broker = new PasswordBroker();
        $passwords = $broker->findAllById(Session::getInstance()->read("currentUser"));
        foreach ($passwords as $password) {
            $password->{"imgPath"} = getImagePath($password->domain);
        }
        $userBroker = new UserBroker();
        return $this->render("index", [
            "title" => "See your passwords",
            "location" => "/",
            "username" => $userBroker->getUsername(),
            "passwords" => $passwords
        ]);
    }

    public function logout()
    {
        Session::getInstance()->remove("currentUser");
        return $this->redirect("/");
    }

    public function export()
    {
        $broker = new PasswordBroker();
        $passwords = $broker->findAllById(Session::getInstance()->read("currentUser"));
        $content = "sep=,\nSite,Username,Password\n";
        foreach ($passwords as $password) {
            $content .= "$password->domain, $password->username, $password->password\n";
        }
        return $this->downloadContent($content, "passwords.csv", "application/CSV");
    }
}