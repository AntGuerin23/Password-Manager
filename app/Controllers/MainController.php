<?php

namespace Controllers;

class MainController extends Controller
{

    public function initializeRoutes()
    {
        $this->get("/", "index");
        $this->get("/profile", "profile");
        $this->get("/login", "login");
        $this->get("/register", "register");
        $this->get("/new-password", "newPassword");
    }

    public function index()
    {
        return $this->render("index", [
            "title" => "See your passwords",
            "location" => "index"
        ]);
    }

    public function login()
    {
        return $this->render("login", [
            "title" => "Login",
        ]);
    }

    public function register()
    {
        return $this->render("register", [
            "title" => "Register"
        ]);
    }

    public function profile()
    {
        return $this->render("profile", [
            "title" => "Manage your profile",
            "location" => "profile"
        ]);
    }

    public function newPassword()
    {
        return $this->render("new-password", [
            "title" => "Add a new password",
            "location" => "new-password"
        ]);
    }
}