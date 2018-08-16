<?php

namespace Dandelion\Controllers;

use Dandelion\Models\User;

class UserController extends ControllerBase
{
    /**
     * 2.1 用户登录
     * - Method: POST
     * - Path: /user/login
     *
     * @throws \Dandelion\HttpError
     * @return \Phalcon\Http\Response
     */
    public function loginAction()
    {
        $appKey = $this->request->getHeader("appKey");
        $code = $this->getJson(["code"])->code;
        if (!User::login($appKey, $code)) {
            return $this->sendFail("登录失败");
        }
        return $this->sendMessage("登录成功");
    }

    /**
     * 2.2 用户上传个人信息
     * - Method: PUT
     * - Path: /user
     *
     * @throws \Dandelion\HttpError
     * @return \Phalcon\Http\Response
     */
    public function editAction()
    {
        $request = $this->getJson(null, null, true);
        $this->user->assign($request, null, User::$editableData);
        if (!$this->user->save()) {
            return $this->sendFail("保存失败");
        }
        return $this->sendMessage("保存成功");
    }

}
