<?php

namespace Controllers;

use Models\Brokers\UserBroker;
use Models\Mfa\EmailSender;
use Models\Redirector;
use Models\Validators\CustomRule;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;
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
        $this->get("/register", "registerPage");
        $this->post("/register", "createUser");
        $this->get("/register/confirm-email", "emailConfirmPage");
        $this->post("/register/confirm-email", "verifyEmailConfirm");
    }

    public function registerPage(): Response
    {
        return $this->render("register", [
            "title" => "Register"
        ]);
    }

    public function createUser(): Response
    {
        $form = $this->buildRegisterForm();
        if ($form->verify()) {
            $email = $form->buildObject()->email;
            (new EmailSender())->sendCode($email, "Your SosPass email verification code");
            Flash::info("An email with a verification code has been sent to " . $email);
            return $this->redirect("/register/confirm-email");
        }
        Flash::error($form->getErrorMessages());
        return $this->redirect("/register");
    }

    public function emailConfirmPage() {
        return $this->render("email-confirm", [
            "title" => "Email confirmation"
        ]);
    }

    public function verifyEmailConfirm() {
        $form = $this->buildForm();
        $form->field("confirmCode")->validate(CustomRule::emailCodeValid("Please enter the code you've just received by email"));
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/register/confirm-email");
        }
        (new UserBroker())->insertUsingSession();
        Flash::success("Your account has been successfully created ✔️");
        return $this->redirect("/login");
    }

    private function buildRegisterForm(): Form
    {
        $form = $this->buildForm();
        $form->field("email")->validate(Rule::email("Please enter a valid email"));
        $form->field("email")->validate(CustomRule::emailDoesntExist());
        $form->field("newUsername")->validate(CustomRule::usernameDoesntExist());
        $form->field("newPassword")->validate(Rule::passwordCompliant("Password must contain at least one uppercase, one lowercase and one number (8 chars min)"));
        $form->field("password-confirm")->validate(Rule::sameAs("newPassword", "The two passwords do not match"));
        Session::getInstance()->setAll((array)$form->buildObject());
        return $form;
    }

    private function tryRegister($form): bool
    {
        if ($form->verify()) {
            $broker = new UserBroker();
            $broker->insert($form->buildObject());
            Flash::success("Your account has been successfully created ✔️");
            return true;
        } else {
            return false;
        }
    }
}