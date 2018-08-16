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

        $this->add("/user/logout",
            [
                "controller" => "user",
                "action"     => "logout",
            ]
        )->via("POST");

        $this->add("/user/info",
            [
                "controller" => "user",
                "action"     => "baseInfo",
            ]
        )->via("GET");

        $this->add("/user/info",
            [
                "controller" => "user",
                "action"     => "selfEdit",
            ]
        )->via("PATCH");
    }
}
