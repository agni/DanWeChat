<?php

use Phalcon\Mvc\Router;

$router = new Router(false);
$router->setDefaultNamespace("Dandelion\\Controllers");

$routersDir = APP_PATH . "/config/router/";
$files = new \FilesystemIterator($routersDir);

/** @var \FilesystemIterator $file */
foreach ($files as $file) {
    if (!$file->isFile() || "php" !== $file->getExtension()) {
        continue;
    }
    $className = $file->getBasename(".php");
    include $routersDir . $className . ".php";
    $router->mount(new $className);
}

$router->notFound([
    "controller" => "index",
    "action"     => "notFound",
]);

return $router;
