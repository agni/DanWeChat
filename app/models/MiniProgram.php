<?php
/**
 * User: yz.chen
 * Time: 2018-08-16 14:38
 */

namespace Dandelion\Models;


class MiniProgram extends ModelBase
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
        $this->setSource("common_mini_program");
    }

    public function getSource()
    {
        return "common_mini_program";
    }
}