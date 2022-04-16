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
    }

    public function index()
    {
        return $this->render("index");
    }

    public function login()
    {
        return $this->render("");
    }

    public function register()
    {
        return $this->render("");
    }

    public function profile()
    {
        return $this->render("");
    }
}