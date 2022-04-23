<?php

namespace Controllers;

use chillerlan\QRCode\QRCode;
use Models\Brokers\PasswordBroker;
use Models\Brokers\UserBroker;
use Models\MFA\GoogleAuthenticator;
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
            "username" => $userBroker->findUsernameById(Session::getInstance()->read("currentUser")),
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
        $content = "sep=,\nSite,Password\n";
        foreach ($passwords as $password) {
            $content .= "$password->domain,$password->password\n";
        }
        return $this->downloadContent($content, "passwords.csv", "application/CSV");
    }
}