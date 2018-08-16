<?php

use Phalcon\Config;
use Phalcon\Logger;

return new Config([
    "env" => "dev",

    "database" => require_once APP_PATH . "/config/mysql.php",

    "redis" => require_once APP_PATH . "/config/redis.php",

    "application" => [
        "appDir"         => APP_PATH . "/",
        "controllersDir" => APP_PATH . "/controllers/",
        "modelsDir"      => APP_PATH . "/models/",
        "tasksDir"       => APP_PATH . "/tasks/",
        "pluginsDir"     => APP_PATH . "/plugins/",
        "viewsDir"       => APP_PATH . "/views/",
        "libraryDir"     => APP_PATH . "/library/",
        "cacheDir"       => BASE_PATH . "/cache/",
        "baseUri"        => "/",
        "salt"           => "8c46e3bd50eb447c3bb225ce93dc8973b4d04bd514501d8a2e383e3324e17acf",
    ],

    "acl" => [
        "public" => [
            "test"     => ["*"],
            "index"    => ["*"],
            "keyword"  => ["*"],
            "user"     => ["login", "logout"],
            "position" => ["list"],
            "product"  => ["categoryList", "categoryProduct"],
        ],
        "guest"  => [],
        "admin"  => [],
        "user"   => [
            "address" => ["list", "edit", "delete"],
            "user"    => ["baseInfo", "selfEdit"],
        ],
    ],

    "logger" => [
        "path"     => APP_PATH . "/logs/",
        "format"   => "%date% [%type%] %message%",
        "date"     => "Ymd_His",
        "logLevel" => Logger::DEBUG,
        "filename" => "application.log",
    ],

    "printNewLine" => true,
]);
