<?php

use Phalcon\Mvc\Dispatcher;
use Phalcon\Http\Response\Cookies;
use Phalcon\Session\Adapter\Redis as RedisSession;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Events\Manager as EventsManager;
use Dandelion\Plugins\Security;
use Dandelion\Models\User;

$di->setShared("eventsManager", function () {
    $eventsManager = new EventsManager();
    $eventsManager->attach("dispatch:beforeDispatch", new Security());
    return $eventsManager;
});

/**
 * Set the default namespace for dispatcher
 */
$di->setShared("dispatcher", function () {
    $dispatcher = new Dispatcher();
    $dispatcher->setDefaultNamespace("Dandelion\\Controllers");
    $dispatcher->setEventsManager($this->getShared("eventsManager"));
    return $dispatcher;
});

$di->set("router", function () {
    $router = require APP_PATH . "/config/router.php";
    return $router;
});

$di->set("cookies", function () {
    $cookies = new Cookies();
    $cookies->useEncryption(false);
    return $cookies;
});

/**
 * Start the session the first time some component request the session service
 */
$di->setShared("session", function () {
    $config = $this->getConfig();
    $session = new RedisSession([
        "host"       => $config->redis->host,
        "port"       => $config->redis->port,
        "auth"       => $config->redis->auth,
        "index"      => $config->redis->index,
        "uniqueId"   => "",
        "persistent" => false,
        "lifetime"   => 86400,
        "prefix"     => "_Session_",
    ]);
    $sessionReflect = new \ReflectionClass($session);
    $metadataProp = $sessionReflect->getProperty("_redis");
    $metadataProp->setAccessible(true);
    $redisCache = $metadataProp->getValue($session);
    $redisCacheReflect = new \ReflectionClass($redisCache);
    $redisCacheProp = $redisCacheReflect->getProperty("_redis");
    $redisCacheProp->setAccessible(true);
    $redisCacheProp->setValue($redisCache, $this->getShared("redis"));
    $session->setName("appSession");
    $session->start();
    return $session;
});

$di->setShared("user", function () {
    $uid = $this->getShared("session")->get("uid", 0);
    return User::ID($uid) ?: null;
});

$di->setShared("view", function () {
    $config = $this->getShared("config");
    $view = new View();
    $view->setDI($this);
    $view->setViewsDir($config->application->viewsDir);
    $view->registerEngines([".phtml" => PhpEngine::class]);
    return $view;
});
