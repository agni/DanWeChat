<?php
/**
 * User: yz.chen
 * Time: 2018-08-16 15:10
 */

namespace Dandelion\Models;

use Dandelion\Http;
use Dandelion\WeChat\Crypt;
use Phalcon\Di;

class User extends ModelBase
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

    public static $editableData = ["nickName", "avatarUrl", "gender", "country", "province", "city"];

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->setSource("wx_user");
    }

    public function getSource()
    {
        return "wx_user";
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
        $app = App::findFirst(["appKey = \"$appKey\""]);
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
        $sessionKey = $weChatRes->session_key;
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
        Di::getDefault()->get("session")->set("sessionKey", $sessionKey);
        return true;
    }

    public function editInfo($encryptedData, $iv)
    {
        $sessionKey = $this->getDI()->get("session")->get("sessionKey");
        $crypt = new Crypt($this->appId, $sessionKey);
        $crypt->decryptData($encryptedData, $iv);
        $data = $crypt->getDecryptedData();
        if (!$data) {
            return false;
        }
        $this->assign(json_decode($data, true), null, static::$editableData);
        return $this->save();
    }
}