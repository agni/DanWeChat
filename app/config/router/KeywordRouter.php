<?php

use Phalcon\Mvc\Router\Group as RouterGroup;

class KeywordRouter extends RouterGroup
{
    public function __construct()
    {
        $this->add("/keyword/{category:[a-z0-9-]+}",
            [
                "controller" => "keyword",
                "action"     => "list",
            ]
        )->via("GET");
    }
}
