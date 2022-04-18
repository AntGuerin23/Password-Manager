<?php

namespace Models\Brokers;

class PasswordBroker extends Broker
{
    public function findAllById($id)
    {
        $result = $this->select("SELECT * FROM public.password WHERE user_id = ?", [$id]);
    }
}