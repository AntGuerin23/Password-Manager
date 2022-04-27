<?php

namespace Controllers;

use Models\Brokers\UserBroker;
use Models\Mfa\GoogleAuthenticator;
use Models\Redirector;
use Models\Validators\CustomRule;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;
use Zephyrus\Network\Response;

class MfaController extends Controller
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
        $this->post("/profile/email-mfa", "setupEmailMfa");
        $this->post("/profile/phone-mfa", "setupPhoneMfa");
        $this->get("/profile/authenticator", "setupAuthenticatorPage");
        $this->post("/profile/authenticator/test", "testAuthenticator");
        $this->delete("/profile/email-mfa", "removeEmailMfa");
        $this->delete("/profile/phone-mfa", "removePhoneMfa");
        $this->delete("/profile/authenticator", "removeGoogleMfa");
    }

    public function setupAuthenticatorPage(): Response
    {
        if ((new UserBroker())->isGoogleMfaSet(Session::getInstance()->read("currentUser"))) {
            Flash::error("You already have added this MFA method ❌");
            return $this->redirect("/profile");
        }
        $authenticator = new GoogleAuthenticator();
        $key = $authenticator->generateKey();
        Session::getInstance()->set("auth_key", $key);
        $qrCode = $authenticator->getQrCode($key);
        return $this->render("authenticator-setup", [
            "title" => "Setup Authenticator",
            "location" => "authenticator-setup",
            "username" => (new UserBroker())->getUsername(),
            "qrCode" => $qrCode
        ]);
    }

    public function testAuthenticator(): Response
    {
        $form = $this->buildForm();
        $form->field("code")->validate(CustomRule::googleCodeValid(Session::getInstance()->read("auth_key")));
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/profile/authenticator-setup");
        }
        $broker = new UserBroker();
        $broker->updateAuthKey(Session::getInstance()->read("auth_key"));
        Flash::success("Google MFA has been successfully added to your account ✔️");
        return $this->redirect("/profile");
    }

    public function setupEmailMfa(): Response
    {
        $broker = new UserBroker();
        if ($broker->isEmailMfaSet(Session::getInstance()->read("currentUser"))) {
            Flash::error("You already have added this MFA method ❌");
            return $this->redirect("/profile");
        }
        $broker->updateEmailMfa(true);
        Flash::success("Email MFA has been successfully added to your account ✔️");
        return $this->redirect("/profile");
    }

    public function setupPhoneMfa(): Response
    {
        $broker = new UserBroker();
        if ($broker->isPhoneMfaSet(Session::getInstance()->read("currentUser"))) {
            Flash::error("You already have added this MFA method ❌");
            return $this->redirect("/profile");
        }
        $form = $this->buildForm();
        $form->field("phone_nb")->validate(Rule::phone("The provided phone number is not valid"));
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/profile");
        }
        $broker->updatePhoneNb(($form->buildObject())->phone_nb);
        Flash::success("Phone MFA has been successfully added to your account ✔️");
        return $this->redirect("/profile");
    }

    public function removeGoogleMfa(): Response
    {
        $broker = new UserBroker();
        $broker->updateAuthKey(null);
        Flash::info("Email Mfa has been successfully removed from your account");
        return $this->redirect("/profile");
    }

    public function removePhoneMfa(): Response
    {
        $broker = new UserBroker();
        $broker->updatePhoneNb(null);
        Flash::info("Email Mfa has been successfully removed from your account");
        return $this->redirect("/profile");
    }

    public function removeEmailMfa(): Response
    {
        $broker = new UserBroker();
        $broker->updateEmailMfa(false);
        Flash::info("Email Mfa has been successfully removed from your account");
        return $this->redirect("/profile");
    }
}