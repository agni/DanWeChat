<?php
/**
 * User: yz.chen
 * Time: 2018-08-16 15:10
 */

namespace Dandelion\Models;


use Dandelion\Http;
use Phalcon\Di;

class WeChatUser extends ModelBase
{
    public $id;
    public $openId;
    public $unionId;
    public $appId;
    public $nickName;
    public $avatarUrl;
    public $gender;
    public $country;
    public $province;
    public $city;
    public $status;
    public $createdAt;
    public $createdBy;
    public $updatedAt;
    public $updatedBy;

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->setSource("wechat_user");
    }

    public function getSource()
    {
        return "wechat_user";
    }

    /**
     * 小程序登录，验证小程序是否存在，登录成功将重要信息写入session
     *
     * @param $appKey
     * @param $code
     * @return bool
     */
    public static function login($appKey, $code)
    {
        $app = MiniProgram::findFirst(["appKey = \"$appKey\""]);
        if (!$app) {
            return false;
        }
        $weChatRes = Http::get("https://api.weixin.qq.com/sns/jscode2session", [
            "appid"      => $app->appId,
            "secret"     => $app->appSecret,
            "js_code"    => $code,
            "grant_type" => "authorization_code",
        ]);
        $weChatRes = json_decode($weChatRes);
        if (!isset($weChatRes->openid)) {
            return false;
        }

        $openId = $weChatRes->openid;
        $unionId = $weChatRes->unionid ?? "";
        $appUser = static::findFirst(["openId = \"$openId\""]);
        if (!$appUser) {
            $appUser = new static();
            $appUser->assign([
                "openId"  => $openId,
                "unionId" => $unionId,
                "appId"   => $app->appId,
            ]);
            $appUser->save();
        }
        Di::getDefault()->get("session")->set("openId", $openId);
        Di::getDefault()->get("session")->set("appId", $app->id);
        return true;
    }
}