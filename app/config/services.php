<?php

use Phalcon\Cache\Frontend\Data as FrontCache;
use Phalcon\Cache\Backend\Redis as RedisCache;
use Phalcon\Logger\Formatter\Line as FormatterLine;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Mvc\Model\Metadata\Redis as RedisModelsMetadata;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;

/**
 * 配置信息
 */
$di->setShared("config", function () {
    return include APP_PATH . "/config/config.php";
});

/**
 * 用于连接持久化数据库
 */
$di->setShared("db", function () {
    $config = $this->getShared("config");
    $class = "Phalcon\\Db\\Adapter\\Pdo\\" . $config->database->adapter;
    $params = [
        "host"     => $config->database->host,
        "username" => $config->database->username,
        "password" => $config->database->password,
        "dbname"   => $config->database->dbname,
        "charset"  => $config->database->charset
    ];
    if ($config->database->adapter == "Postgresql") {
        unset($params["charset"]);
    }
    $connection = new $class($params);
    return $connection;
});

/**
 * 用于记录数据表的信息
 */
$di->setShared('modelsMetadata', function () {
    /** @var mixed|\Phalcon\Config $config */
    $config = $this->getShared("config");
    if ("DEV" === ENV) {
        return new MetaDataAdapter();
    }
    $modelsMetadata = new RedisModelsMetadata([
        "host"       => $config->redis->host,
        "port"       => $config->redis->port,
        "auth"       => $config->redis->auth,
        "index"      => $config->redis->index,
        "persistent" => false,
        "statsKey"   => "_PHCM_ModelsMetadata:s",
        "lifetime"   => 172800,
    ]);
    $metadataReflect = new \ReflectionClass($modelsMetadata);
    $metadataProp = $metadataReflect->getProperty("_redis");
    $metadataProp->setAccessible(true);
    $redisCache = $metadataProp->getValue($modelsMetadata);
    $redisCacheReflect = new \ReflectionClass($redisCache);
    $redisCacheProp = $redisCacheReflect->getProperty("_redis");
    $redisCacheProp->setAccessible(true);
    $redisCacheProp->setValue($redisCache, $this->getShared("redis"));
    return $modelsMetadata;
});

/**
 * 缓存服务
 */
$di->setShared("cache", function () {
    $config = $this->getShared("config");
    $frontCache = new FrontCache([
        "lifetime" => 86400 * 2,
    ]);
    $cache = new RedisCache($frontCache, [
        "host"       => $config->redis->host,
        "port"       => $config->redis->port,
        "auth"       => $config->redis->auth,
        "index"      => $config->redis->index,
        "persistent" => false,
        "prefix"     => "_Cache_",
    ]);
    $reflect = new \ReflectionClass($cache);
    $prop = $reflect->getProperty("_redis");
    $prop->setAccessible(true);
    $prop->setValue($cache, $this->getShared("redis"));
    return $cache;
});

/**
 * 模型(EventRecord)的缓存服务
 */
$di->setShared("modelCache", function () {
    $config = $this->getShared("config");
    $frontCache = new FrontCache([
        "lifetime" => 86400 * 2,
    ]);
    $modelCache = new RedisCache($frontCache, [
        "host"       => $config->redis->host,
        "port"       => $config->redis->port,
        "auth"       => $config->redis->auth,
        "index"      => $config->redis->index,
        "persistent" => false,
        "prefix"     => "_ModelCache_",
    ]);
    $reflect = new \ReflectionClass($modelCache);
    $prop = $reflect->getProperty("_redis");
    $prop->setAccessible(true);
    $prop->setValue($modelCache, $this->getShared("redis"));
    return $modelCache;
});

/**
 * 该实例用于在逻辑代码中
 */
$di->setShared("redis", function () {
    $config = $this->getShared("config");
    $redis = new Redis();
    $redis->connect($config->redis->host, $config->redis->port);
    if ("" !== $config->redis->auth) {
        $redis->auth($config->redis->auth);
    }
    return $redis;
});

/**
 * 注册该实例是为了其他服务中如session、cache等使用统一的redis连接，而不是在各服务使用各自的连接，
 * 在逻辑代码中不应使用该实例
 */
$di->setShared("servicesRedis", function () {
    $config = $this->getShared("config");
    $redis = new Redis();
    $redis->connect($config->redis->host, $config->redis->port);
    if ("" !== $config->redis->auth) {
        $redis->auth($config->redis->auth);
    }
    return $redis;
});

/**
 * 日志服务
 */
$di->setShared("logger", function ($filename = null, $format = null) {
    $config   = $this->getShared("config");
    $format   = $format ?: $config->logger->format;
    $filename = trim($filename ?: $config->logger->filename, "\\/");
    $path     = rtrim($config->logger->path, "\\/") . DIRECTORY_SEPARATOR;
    if (!file_exists($path)) {
        mkdir($path);
    }
    if (!file_exists($path . $filename)) {
        $loggerFile = fopen($path . $filename, "a+");
        fclose($loggerFile);
    }
    $formatter = new FormatterLine($format, $config->logger->date);
    $logger    = new FileLogger($path . $filename);

    $logger->setFormatter($formatter);
    $logger->setLogLevel($config->logger->logLevel);

    return $logger;
});