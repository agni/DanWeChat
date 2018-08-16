<?php

use Phalcon\Mvc\Router;

$router = new Router(false);
$router->setDefaultNamespace("Dandelion\\Controllers");

$routersDir = APP_PATH . "/config/router/";

include $routersDir . "TestRouter.php";
include $routersDir . "UserRouter.php";
include $routersDir . "KeywordRouter.php";
include $routersDir . "WeChatRouter.php";

$router->mount(new TestRouter());
$router->mount(new UserRouter());
$router->mount(new KeywordRouter());
$router->mount(new WeChatRouter());

$router->notFound([
    "controller" => "index",
    "action"     => "notFound",
]);

return $router;
