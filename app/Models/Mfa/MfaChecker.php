<?php

namespace Models\Mfa;

use Models\Brokers\UserBroker;
use Models\Validators\CustomRule;
use Zephyrus\Application\Form;
use Zephyrus\Application\Rule;
use Zephyrus\Application\Session;

class MfaChecker
{
    public static function sendCodes()
    {
        $broker = new UserBroker();
        if ($broker->isPhoneMfaSet(Session::getInstance()->read("loginId"))) {
            (new SmsSender())->sendCode($broker->getPhoneNb(Session::getInstance()->read("loginId")));
        }
        if ($broker->isEmailMfaSet(Session::getInstance()->read("loginId"))) {
            (new EmailSender())->sendCode($broker->getEmail(Session::getInstance()->read("loginId")), "Your SosPass authentication code");
        }
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
            $form->field("email")->validate(CustomRule::emailCodeValid());
        }
        if ($broker->isPhoneMfaSet($id)) {
            $form->field("phone")->validate(CustomRule::phoneCodeValid());
        }
        if ($broker->isGoogleMfaSet($id)) {
            $broker = new UserBroker();
            $form->field("google")->validate(CustomRule::googleCodeValid($broker->getAuthKey(Session::getInstance()->read("loginId"))));
        }
        return $form;
    }
}