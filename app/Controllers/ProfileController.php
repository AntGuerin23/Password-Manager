<?php

namespace Controllers;

use JetBrains\PhpStorm\NoReturn;
use Models\Brokers\ConnectionBroker;
use Models\Brokers\UserBroker;
use Models\Mfa\GoogleAuthenticator;
use Models\Mfa\MfaChecker;
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
        $this->delete("/profile/connection/{id}", "deleteConnection");
    }

    public function profile(): Response
    {
        return $this->render("profile", [
            "title" => "Manage your profile",
            "location" => "/profile",
            "username" => (new UserBroker())->getUsername(),
            "activated" => MfaChecker::getActivatedMethods(Session::getInstance()->read("currentUser")),
            "conn" => (new ConnectionBroker())->getConnectionsForUser()
        ]);
    }

    public function updatePassword(): Response
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

    public function deleteConnection($id): Response
    {
        $broker = new ConnectionBroker();
        $broker->delete($id);
        Flash::success("Connection has been successfully closed ✔️");
        return $this->redirect("/profile");
    }
}