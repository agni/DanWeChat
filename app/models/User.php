<?php

namespace Dandelion\Models;

use Dandelion\Validator;
use Phalcon\DI;

class User extends ModelBase
{
    public $id;
    public $name;
    public $account;
    public $password;
    public $gender;
    public $avatar;
    public $status;
    public $createdAt;
    public $createdBy;
    public $updatedAt;
    public $updatedBy;

    public static $userCookiesKey = "_PHCR_Login_user:cookies:h";
    public static $editableData = ["name", "gender", "avatar"];
    public static $INFO_COLUMNS = [
        "base" => ["id", "name", "account", "gender", "avatar"],
    ];

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->setSource("common_user");
    }

    public function getSource()
    {
        return "common_user";
    }

    protected function beforeSave()
    {
        $validator = new Validator();
        $validator->add($this->name, Validator::$STR_LEN, [1, 16])
                  ->add($this->gender, Validator::$ID_CHECK, ["class" => Keyword::class, "category" => "gender"])
                  ->add($this->avatar, Validator::$STR_LEN, [null, null]);
        return $validator->validate();
    }

    /**
     * 计算密码的hash值
     *
     * @param  String $password   密码
     * @param  String $confusion  salt的一部分，推荐使用用户的创建时间
     * @return String             密码加salt的hash值
     */
    public static function hashPassword($password, $confusion)
    {
        $salt = DI::getDefault()["config"]->application->salt;
        return hash("sha256", $password . $confusion . $salt);
    }

    /**
     * 用户登录，删除异地登录的信息，删除上一个未退出用户的信息
     *
     * @param  String $password  密码，该值为null时表示验证码登录，无需验证密码
     * @return bool              是否成功登录
     */
    public function login($password = null)
    {
        $realPwd = $this->password;
        if (!is_null($password) && User::hashPassword($password, $this->createdAt) !== $realPwd) {
            return false;
        }
        $session = $this->getDI()->get("session");
        $cookies = $session->getID();
        $redis = $this->getDI()->get("redis");
        // 清除上一个占用此cookie的用户信息（上一个用户没有注销，又在同一个设备登录当前用户）
        $lastUid = $session->get("uid");
        if ($lastUid) {    //说明他人未注销
            $redis->hDel(static::$userCookiesKey, $lastUid);
        }
        $session->destroy();
        // 清除/更改当前用户在他处的登录信息（强制下线）
        $oldCookies = $redis->hGet(static::$userCookiesKey, $this->id);
        if ($oldCookies) { // 说明在他处有登录
            // TODO: 可在此处推送下线通知
            $redis->del("_PHCR_Session_" . $oldCookies);
        }
        $redis->hSet(static::$userCookiesKey, $this->id, $cookies);
        // 记录新的session信息
        $session->set("uid", $this->id);
        return true;
    }

    /**
     * 用户登出，清除登录态信息
     */
    public function logout()
    {
        $session = $this->getDI()->get("session");
        $session->destroy();
        $redis = $this->getDI()->get("redis");
        $redis->hDel(static::$userCookiesKey, $this->id);
    }

}
