<?php

namespace Models\Brokers;

class KeyBroker extends Broker
{
    public function getUserFromKey($key) : int
    {
        //todo : return user id, or null if doesn't exist
        return 3;
    }
}