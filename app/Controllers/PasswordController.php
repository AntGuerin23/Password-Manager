<?php

namespace Controllers;

use Models\Brokers\PasswordBroker;
use Models\Brokers\UserBroker;
use Models\Redirector;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;
use Zephyrus\Network\Response;
use Zephyrus\Security\SecureHeader;

class PasswordController extends Controller
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
        $this->get("/add-password", "addPasswordPage");
        $this->post("/password", "insertPassword");
        $this->delete("/password/{id}", "deletePassword");
        $this->put("/password/{id}", "updatePassword");
    }

    public function addPasswordPage()
    {
        $broker = new UserBroker();
        return $this->render("add-password", [
            "title" => "Add a new password",
            "location" => "/add-password",
            "username" => $broker->getUsername()
        ]);
    }

    public function insertPassword()
    {
        $form = $this->buildForm();
        $form->field("appName")->validate(Rule::notEmpty("Please enter the application name"));
        $form->field("sitePassword")->validate(Rule::notEmpty("Please enter a password"));
        $form->field("siteUsername")->validate(Rule::notEmpty("Please enter a username"));
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/add-password");
        }
        (new PasswordBroker())->insert($form->buildObject());
        Flash::success("Your new password has been successfully added ✔️");
        return $this->redirect("/");
    }

    public function deletePassword($id)
    {
        $broker = new PasswordBroker();
        if (!$broker->passwordBelongsToUser($id)) {
            Flash::error("This action is forbidden");
            return $this->redirect("/");
        }
        $broker->delete($id);
        Flash::info("The requested password has been deleted");
        return $this->redirect("/");
    }

    public function updatePassword($id)
    {
        $form = $this->buildForm();
        $form->field("updatePassword")->validate(Rule::notEmpty("Please enter a new password"));
        $form->field("updatePasswordConfirm")->validate(Rule::sameAs("updatePassword", "The two passwords do not match"));
        Form::removeMemorizedValue();
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/");
        }
        $broker = new PasswordBroker();
        if (!$broker->passwordBelongsToUser($id)) {
            Flash::error("This action is forbidden");
            return $this->redirect("/");
        }
        $broker->modify($form->buildObject()->updatePassword, $id);
        Flash::success("The requested password has been successfully modified ✔️");
        return $this->redirect("/");
    }
}