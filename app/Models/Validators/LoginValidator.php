<?php

namespace Models\Validators;

use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;

class LoginValidator
{
    static function validateLoginForm(Form $form)
    {
        $form->field("username")->validate(Rule::notEmpty("Please enter a username"));
        $form->field("password")->validate(Rule::notEmpty("Please enter a password"));
        if ($form->verify()) {
            return $form->buildObject();
        }
        return null;
    }
}