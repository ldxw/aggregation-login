<?php

namespace addons\clogin\controller;

use addons\clogin\library\Service;
use addons\clogin\library\Oauth;
use addons\clogin\model\CloginUser;
use think\addons\Controller;
use think\Config;
use think\Cookie;
use think\Hook;
use think\Lang;
use think\Session;

/**
 * 第三方登录插件
 */
class Index extends Controller
{
    protected $app = null;
    protected $options = [];
    protected $config = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->config = get_addon_config('clogin');
    }

    /**
     * 插件首页
     */
    public function index()
    {
        if (!\app\admin\library\Auth::instance()->id) {
            $this->error('当前插件暂无前台页面');
        }
        $typelist = [];
        if ($this->auth->id) {
            $typelist = CloginUser::where('user_id', $this->auth->id)->column('type');
        }
        $this->view->assign('typelist', $typelist);
        return $this->view->fetch();
    }

    /**
     * 发起授权
     */
    public function connect()
    {
        $type = $this->request->param('type');
        $types = explode(',', $this->config['types']);
        if (!in_array($type, $types)) {
            $this->error("该登录方式已关闭");
        }

        $this->config['callback'] = addon_url('clogin/index/callback', [':type'=>$type], true, true);
        $oauth = new Oauth($this->config);
        $arr = $oauth->login($type);
        if(isset($arr['code']) && $arr['code']==0){
            $url = $this->request->request('url', $this->request->server('HTTP_REFERER', '/', 'trim'), 'trim');
            if ($url) {
                Session::set("redirecturl", $url);
            }
            $this->redirect($arr['url']);
        }else{
            $this->error($arr ? $arr['msg'] : '获取登录地址失败');
        }
    }

    /**
     * 通知回调
     */
    public function callback()
    {
        $auth = $this->auth;
        $type = $this->request->param('type');
        $state = $this->request->param('state');
        if (!$type || !$state) {
            $this->error(__('Invalid parameters'));
        }
        if ($state != Session::get('Oauth_state')){
            $this->error('Oauth_state Error');
        }

        //监听注册登录注销的事件
        Hook::add('user_login_successed', function ($user) use ($auth) {
            $expire = input('post.keeplogin') ? 30 * 86400 : 0;
            Cookie::set('uid', $user->id, $expire);
            Cookie::set('token', $auth->getToken(), $expire);
        });
        Hook::add('user_register_successed', function ($user) use ($auth) {
            Cookie::set('uid', $user->id);
            Cookie::set('token', $auth->getToken());
        });
        Hook::add('user_logout_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
        
        // 成功后返回之前页面，但忽略登录/注册页面
        $url = Session::has("redirecturl") ? Session::pull("redirecturl") : url('index/user/index');
        $url = preg_match("/\/user\/(register|login|resetpwd)/i", $url) ? url('index/user/index') : $url;

        // 授权成功后的回调
        $oauth = new Oauth($this->config);
        $arr = $oauth->callback();
        if(isset($arr['code']) && $arr['code']==0){
            $userinfo = [
                'type'     => $type,
                'openid'   => $arr['social_uid'],
                'avatar'   => $arr['faceimg'],
                'nickname' => $arr['nickname']
            ];
            Session::set("{$type}-userinfo", $userinfo);
            //判断是否启用账号绑定
            $third = CloginUser::get(['type' => $type, 'openid' => $userinfo['openid']]);
            if (!$third) {
                //要求绑定账号或会员当前是登录状态
                if ($this->config['bindaccount'] || $this->auth->id) {
                    $this->redirect(url('index/clogin/prepare') . "?" . http_build_query(['type' => $type, 'url' => $url]));
                }
            }

            //直接登录
            $loginret = Service::connect($type, $userinfo);
            if ($loginret) {
                $this->redirect($url);
            } else {
                $this->error("登录失败，请返回重试", $url);
            }
        }else{
            $this->error($arr ? $arr['msg'] : '获取登录信息失败');
        }
    }

    /**
     * 绑定账号
     */
    public function bind()
    {
        $type = $this->request->request('type', $this->request->param('type', ''));
        $url = $this->request->get('url', $this->request->server('HTTP_REFERER', '', 'trim'), 'trim');
        $redirecturl = url("index/clogin/bind") . "?" . http_build_query(['type' => $type, 'url' => $url]);
        $this->redirect($redirecturl);
        return;
    }

    /**
     * 解绑账号
     */
    public function unbind()
    {
        $type = $this->request->request('type', $this->request->param('type', ''));
        $url = $this->request->get('url', $this->request->server('HTTP_REFERER', '', 'trim'), 'trim');
        $redirecturl = url("index/clogin/unbind") . "?" . http_build_query(['type' => $type, 'url' => $url]);
        $this->redirect($redirecturl);
        return;
    }

}
