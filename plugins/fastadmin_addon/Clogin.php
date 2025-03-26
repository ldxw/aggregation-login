<?php

namespace addons\clogin;

use app\common\library\Auth;
use app\common\library\Menu;
use think\Addons;
use think\Request;
use think\Session;

/**
 * 插件
 */
class Clogin extends Addons
{

    protected static $html = ['register' => '', 'profile' => ''];

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = [
            [
                'name'    => 'clogin',
                'title'   => '聚合登录管理',
                'icon'    => 'fa fa-users',
                'sublist' => [
                    [
                        "name"  => "clogin/index",
                        "title" => "查看"
                    ],
                    [
                        "name"  => "clogin/del",
                        "title" => "删除"
                    ]
                ]
            ]
        ];
        Menu::create($menu);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete("clogin");
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable("clogin");
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable("clogin");
        return true;
    }

    /**
     * 删除第三方登录表的关联数据
     */
    public function userDeleteSuccessed(\app\common\model\User $user)
    {
        \addons\clogin\model\CloginUser::where('user_id', $user->id)->delete();
    }

    /**
     * 移除第三方登录信息
     */
    public function userLogoutSuccessed(\app\common\model\User $user)
    {
        Session::delete(["wx-userinfo", "qq-userinfo", "sina-userinfo"]);
    }

    /**
     * 模块开始
     */
    public function moduleInit()
    {
        $config = $this->getConfig();
        if (!$config['types']) {
            return;
        }
        $request = Request::instance();

        $module = strtolower($request->module());
        $controller = strtolower($request->controller());
        $action = strtolower($request->action());
        if ($module !== 'index' || $controller !== 'user' || !in_array($action, ['login', 'register'])) {
            return;
        }
        $url = $request->get('url', $request->server('HTTP_REFERER', '', 'trim'), 'trim');
        $data = [
            'types' => isset($config['types']) ? explode(',', $config['types']) : [],
            'url'    => $url
        ];
        self::$html['register'] = $this->view->fetch('view/hook/user_register_end', $data);
    }

    /**
     * 方法开始
     */
    public function actionBegin()
    {
        $config = $this->getConfig();
        if (!$config['types']) {
            return;
        }
        $request = Request::instance();

        $module = strtolower($request->module());
        $controller = strtolower($request->controller());
        $action = strtolower($request->action());
        if ($module !== 'index' || $controller !== 'user' || !in_array($action, ['profile'])) {
            return;
        }
        $typelist = \addons\clogin\model\CloginUser::where('user_id', Auth::instance()->id)->column('type');
        $data = [
            'types'   => isset($config['types']) ? explode(',', $config['types']) : [],
            'typelist' => $typelist
        ];
        self::$html['profile'] = $this->view->fetch('view/hook/user_profile_end', $data);
    }

    /**
     * 配置
     * @param $params
     */
    public function configInit(&$params)
    {
        // 兼容旧版本FastAdmin
        $config = $this->getConfig();
        $module = strtolower(request()->module());
        $controller = strtolower(request()->controller());
        $action = strtolower(request()->action());
        $loginhtml = version_compare(config('fastadmin.version'), '1.3.0', '<') > 0 && $module === 'index' && $controller === 'user' && in_array($action, ['login', 'register']) ? self::$html['register'] : '';
        $params['clogin'] = ['types' => explode(',', $config['types']), 'loginhtml' => $loginhtml];
    }

    /**
     * HTML替换
     */
    public function viewFilter(& $content)
    {
        $config = $this->getConfig();
        if (!$config['types']) {
            return;
        }
        $request = Request::instance();

        $module = strtolower($request->module());
        $controller = strtolower($request->controller());
        $action = strtolower($request->action());
        if ($module !== 'index' || $controller !== 'user') {
            return;
        }
        if (in_array($action, ['login', 'register'])) {
            $html = self::$html['register'] ?? '';
            $content = str_replace(['<!--@IndexRegisterFormEnd-->', '<!--@IndexLoginFormEnd-->'], $html, $content);
        } elseif ($action === 'profile') {
            $html = self::$html['profile'] ?? '';
            $content = str_replace("<div class=\"form-group normal-footer\">", "{$html}<div class=\"form-group normal-footer\">", $content);
        }
    }
}
