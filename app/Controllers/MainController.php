<?php

namespace Controllers;

use Models\Brokers\UserBroker;
use Models\Redirector;
use Zephyrus\Application\Session;
use Zephyrus\Network\Response;

class MainController extends Controller
{

    public function before() : ?Response
    {
        if (Redirector::isForbidden(true)) {
            return $this->redirect("login");
        }
        return parent::before();
    }

    public function initializeRoutes()
    {
        $this->get("/", "index");
        $this->get("/profile", "profile");
        $this->get("/add-password", "addPassword");
        $this->delete("/login", "logout");
    }

    public function index()
    {
        //Get every password for user
        return $this->render("index", [
            "title" => "See your passwords",
            "location" => "/"
        ]);
    }

    public function profile()
    {
        return $this->render("profile", [
            "title" => "Manage your profile",
            "location" => "profile"
        ]);
    }

    public function addPassword()
    {
        return $this->render("add-password", [
            "title" => "Add a new password",
            "location" => "add-password"
        ]);
    }

    public function logout() {
        Session::getInstance()->remove("currentUser");
        return $this->redirect("/");
    }
}