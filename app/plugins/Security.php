<?php
/**
 * User: yz.chen
 * Time: 2018-06-30 17:58
 */

namespace Dandelion\Plugins;

use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Adapter\Memory as AclList;

/**
 * Class Security
 *
 * @package Dandelion\Plugins
 * @author  yz.chen
 *
 * @property \Phalcon\Cache\Backend\Redis $cache
 * @property \Phalcon\Config              $config
 * @property \Dandelion\Models\User       $user
 */
class Security extends Plugin
{
    /**
     * @return AclList $acl
     */
    public function getAcl()
    {
        /** @var AclList $acl */
        $acl = "DEV" === ENV ? null : $this->cache->get("AclList");
        if (!$acl) {
            $acl = new AclList();
            $acl->setDefaultAction(Acl::DENY);

            // 合并各角色访问权限和公共访问权限，并加入ACL
            $roleNames = ["guest", "admin", "user"];
            $aclConf = $this->config->get("acl")->toArray();
            $aclConfMerge = [];
            foreach ($roleNames as $roleName) {
                $acl->addRole(new Role($roleName));
                $aclConfMerge[$roleName] = array_merge_recursive($aclConf["public"], $aclConf[$roleName]);
            }

            // 添加资源到ACL
            $resourceMerge = array_merge_recursive(...array_values($aclConf));
            foreach ($resourceMerge as $resourceName => $action) {
                $acl->addResource(new Resource($resourceName), $action);
            }

            // 对角色应用ACL
            foreach ($roleNames as $roleName) {
                foreach ($aclConfMerge[$roleName] as $resourceName => $actions) {
                    $acl->allow($roleName, $resourceName, $actions);
                }
            }

            "DEV" === ENV || $this->cache->save("AclList", $acl);
        }
        return $acl;
    }

    /**
     * @param Event      $event
     * @param Dispatcher $dispatcher
     * @return bool
     */
    public function beforeDispatch(Event $event, Dispatcher $dispatcher)
    {
        return true;
        $role = $this->user ? "user" : "guest";
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();
        $acl = $this->getAcl();

        if (!$acl->isAllowed($role, $controller, $action)) {
            $this->dispatcher->forward([
                "controller" => "index",
                "action"     => "user" === $role ? "forbidden" : "unauthorized",
            ]);
            return false;
        }
        return true;
    }
}