<?php
/**
 * User: yz.chen
 * Time: 2018-08-16 14:38
 */

namespace Dandelion\Models;


class App extends ModelBase
{
    public $id;
    public $name;
    public $appId;
    public $appSecret;
    public $appKey;
    public $account;
    public $password;
    public $extra;
    public $status;
    public $createdAt;
    public $createdBy;
    public $updatedAt;
    public $updatedBy;

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->setSource("wx_app");
    }

    public function getSource()
    {
        return "wx_app";
    }
}