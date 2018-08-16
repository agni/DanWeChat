<?php

use Phalcon\Mvc\Router\Group as RouterGroup;

class UserRouter extends RouterGroup
{
    public function __construct()
    {
        $this->add("/user/login",
            [
                "controller" => "user",
                "action"     => "login",
            ]
        )->via("POST");

        $this->add("/user",
            [
                "controller" => "user",
                "action"     => "edit",
            ]
        )->via("PUT");
    }
}
