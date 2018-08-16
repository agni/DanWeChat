<?php

use Phalcon\Mvc\Router\Group as RouterGroup;

class WeChatRouter extends RouterGroup
{
    public function __construct()
    {
        $this->add("/we-chat/login",
            [
                "controller" => "we_chat",
                "action"     => "login",
            ]
        )->via("POST");
    }
}
