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
     */
    public function loginAction()
    {
        $request  = $this->getJson(["account", "password"]);
        $account  = $request->account;
        $password = $request->password;
        $user = User::findFirst("account = \"${account}\" AND status > 0");
        if (!$user || !$user->login($password)) {
            return $this->sendFail("用户名或密码错误");
        }
        return $this->sendSuccess(["id" => $user->id]);
    }

    /**
     * 2.2 用户退出
     * - Method: POST
     * - Path: /user/logout
     */
    public function logoutAction()
    {
        $userID = $this->session->get("uid");
        if ($userID && $user = User::ID($userID)) {
            $user->logout();
        }
        return $this->sendMessage("退出成功");
    }

    /**
     * 2.3 用户获取自己的基本信息
     * - Method: GET
     * - Path: /user/info
     */
    public function baseInfoAction()
    {
        return $this->sendSuccess($this->user->toArray(User::$INFO_COLUMNS["base"]));
    }

    /**
     * 2.4 修改个人信息
     * - Method: PATCH
     * - Path: /user/info
     */
    public function selfEditAction()
    {
        $data = $this->getJson([], User::$editableData, true);
        if (!$this->user->save($data)) {
            return $this->sendFail("保存失败");
        } else {
            return $this->sendMessage("保存成功");
        }
    }

}
