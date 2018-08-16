<?php

use Phalcon\Mvc\Router\Group as RouterGroup;

class TestRouter extends RouterGroup
{
    public function __construct()
    {
        $this->add("/test",
            [
                "controller" => "test",
                "action"     => "index",
            ]
        )->via(["GET", "POST", "DELETE", "PUT", "PATCH"]);
    }
}
