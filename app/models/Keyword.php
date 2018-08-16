<?php
/**
 * User: yz.chen
 * Time: 2018-07-25 15:04
 */

namespace Dandelion\Models;

class Keyword extends ModelBase
{
    public $id;
    public $name;
    public $category;
    public $status;
    public $createdAt;
    public $createdBy;
    public $updatedAt;
    public $updatedBy;

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->setSource("common_keyword");
    }

    public function getSource()
    {
        return "common_keyword";
    }
}
