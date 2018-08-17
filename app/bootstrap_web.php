<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Application;
use Dandelion\HttpError;

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('UPLOAD_PATH', BASE_PATH . '/upload/');
define('ENV', 'DEV');
define('DEBUG_MODE', true);

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin:' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials:true');
    if ("OPTIONS" == $_SERVER["REQUEST_METHOD"]) {
        header('Access-Control-Allow-Methods:OPTIONS, GET, POST, DELETE, PUT, PATCH');
        header('Access-Control-Allow-Headers:Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With, Accept, Connection, User-Agent, Cookie');
        header('Access-Control-Max-Age:86400');
        exit();
    }
}

try {
    $di = new FactoryDefault();

    include APP_PATH . '/config/services.php';

    include APP_PATH . '/config/services_web.php';

    include APP_PATH . '/config/loader.php';

    $application = new Application($di);

    echo str_replace(["\n", "\r", "\t"], '', $application->handle()->getContent());

} catch (HttpError $err) {
    $code    = $err->getCode();
    $message = $err->getMessage();
    header("HTTP/1.1 $code");
    header("Content-Type: application/json; charset=UTF-8");
    ob_clean();
    echo json_encode(["message" => $message]);
} catch (\Error $err) {
    header("HTTP/1.1 500");
    if (DEBUG_MODE) {
        echo $err->getMessage() . '<br>';
        echo '<pre>' . $err->getTraceAsString() . '</pre>';
    } else {
        $timeStr = date("Ymd_His");
        $redis = \Phalcon\Di::getDefault()->get("redis");
        $redis->hMset("_PHCR_Exception_$timeStr", [
            "message" => $err->getMessage(),
            "trace"   => $err->getTraceAsString(),
        ]);
        $redis->expire("_PHCR_Exception_$timeStr", 86400);
        header("Content-Type: application/json; charset=UTF-8");
        ob_clean();
        echo json_encode(["message" => "服务器暂时走神了，请联系管理员查看错误日志！"]);
    }
}
