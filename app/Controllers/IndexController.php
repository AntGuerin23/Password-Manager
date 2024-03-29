<?php

namespace Controllers;

use Exception;
use Models\Brokers\ConnectionBroker;
use Models\Brokers\PasswordBroker;
use Models\Brokers\UserBroker;
use Models\ConnectionUpdater;
use Models\Mfa\EmailSender;
use Models\Redirector;
use Zephyrus\Application\Session;
use Zephyrus\Network\Cookie;
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
        $this->get("/", "indexPage");
        $this->delete("/login", "logout");
        $this->get("/passwords", "export");
    }

    public function indexPage(): Response
    {
        //try {
            $passwords = $this->getPasswords();
//        }
//        catch (Exception) {
//            return $this->logout();
//        }
        return $this->render("index", [
            "title" => "See your passwords",
            "location" => "/",
            "username" => (new UserBroker)->getUsername(),
            "passwords" => $passwords
        ]);
    }

    public function logout(): Response
    {
        ConnectionUpdater::disconnect();
        setcookie("userKey", "", 1);
        Session::getInstance()->restart();
        return $this->redirect("/");
    }

    public function export(): Response
    {
        $passwords = (new PasswordBroker())->findAllForUser(Session::getInstance()->read("currentUser"));
        $content = "sep=,\nSite,Username,Password\n";
        foreach ($passwords as $password) {
            $content .= "$password->domain,$password->username,$password->password\n";
        }
        return $this->downloadContent($content, "passwords.csv", "application/CSV");
    }

    private function getPasswords(): array
    {
        $broker = new PasswordBroker();
        $passwords = $broker->findAllForUser(Session::getInstance()->read("currentUser"));
        foreach ($passwords as $password) {
            $password->{"imgPath"} = getImagePath($password->domain);
        }
        return $passwords;
    }
}