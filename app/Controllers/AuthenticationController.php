<?php

namespace Controllers;

use Models\Brokers\UserBroker;
use Models\Redirector;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;
use Zephyrus\Network\Response;

class AuthenticationController extends Controller
{
    public function before(): ?Response
    {
        if (Redirector::isForbidden(false)) {
            return $this->redirect("/");
        }
        return parent::before();
    }

    public function initializeRoutes()
    {
        $this->get("/login", "login");
        $this->post("/login", "authenticate");
        $this->get("/register", "register");
        $this->post("/register", "createUser");
    }

    public function login()
    {
        return $this->render("login", [
            "title" => "Login",
        ]);
    }

    public function authenticate()
    {
        $form = $this->buildLoginForm();
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("login");
        }
        if ($this->tryAuthenticating($form)) {
            return $this->redirect("/");
        }
        return $this->redirect("login");
    }

    public function register()
    {
        return $this->render("register", [
            "title" => "Register"
        ]);
    }

    public function createUser()
    {
        $form = $this->buildRegisterForm();
        //TODO : Check if both email and username already exist (custom rule)
        if ($this->tryRegister($form)) {
            return $this->redirect("/login");
        }
        return $this->redirect("/register");
    }

    private function buildLoginForm(): Form
    {
        $form = $this->buildForm();
        $form->field("username")->validate(Rule::notEmpty("Please enter a username"));
        $form->field("password")->validate(Rule::notEmpty("Please enter a password"));
        return $form;
    }

    private function buildRegisterForm(): Form
    {
        $form = $this->buildForm();
        $form->field("email")->validate(Rule::email("Please enter a valid email"));
        $form->field("username")->validate(Rule::notEmpty("Please enter a username"));
        $form->field("password")->validate(Rule::notEmpty("Please enter a password"));
        $form->field("password-confirm")->validate(Rule::sameAs("password", "The two passwords do not match"));
        return $form;
    }

    private function tryAuthenticating($form) : bool
    {
        $loginInfo = $form->buildObject();
        $broker = new UserBroker();
        if ($broker->tryAuthenticating($loginInfo->username, $loginInfo->password)) {
            //TODO : 2FA if enabled
            return true;
        } else {
            Flash::error("You have provided an invalid username or password ❌");
            return false;
        }
    }

    private function tryRegister($form) : bool
    {
        if ($form->verify()) {
            $broker = new UserBroker();
            $broker->insert($form->buildObject());
            Flash::success("Your account has been successfully created ✔️");
            //TODO : Confirm email
            return true;
        } else {
            Flash::error($form->getErrorMessages());
            return false;
        }
    }
}