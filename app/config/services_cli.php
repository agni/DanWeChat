<?php

use Phalcon\Cli\Dispatcher;

/**
 * Set the default namespace for dispatcher
 */
$di->setShared("dispatcher", function () {
    $dispatcher = new Dispatcher();
    $dispatcher->setDefaultNamespace("Dandelion\\Tasks");
    return $dispatcher;
});

$di->setShared("user", function () {
    return null;
});
