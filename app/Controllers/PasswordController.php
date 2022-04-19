<?php

namespace Controllers;

use Models\Brokers\PasswordBroker;
use Models\Brokers\UserBroker;
use Models\Redirector;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;
use Zephyrus\Network\Response;

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
        $this->get("/add-password", "addPassword");
        $this->post("/password", "insertPassword");
        $this->delete("/password/{id}", "deletePassword");
        $this->put("/password/{id}", "updatePassword");
    }

    public function addPassword()
    {
        $broker = new UserBroker();
        return $this->render("add-password", [
            "title" => "Add a new password",
            "location" => "add-password",
            "username" => $broker->findUsernameById(Session::getInstance()->read("currentUser"))
        ]);
    }

    public function insertPassword()
    {
        $form = $this->buildForm();
        $form->field("appName")->validate(Rule::notEmpty("Please enter the application name"));
        $form->field("password")->validate(Rule::notEmpty("Please enter a password"));
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("add-password");
        }
        (new PasswordBroker())->insert($form->buildObject());
        Flash::success("Your new password has been successfully added ✔️");
        return $this->redirect("/");
    }

    public function deletePassword($id)
    {
        $broker = new PasswordBroker();
        $broker->delete($id);
        Flash::info("The requested password has been deleted");
        return $this->redirect("/");
    }

    public function updatePassword($id)
    {
        $form = $this->buildForm();
        $form->field("password")->validate(Rule::notEmpty("Please enter a new password"));
        $form->field("passwordConfirm")->validate(Rule::sameAs("password", "The two passwords do not match"));
        if (!$form->verify()) {
            Flash::error($form->getErrorMessages());
            return $this->redirect("/");
        }
        $broker = new PasswordBroker();
        $broker->modify($form->buildObject()->password, $id);
        Flash::success("The requested password has been successfully modified ✔️");
        return $this->redirect("/");

    }
}