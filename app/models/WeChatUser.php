<?php
/**
 * User: yz.chen
 * Time: 2018-08-16 15:10
 */

namespace Dandelion\Models;


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

    public function login()
    {

    }
}