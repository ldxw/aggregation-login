<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Lang;
use think\Session;

/**
 * 第三方登录控制器
 */
class Clogin extends Frontend
{
    protected $noNeedLogin = ['prepare'];
    protected $noNeedRight = ['*'];
    protected $options = [];
    protected $layout = 'default';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 准备绑定
     */
    public function prepare()
    {
        $type = $this->request->request('type');
        $url = $this->request->get('url', '/', 'trim');
        if ($this->auth->id) {
            $this->redirect(url("index/clogin/bind") . "?" . http_build_query(['type' => $type, 'url' => $url]));
        }

        // 授权成功后的回调
        $userinfo = Session::get("{$type}-userinfo");
        if (!$userinfo) {
            $this->error("操作失败，请返回重新登录");
        }

        Lang::load([
            APP_PATH . 'index' . DS . 'lang' . DS . $this->request->langset() . DS . 'user' . EXT,
        ]);

        $this->view->assign('userinfo', $userinfo);
        $this->view->assign('type', $type);
        $this->view->assign('url', $url);
        $this->view->assign('bindurl', url("index/clogin/bind") . '?' . http_build_query(['type' => $type, 'url' => $url]));
        $this->view->assign('captchaType', config('fastadmin.user_register_captcha'));
        $this->view->assign('title', "账号绑定");

        return $this->view->fetch();
    }

    /**
     * 绑定账号
     */
    public function bind()
    {
        $type = $this->request->request('type');
        $url = $this->request->get('url', $this->request->server('HTTP_REFERER', '', 'trim'), 'trim');
        if (!$type) {
            $this->error("参数不正确");
        }

        // 授权成功后的回调
        $userinfo = Session::get("{$type}-userinfo");
        if (!$userinfo) {
            $this->redirect(addon_url('clogin/index/connect', [':type' => $type]) . '?url=' . urlencode($url));
        }
        $cloginUser = \addons\clogin\model\CloginUser::where('user_id', $this->auth->id)->where('type', $type)->find();
        if ($cloginUser) {
            $this->error("已绑定账号，请勿重复绑定");
        }
        $values = [
            'user_id'       => $this->auth->id,
            'type'          => $type,
            'openid'        => $userinfo['openid'],
            'openname'      => isset($userinfo['nickname']) ? $userinfo['nickname'] : '',
        ];
        $cloginUser = \addons\clogin\model\CloginUser::create($values);
        if ($cloginUser) {
            $this->success("账号绑定成功", $url);
        } else {
            $this->error("账号绑定失败，请重试", $url);
        }
    }

    /**
     * 解绑账号
     */
    public function unbind()
    {
        $type = $this->request->request('type');
        $cloginUser = \addons\clogin\model\CloginUser::where('user_id', $this->auth->id)->where('type', $type)->find();
        if (!$cloginUser) {
            $this->error("未找到指定的账号绑定信息");
        }
        Session::delete("{$type}-userinfo");
        $cloginUser->delete();
        $this->success("账号解绑成功");
    }
}
