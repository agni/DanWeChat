<?php
/**
 * User: yz.chen
 * Time: 2017-08-26 11:12
 */

namespace Dandelion\WeChat;

class Oauth2
{
    private static $appid  = "appid";
    private static $secret = "secret";
    private static $state  = "myriad";
    private static $urlForAccess   = "https://api.weixin.qq.com/sns/oauth2/access_token";
    private static $urlForUserInfo = "https://api.weixin.qq.com/sns/userinfo";
    private $accessParams = [
        "appid"      => "",
        "secret"     => "",
        "code"       => "",
        "grant_type" => "authorization_code",
    ];
    private $userInfoParams = [
        "access_token" => "",
        "openid"       => "",
    ];
    private $userInfo = [
        "openid"     => "",
        "unionid"    => "",
        "nickname"   => "",
        "headimgurl" => "",
    ];

    public function __construct($code)
    {
        $this->accessParams["appid"]  = Oauth2::$appid;
        $this->accessParams["secret"] = Oauth2::$secret;
        $this->accessParams["code"]   = $code;
    }

    public static function rGet($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $url . "?" . http_build_query($params));
        curl_setopt($ch, CURLOPT_HEADER,         0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS,      1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    public function checkState($state)
    {
        return $state == Oauth2::$state;
    }

    public function getUserInfo()
    {
        return $this->userInfo;
    }

    public function accessRes()
    {
        $url    = Oauth2::$urlForAccess;
        $params = $this->accessParams;
        $res    = Oauth2::rGet($url, $params);
        $resObj = json_decode($res);
        $this->userInfoParams["access_token"] = $resObj->access_token;
        $this->userInfoParams["openid"]       = $resObj->openid;
        return $resObj;
    }

    public function userInfoRes()
    {
        $url = Oauth2::$urlForUserInfo;
        $params = $this->userInfoParams;
        if ($params["access_token"] == "" || $params["openid"] == "") {
            return null;
        }
        $res = Oauth2::rGet($url, $params);
        $resObj = json_decode($res);
        $this->userInfo["openid"]     = $resObj->openid;
        $this->userInfo["unionid"]    = $resObj->unionid ?? "";
        $this->userInfo["nickname"]   = $resObj->nickname;
        $this->userInfo["headimgurl"] = $resObj->headimgurl;
        return $resObj;
    }

    public function getUserInfoOneStep($state)
    {
        if (!$this->checkState($state)) {
            return null;
        }
        $this->accessRes();
        if (!$this->userInfoRes()) {
            return null;
        }
        return $this->userInfo;
    }
}