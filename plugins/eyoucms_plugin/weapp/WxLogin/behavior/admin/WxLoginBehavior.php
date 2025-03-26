<?php

namespace weapp\WxLogin\behavior\admin;

use think\Db;

/**
 * 行为扩展
 */
class WxLoginBehavior
{
    protected static $actionName;
    protected static $controllerName;
    protected static $moduleName;
    protected static $method;

    /**
     * 构造方法
     * @param Request $request Request对象
     * @access public
     */
    public function __construct()
    {
        !isset(self::$moduleName) && self::$moduleName = request()->module();
        !isset(self::$controllerName) && self::$controllerName = request()->controller();
        !isset(self::$actionName) && self::$actionName = request()->action();
        !isset(self::$method) && self::$method = strtoupper(request()->method());
    }

    /**
     * 模块初始化
     * @param array $params 传入参数
     * @access public
     */
    public function moduleInit(&$params)
    {

    }

    /**
     * 操作开始执行
     * @param array $params 传入参数
     * @access public
     */
    public function actionBegin(&$params)
    {

    }

    /**
     * 视图内容过滤
     * @param array $params 传入参数
     * @access public
     */
    public function viewFilter(&$params)
    {

    }

    /**
     * 应用结束
     * @param array $params 传入参数
     * @access public
     */
    public function appEnd(&$params)
    {
        if ('POST' == self::$method && self::$controllerName == 'Member' && self::$actionName == 'users_del') {
            if (!empty($_POST['del_id'])) {
                $users_ids = is_array($_POST['del_id']) ? $_POST['del_id'] : [$_POST['del_id']];
                Db::name('weapp_wxlogin')->where(['users_id'=>['IN', $users_ids]])->delete();
            }
        }
    }
}