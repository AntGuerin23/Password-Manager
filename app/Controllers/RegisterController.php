<?php

namespace Controllers;

use Models\Brokers\UserBroker;
use Models\Redirector;
use Models\Validators\CustomRule;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;
use Zephyrus\Network\Response;

class RegisterController extends Controller
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
        $this->get("/register", "register");
        $this->post("/register", "createUser");
    }

    public function register(): Response
    {
        return $this->render("register", [
            "title" => "Register"
        ]);
    }

    public function createUser(): Response
    {
        $form = $this->buildRegisterForm();
        if ($this->tryRegister($form)) {
            return $this->redirect("/login");
        }
        return $this->redirect("/register");
    }

    private function buildRegisterForm(): Form
    {
        $form = $this->buildForm();
        $form->field("email")->validate(Rule::email("Please enter a valid email"));
        $form->field("email")->validate(CustomRule::emailDoesntExist());
        $form->field("newUsername")->validate(CustomRule::usernameDoesntExist());
        $form->field("newPassword")->validate(Rule::passwordCompliant("Password must contain at least one uppercase, one lowercase and one number (8 chars min)"));
        $form->field("password-confirm")->validate(Rule::sameAs("newPassword", "The two passwords do not match"));
        return $form;
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
}