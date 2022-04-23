<?php

namespace Controllers;

use Models\Brokers\UserBroker;
use Models\MFA\GoogleAuthenticator;
use Models\Redirector;
use Models\Validators\CustomRule;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;
use Zephyrus\Network\Response;

class ProfileController extends Controller
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
        $this->get("/profile", "profile");
        $this->put("/profile/password", "updatePassword");
        $this->get("/authenticator-setup", "setupAuthenticator");
        $this->get("/authenticator-setup/test", "testAuthenticator");

    }

    public function profile()
    {
        $broker = new UserBroker();
        return $this->render("profile", [
            "title" => "Manage your profile",
            "location" => "profile",
            "username" => $broker->getUsername()
        ]);
    }

    public function updatePassword()
    {
        $form = $this->buildForm();
        $form->field("oldPassword")->validate(CustomRule::passwordValid());
        $form->field("newPassword")->validate(Rule::passwordCompliant("Password must contain at least one uppercase, one lowercase and one number (8 chars min)"));
        $form->field("newPasswordConfirm")->validate(Rule::sameAs("newPassword", "The two passwords do not match"));
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
        } else {
            $broker = new UserBroker();
            $broker->updatePassword($form->buildObject()->newPassword);
            Flash::success("Your password has been successfully changed ✔️");
        }
        return $this->redirect("/profile");
    }

    public function setupAuthenticator()
    {
        $authenticator = new GoogleAuthenticator();
        $key = $authenticator->generateKey();
        $broker = new UserBroker();
        $broker->updateAuthKey($key);
        $qrCode = $authenticator->getQrCode($key);
        return $this->render("authenticator-setup", [
            "title" => "Setup Authenticator",
            "location" => "authenticator-setup",
            "username" => $broker->getUsername(),
            "qrCode" => $qrCode
        ]);
    }

    public function testAuthenticator() {
        //TODO : Redirect if doesn't come from last page
        //TODO : Test, if it doesn't work, remove from bd and redirect
        $broker = new UserBroker();

        return $this->render("authenticator-test", [
            "title" => "Setup Authenticator",
            "location" => "authenticator-setup/test",
            "username" => $broker->getUsername(),
        ]);
    }
}