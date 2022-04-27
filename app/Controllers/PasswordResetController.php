<?php

namespace Controllers;

use Models\Brokers\UserBroker;
use Models\Mfa\EmailSender;
use Models\Redirector;
use Models\Validators\CustomRule;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;
use Zephyrus\Network\Response;

class PasswordResetController extends Controller
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
        $this->get("/login/forgot", "forgotPasswordPage");
        $this->post("/login/forgot", "sendEmail");
        $this->get("/login/forgot/reset", "resetPasswordPage");
        $this->post("/login/forgot/reset", "validateResetPassword");
    }

    public function forgotPasswordPage(): Response
    {
        return $this->render("forgot-password", [
            "title" => "Forgot password"
        ]);
    }

    public function sendEmail(): Response
    {
        $form = $this->buildForm();
        $form->field("passwordResetEmail")->validate(Rule::email("Please enter a valid email"));
        return $this->verifyFormForgotPassword($form);
    }

    public function resetPasswordPage(): Response
    {
        if (!Session::getInstance()->has("passwordResetEmail")) {
            return $this->redirect("/login");
        }

        return $this->render("reset-password", [
            "title" => "Reset password"
        ]);
    }

    public function validateResetPassword(): Response
    {
        $form = $this->buildForm();
        $form->field("resetCode")->validate(CustomRule::emailCodeValid("Please enter the code you received by email"));
        $form->field("passwordReset")->validate(Rule::passwordCompliant("Please enter a valid password"));
        $form->field("passwordResetConfirm")->validate(Rule::sameAs("passwordReset", "The two passwords do not match"));

        return $this->verifyFormResetPassword($form);
    }

    private function verifyFormForgotPassword($form): Response
    {
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/login/forgot");
        }
        $broker = new UserBroker();
        $formObj = $form->buildObject();
        if ($broker->isEmailTaken($formObj->passwordResetEmail)) {
            (new EmailSender())->sendCode($formObj->passwordResetEmail, "Your SosPass password reset code");
        }
        Flash::info("A code has been sent to $formObj->passwordResetEmail");
        Session::getInstance()->set("passwordResetEmail", $formObj->passwordResetEmail);
        return $this->redirect("/login/forgot/reset");
    }

    private function verifyFormResetPassword($form): Response
    {
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/login/forgot/reset");
        }
        Session::getInstance()->remove("emailCode");
        $broker = new UserBroker();
        $broker->updatePassword($form->buildObject()->passwordReset, $broker->findByEmail(Session::getInstance()->read("passwordResetEmail")));
        Session::getInstance()->remove("passwordResetEmail");
        Flash::success("Your password has been successfully updated  ✔️");
        return $this->redirect("/login");
    }
}