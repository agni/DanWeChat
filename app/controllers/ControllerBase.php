<?php

namespace Dandelion\Controllers;

use Dandelion\HttpError;
use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

/**
 * Class ControllerBase
 *
 * @package Dandelion\Controllers
 *
 * @property \Phalcon\Logger\Adapter\File $logger
 * @property \Phalcon\Config              $config
 * @property \Phalcon\Cache\Backend\Redis $cache
 * @property \Redis                       $redis
 * @property \Dandelion\Models\User       $user
 */
class ControllerBase extends Controller
{
    /**
     * 设置response的基本方法，返回Response
     *
     * @param integer $statusCode
     * @param mixed   $data
     * @return Response
     */
    protected function sendResponse($statusCode, $data)
    {
//        $this->logMsg($statusCode, $data);
        $this->response->setStatusCode($statusCode);
        $this->response->setJsonContent($data);
        return $this->response;
    }

    /**
     * 设置response的content，statusCode为200
     *
     * @param mixed $data
     * @return Response
     */
    protected function sendSuccess($data)
    {
        return $this->sendResponse(200, $data);
    }

    /**
     * 在response中设置提示消息，statusCode为200
     *
     * @param string $message
     * @return Response
     */
    protected function sendMessage($message)
    {
        return $this->sendSuccess(["message" => $message]);
    }

    /**
     * 在response中设置失败消息和失败状态标示，statusCode为200
     *
     * @param string $message
     * @param string $errCode
     * @return Response
     */
    protected function sendFailure($message, $errCode = "")
    {
        return $this->sendResponse(400, ["message" => $message, "errCode" => $errCode]);
    }

    /**
     * 在response中设置参数错误提示，用于提示request的参数与要求的不符合，statusCode为400
     *
     * @return Response
     */
    protected function sendParamError()
    {
        return $this->sendFailure("参数错误", "param_err");
    }

    /**
     * 在response中设置未登录提示，statusCode为401
     *
     * @param string $message
     * @return Response
     */
    protected function sendUnauthorized($message = "未登录")
    {
        return $this->sendResponse(401, ["message" => $message]);
    }

    /**
     * 在response中设置无权操作提示，statusCode为403
     *
     * @param string $message
     * @return Response
     */
    protected function sendForbidden($message = "无权执行此操作")
    {
        return $this->sendResponse(403, ["message" => $message]);
    }

    /**
     * 在response中设置NotFound提示，statusCode为404
     *
     * @param string $message
     * @return Response
     */
    protected function sendNotFound($message = "资源不存在")
    {
        return $this->sendResponse(404, ["message" => $message]);
    }

    /**
     * 记录请求日志
     *
     * @param integer $code
     * @param mixed   $data
     */
    protected function logMsg($code, $data)
    {
        $msg = [
            "uri"      => $this->request->getURI(),
            "param"    => $this->request->getJsonRawBody(),
            "response" => $data,
        ];
        if ($code < 400) {
            $this->logger->log(json_encode($msg));
        } else {
            $this->logger->error(json_encode($msg));
        }
    }

    /**
     * 获取request中的content
     *
     * @param array $paramsRequired 必要的参数，content不满足这些参数会抛出错误
     * @param array $whiteList      白名单，不在白名单中的key将被过滤
     * @param bool  $asArray        返回数组形式，设置为false返回\stdClass
     * @return array|\stdClass
     */
    public function getJson($paramsRequired = [], $whiteList = null, $asArray = false)
    {
        $json = $this->request->getJsonRawBody();
        foreach ($paramsRequired as $key) {
            if (!isset($json->$key)) {
                throw new HttpError("参数错误", 400);
            }
        }
        if ($whiteList) {
            $newObject = new \stdClass();
            foreach ($whiteList as $key) {
                if (isset($json->$key)) {
                    $newObject->$key = $json->$key;
                }
            }
            $json = $newObject;
        }
        return $asArray ? (array)$json : $json;
    }

}
