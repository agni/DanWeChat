<?php

namespace Dandelion\Models;

use Phalcon\DI;
use Phalcon\Mvc\Model;

abstract class ModelBase extends Model
{
    public $id;
    public $status;
    public $createdAt;
    public $createdBy;
    public $updatedAt;
    public $updatedBy;

    public static $editableData = null;
    public static $INFO_COLUMNS = null;

    /**
     * 通过id查找对应的model
     *
     * @param string|integer $id
     * @return static
     */
    public static function ID($id)
    {
        if ($model = static::findFromCache($id)) {
            return $model;
        }
        $model = static::findFirst(["id = $id AND status > 0"]);
        if ($model) $model->updateCache();
        return $model ?: null;
    }

    private static function findFromCache($id)
    {
        if ("DEV" === ENV) return null;
        $cache = DI::getDefault()->get("modelCache");
        $cacheKey = static::class . ":" . $id;
        $model = $cache->get($cacheKey);
        return $model;
    }

    private function updateCache()
    {
        if ("DEV" === ENV) return;
        $cache = $this->getDI()->get("modelCache");
        $cacheKey = static::class . ":" . $this->id;
        if ($this->status > 0) {
            $cache->save($cacheKey, $this);
        } else {
            $cache->delete($cacheKey);
        }
    }


    /**
     * 保存到数据库并缓存，自动更新编辑者信息
     *
     * @param array $data
     * @param array $whiteList
     * @return bool
     */
    public function save($data = null, $whiteList = null)
    {
        $now = time();
        $editor = $this->getDI()->get("user");
        if (null === $this->createdAt) {
            $data["createdAt"] = $now;
            $data["createdBy"] = $editor ? $editor->id : "0";
        }
        $data["updatedAt"] = $now;
        $data["updatedBy"] = $editor ? $editor->id : "0";
        if (null !== $whiteList) {
            $whiteList = array_merge($whiteList, ["status", "createdAt", "createdBy", "updatedAt", "updatedBy"]);
        }
        if (null === $this->status && !isset($data["status"])) {
            $data["status"] = 1;
        }
        $isSaved = parent::save($data, $whiteList);
        if ($isSaved) {
            $this->updateCache();
        }
        return $isSaved;
    }

    /**
     * 逻辑删除，同时删除缓存的数据
     *
     * @param bool $hard 是否从硬盘删除
     * @return bool
     */
    public function delete($hard = false)
    {
        if (!$this->id) {
            return false;
        }
        if ($hard) {
            return parent::delete();
        }
        $time = time();
        $editor = $this->getDI()->get("user");
        return $this->save([
            "status"    => -1,
            "updatedAt" => $time,
            "updatedBy" => $editor ? $editor->id : "0",
        ]);
    }

}
