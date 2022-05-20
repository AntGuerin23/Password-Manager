<?php

namespace Models\Validators;

use Models\Authenticator;
use Models\Brokers\KeyBroker;
use Models\Brokers\PasswordBroker;
use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;

class ApiValidator
{

    public static function validateAuthentication(Form $form): ?\stdClass
    {
        $form->field("username")->validate(Rule::notEmpty());
        $form->field("password")->validate(Rule::notEmpty());
        $id = Authenticator::tryAuthenticating($form->getValue("username"), $form->getValue("password"));
        if ($form->verify() && $id != null) {
            $form->addField("id", $id);
            return $form->buildObject();
        }
        return null;
    }

    public static function validatePasswordJson(Form $form): ?\stdClass
    {
        if (!$formObject = self::validatePasswordForm($form)) {
            return null;
        }
        if (!$id = (new KeyBroker())->getUserFromKey($formObject->apiKey)) {
            return null;
        }
        if (!$result = (new PasswordBroker())->findByDomain($id, $formObject->domain, $formObject->encryptionKey)) {
            return null;
        }
        return $result;
    }

    private static function validatePasswordForm(Form $form)
    {
        $form->field("apiKey")->validate(Rule::notEmpty());
        $form->field("encryptionKey")->validate(Rule::notEmpty());
        $form->field("domain")->validate(Rule::notEmpty());
        if (!$form->verify()) {
            return null;
        }
        return $form->buildObject();
    }
}