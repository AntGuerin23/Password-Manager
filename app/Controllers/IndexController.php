<?php

namespace Controllers;

use Models\Brokers\ConnectionBroker;
use Models\Brokers\PasswordBroker;
use Models\Brokers\UserBroker;
use Models\ConnectionUpdater;
use Models\Mfa\EmailSender;
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
        ConnectionUpdater::update();
        return parent::before();
    }

    public function initializeRoutes()
    {
        $this->get("/", "index");
        $this->delete("/login", "logout");
        $this->get("/passwords", "export");
    }

    public function index(): Response
    {
        return $this->render("index", [
            "title" => "See your passwords",
            "location" => "/",
            "username" => (new UserBroker)->getUsername(),
            "passwords" => $this->getPasswords()
        ]);
    }

    public function logout(): Response
    {
        Session::getInstance()->restart();
        ConnectionUpdater::disconnect();
        return $this->redirect("/");
    }

    public function export(): Response
    {
        $passwords = (new PasswordBroker())->findAllById(Session::getInstance()->read("currentUser"));
        $content = "sep=,\nSite,Username,Password\n";
        foreach ($passwords as $password) {
            $content .= "$password->domain,$password->username,$password->password\n";
        }
        return $this->downloadContent($content, "passwords.csv", "application/CSV");
    }

    private function getPasswords(): array
    {
        $broker = new PasswordBroker();
        $passwords = $broker->findAllById(Session::getInstance()->read("currentUser"));
        foreach ($passwords as $password) {
            $password->{"imgPath"} = getImagePath($password->domain);
        }
        return $passwords;
    }
}