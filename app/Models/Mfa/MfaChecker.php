<?php

namespace Models\Mfa;

use Models\Brokers\UserBroker;
use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;

class MfaChecker
{
    public static function sendCodes()
    {
        //send codes where it is activated
        //save inside session
    }

    public static function verifyCodes($codes): bool
    {
        //check for session
    }

    public static function getActivatedMethods($id): array
    {
        $broker = new UserBroker();
        return [
            "google" => $broker->isGoogleMfaSet($id),
            "email" => $broker->isEmailMfaSet($id),
            "phone" => $broker->isPhoneMfaSet($id)
        ];
    }

    public static function hasActivatedMethods($id): bool
    {
        $broker = new UserBroker();
        return $broker->isEmailMfaSet($id) || $broker->isPhoneMfaSet($id) || $broker->isGoogleMfaSet($id);
    }

    public static function setupValidation($form, $id): Form
    {
        $broker = new UserBroker();
        if ($broker->isEmailMfaSet($id)) {
            $form->field("email")->validate(Rule::notEmpty("Empty"));
        }
        if ($broker->isPhoneMfaSet($id)) {
            $form->field("phone")->validate(Rule::notEmpty("Empty"));
        }
        if ($broker->isEmailMfaSet($id)) {
            $form->field("google")->validate(Rule::notEmpty("Empty"));
        }
        return $form;
    }
}