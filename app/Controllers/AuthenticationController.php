<?php

namespace Controllers;

use Models\Brokers\UserBroker;
use Models\Redirector;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;
use Zephyrus\Network\Cookie;
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
        $loginInfo = $form->buildObject();
        $id = $this->tryAuthenticating($loginInfo);
        if (!is_null($id)) {
            //TODO : 2FA if enabled
            $this->configureSession($loginInfo, $id);
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
        $form->field("password")->validate(Rule::passwordCompliant("Password must contain at least one uppercase, one lowercase and one number (8 chars min)"));
        $form->field("password-confirm")->validate(Rule::sameAs("password", "The two passwords do not match"));
        return $form;
    }

    private function tryAuthenticating($loginInfo): int | null
    {
        $broker = new UserBroker();
        $userid = $broker->tryAuthenticating($loginInfo->username, $loginInfo->password);
        if (!is_null($userid)) {
            return $userid;
        } else {
            Flash::error("You have provided an invalid username or password ❌");
            return null;
        }
    }

    private function tryRegister($form): bool
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

    private function configureSession($loginInfo, $id) {
        if (isset($loginInfo->remember)) {
            $this->rememberUser();
        }
        Session::getInstance()->refresh();
        Session::getInstance()->set("currentUser", $id);
    }

    private function rememberUser() {
        Session::getInstance()->destroy();
        session_set_cookie_params(Cookie::DURATION_MONTH);
        session_start();
        //TODO : Insert connection into bd
    }
}