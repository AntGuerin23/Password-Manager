<?php

namespace Controllers;

use Models\Brokers\UserBroker;
use Models\Mfa\MfaChecker;
use Models\Redirector;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;
use Zephyrus\Network\Cookie;
use Zephyrus\Network\Response;

class LoginController extends Controller
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
        $this->get("/login/mfa", "mfa");
        $this->post("/login/mfa", "validateMfa");
    }

    public function login(): Response
    {
        Session::getInstance()->restart();
        return $this->render("login", [
            "title" => "Login",
        ]);
    }

    public function authenticate(): Response
    {
        $form = $this->buildLoginForm();
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/login");
        }
        return $this->checkIfLoginSucceeded($form);
    }

    public function mfa(): Response
    {
        if (!Session::getInstance()->has("remember") || !Session::getInstance()->has("loginId")) {
            return $this->redirect("/login");
        }
        $this->sendCodesIfNotAlreadySent();
        return $this->render("mfa-connect", [
            "title" => "Multi-factor authentication",
            "activated" => MfaChecker::getActivatedMethods(Session::getInstance()->read("loginId"))
        ]);
    }

    public function validateMfa(): Response
    {
        $form = $this->buildForm();
        $newForm = MfaChecker::setupValidation($form, Session::getInstance()->read("loginId"));
        if (!$newForm->verify()) {
            Flash::error($newForm->getErrorMessages());
            return $this->redirect("/login/mfa");
        }
        Session::getInstance()->remove("codesWereSent");
        $this->configureSession(Session::getInstance()->read("remember"), Session::getInstance()->read("loginId"));
        return $this->redirect("/");
    }

    private function buildLoginForm(): Form
    {
        $form = $this->buildForm();
        $form->field("username")->validate(Rule::notEmpty("Please enter a username"));
        $form->field("password")->validate(Rule::notEmpty("Please enter a password"));
        return $form;
    }

    private function tryAuthenticating($loginInfo): int|null
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

    private function rememberUser()
    {
        Session::getInstance()->destroy();
        session_set_cookie_params(Cookie::DURATION_MONTH);
        session_start();
        //TODO : Insert connection into bd
    }

    private function checkIfLoginSucceeded($form): Response
    {
        $loginInfo = $form->buildObject();
        $id = $this->tryAuthenticating($loginInfo);
        if (!is_null($id)) {
            return $this->redirectDependingOnMfa($id, $loginInfo);
        }
        return $this->redirect("/login");
    }

    private function redirectDependingOnMfa($id, $loginInfo)
    {
        if (MfaChecker::hasActivatedMethods($id)) {
            Session::getInstance()->set("loginId", $id);
            Session::getInstance()->set("remember", $this->checkForRemember($loginInfo));
            return $this->redirect("login/mfa");
        }
        $this->configureSession($this->checkForRemember($loginInfo), $id);
        return $this->redirect("/");
    }

    private function sendCodesIfNotAlreadySent()
    {
        if (!Session::getInstance()->has("codesWereSent")) {
            MfaChecker::sendCodes();
            Session::getInstance()->set("codesWereSent", true);
        }
    }

    private function configureSession($remember, $id)
    {
        if ($remember) {
            $this->rememberUser();
        }
        Session::getInstance()->refresh();
        Session::getInstance()->set("currentUser", $id);
    }

    private function checkForRemember($loginInfo): bool
    {
        return (isset($loginInfo->remember));
    }
}