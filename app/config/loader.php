<?php

use Phalcon\Di;
use Phalcon\Loader;

$config = Di::getDefault()->getShared("config");
$loader = new Loader();

$loader->registerNamespaces(
    [
        "Dandelion\\Controllers" => $config->application->controllersDir,
        "Dandelion\\Models"      => $config->application->modelsDir,
        "Dandelion\\Tasks"       => $config->application->tasksDir,
        "Dandelion\\Plugins"     => $config->application->pluginsDir,
        "Dandelion"              => $config->application->libraryDir,
    ]
);

$loader->register();
