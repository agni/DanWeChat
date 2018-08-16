<?php

namespace Dandelion\Controllers;

use Dandelion\Http;
use Dandelion\Models\MiniProgram;
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
        $app = MiniProgram::findFirst(["appKey = \"$appKey\""]);
        $weChatRes = Http::get("https://api.weixin.qq.com/sns/jscode2session", [
            "appid"      => $app->appId,
            "secret"     => $app->appSecret,
            "js_code"    => $code,
            "grant_type" => "authorization_code",
        ]);
        $weChatRes = json_decode($weChatRes);
        $openId = $weChatRes->openid;
        $unionId = $weChatRes->unionid ?? "";
        $appUser = WeChatUser::findFirst(["openId = \"$openId\""]);
        if (!$appUser) {
            $appUser = new WeChatUser();
            $appUser->openId = $openId;
            $appUser->unionId = $unionId;
            $appUser->appId = $app->appId;
            $appUser->save();
        }
        $this->session->set("openId", $openId);
        $this->session->set("appId", $app->id);
        return $this->sendMessage("OK");
    }

}
