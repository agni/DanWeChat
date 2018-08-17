<?php
//print_r (get_loaded_extensions());die;
use Phalcon\Di\FactoryDefault\Cli as FactoryDefault;
use Phalcon\Cli\Console as ConsoleApp;

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('ENV', 'DEV');

try {
    $di = new FactoryDefault();

    include APP_PATH . '/config/services.php';
    include APP_PATH . '/config/services_cli.php';

    $config = $di->get("config");
    include APP_PATH . '/config/loader.php';

    $application = new ConsoleApp($di);

    $arguments = [];

    foreach ($argv as $k => $arg) {
        if ($k == 1) {
            $arguments['task'] = $arg;
        } elseif ($k == 2) {
            $arguments['action'] = $arg;
        } elseif ($k >= 3) {
            $arguments['params'][] = $arg;
        }
    }

    $application->handle($arguments);

    if (isset($config["printNewLine"]) && $config["printNewLine"]) {
        echo PHP_EOL;
    }

} catch (Throwable $e) {
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    exit(255);
}
