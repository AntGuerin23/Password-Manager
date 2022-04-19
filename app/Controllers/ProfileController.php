<?php

namespace Controllers;

use Models\Brokers\UserBroker;
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

    }

    public function profile()
    {
        $broker = new UserBroker();
        return $this->render("profile", [
            "title" => "Manage your profile",
            "location" => "profile",
            "username" => $broker->findUsernameById(Session::getInstance()->read("currentUser"))
        ]);
    }

    public function updatePassword()
    {
        $form = $this->buildForm();
        $form->field("oldPassword")->validate(CustomRule::passwordValid());
        $form->field("newPassword")->validate(Rule::notEmpty("Please enter a new password"));
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
}