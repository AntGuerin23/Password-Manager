<?php

use Models\Brokers\PasswordBroker;
use Zephyrus\Application\Session;

//parameter ??

$broker = new PasswordBroker();

$passwords = $broker->findAllForUser(Session::getInstance()->read("currentUser"));

foreach ($passwords as $password) {
    //check if contains
    echo json_encode($passwords);
}
