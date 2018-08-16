<?php

namespace Dandelion\Controllers;

use Dandelion\Models\WeChatUser;

class WeChatController extends ControllerBase
{
    /**
     * 2.1 用户登录
     * - Method: POST
     * - Path: /we-chat/login
     *
     * @throws \Dandelion\HttpError
     */
    public function loginAction()
    {
        $appKey = $this->request->getHeader("appKey");
        $code = $this->getJson(["code"])->code;
        if (!WeChatUser::login($appKey, $code)) {
            return $this->sendFail("登录失败");
        }
        return $this->sendMessage("登录成功");
    }

}
