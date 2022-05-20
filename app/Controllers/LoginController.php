<?php

namespace Controllers;

use http\Client\Curl\User;
use Models\Authenticator;
use Models\Brokers\ConnectionBroker;
use Models\Brokers\UserBroker;
use Models\Mfa\EmailSender;
use Models\Mfa\MfaChecker;
use Models\Redirector;
use Models\UserMemory;
use Models\Validators\LoginValidator;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;
use Zephyrus\Network\Cookie;
use Zephyrus\Network\Response;
use Zephyrus\Security\Cryptography;

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
        $this->get("/login", "loginPage");
        $this->post("/login", "authenticate");
        $this->get("/login/mfa", "mfaPage");
        $this->post("/login/mfa", "validateMfa");
    }

    public function loginPage(): Response
    {
        return $this->render("login", [
            "title" => "Login",
        ]);
    }

    public function authenticate(): Response
    {
        $form = $this->buildForm();
        $form->verify();
        if (!$formObject = LoginValidator::validateLoginForm($form)) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/login");
        }
        return $this->checkIfLoginSucceeded($formObject);
    }

    public function mfaPage(): Response
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
        $this->configureSession(Session::getInstance()->read("remember"), Session::getInstance()->read("loginId"), Session::getInstance()->read("passwordTemp"));
        Session::getInstance()->remove("passwordTemp");
        return $this->redirect("/");
    }

    private function checkIfLoginSucceeded($formObject): Response
    {
        if ($id = Authenticator::tryAuthenticating($formObject->username, $formObject->password)) {
            return $this->redirectDependingOnMfa($id, $formObject);
        }
        Flash::error("You have provided an invalid username or password ❌");
        return $this->redirect("/login");
    }

    private function redirectDependingOnMfa($id, $formObject)
    {
        Session::getInstance()->set("loginId", $id);
        Session::getInstance()->set("passwordTemp", $formObject->password);
        Session::getInstance()->set("remember", (isset($formObject->remember)));
        if (MfaChecker::hasActivatedMethods($id)) {
            return $this->redirect("login/mfa");
        }
        $this->configureSession(Session::getInstance()->read("remember"), Session::getInstance()->read("loginId"), $formObject->password);
        return $this->redirect("/");
    }

    private function sendCodesIfNotAlreadySent()
    {
        if (!Session::getInstance()->has("codesWereSent")) {
            MfaChecker::sendCodes();
            Session::getInstance()->set("codesWereSent", true);
        }
    }

    private function configureSession($remember, $id, $password)
    {
        $cookie = new Cookie("userKey");
        $cookie->setValue(Cryptography::deriveEncryptionKey($password, (new UserBroker())->getSalt()));
        UserMemory::checkMemory($remember, $id, $cookie);
        $cookie->send();
        Session::getInstance()->set("currentUser", $id);
    }
}