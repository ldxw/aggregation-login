<?php

namespace addons\clogin\controller;

use addons\clogin\library\Oauth;
use app\common\controller\Api as commonApi;
use addons\clogin\library\Service;
use addons\clogin\model\CloginUser;
use app\common\library\Sms;
use fast\Random;
use think\Lang;
use think\Config;
use think\Session;
use think\Validate;

/**
 * 第三方登录插件
 */
class Api extends commonApi
{
    protected $noNeedLogin = ['getAuthUrl', 'callback', 'account']; // 无需登录即可访问的方法，同时也无需鉴权了
    protected $noNeedRight = ['*']; // 无需鉴权即可访问的方法

    protected $options = [];
    protected $config = null;

    public function _initialize()
    {
        //跨域检测
        check_cors_request();
        //设置session_id
        Config::set('session.id', $this->request->server("HTTP_SID"));

        parent::_initialize();
        $this->config = get_addon_config('clogin');
    }

    /**
     * H5获取授权链接
     * @return void
     */
    public function getAuthUrl()
    {
        $url = $this->request->param('url', '', 'trim');
        $type = $this->request->param('type');
        if (!$url || !$type) {
            $this->error('参数错误');
        }
        $this->config['callback'] = addon_url('clogin/index/callback', [':type'=>$type], true, true);
        $oauth = new Oauth($this->config);
        $arr = $oauth->login($type);
        if(isset($arr['code']) && $arr['code']==0){
            $this->success('', $arr['url']);
        }else{
            $this->error($arr ? $arr['msg'] : '获取登录地址失败');
        }
    }

    /**
     * 公众号:wechat 授权回调的请求【非第三方，自己的前端请求】
     * @return void
     */
    public function callback()
    {
        $type = $this->request->param('type');
        $state = $this->request->param('state');
        if (!$type || !$state) {
            $this->error(__('Invalid parameters'));
        }
        if ($state != Session::get('Oauth_state')){
            $this->error('Oauth_state Error');
        }
        $oauth = new Oauth($this->config);
        $arr = $oauth->callback();
        if(isset($arr['code']) && $arr['code']==0){
            $userinfo = [
                'type'     => $type,
                'openid'   => $arr['social_uid'],
                'avatar'   => $arr['faceimg'],
                'nickname' => $arr['nickname']
            ];
    
            $user = null;
            if ($this->auth->isLogin() || Service::isBindThird($type, $userinfo['openid'])) {
                Service::connect($type, $userinfo);
                $user = $this->auth->getUserinfo();
            } else {
                $user = false;
                Session::set('third-userinfo', $userinfo);
            }
            $this->success("授权成功！", ['user' => $user, 'third' => $userinfo]);
        }else{
            $this->error($arr ? $arr['msg'] : '获取登录信息失败');
        }
        
    }

    /**
     * 登录或创建账号
     */
    public function account()
    {

        if ($this->request->isPost()) {
            $params = Session::get('third-userinfo');
            $mobile = $this->request->post('mobile', '');
            $code = $this->request->post('code', $this->request->post('captcha'));
            $token = $this->request->post('__token__');
            $rule = [
                'mobile'    => 'require|regex:/^1\d{10}$/',
                '__token__' => 'require|token',
            ];
            $msg = [
                'mobile' => 'Mobile is incorrect',
            ];
            $data = [
                'mobile'    => $mobile,
                '__token__' => $token,
            ];
            $ret = Sms::check($mobile, $code, 'bind');
            if (!$ret) {
                $this->error(__('验证码错误'));
            }
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), ['__token__' => $this->request->token()]);
            }

            $userinfo = \app\common\model\User::where('mobile', $mobile)->find();
            if ($userinfo) {
                $result = $this->auth->direct($userinfo->id);
            } else {
                $result = $this->auth->register($mobile, Random::alnum(), '', $mobile, $params);
            }

            if ($result) {
                Service::connect($params['type'], $params);
                $this->success(__('绑定账号成功'), ['userinfo' => $this->auth->getUserinfo()]);
            } else {
                $this->error($this->auth->getError(), ['__token__' => $this->request->token()]);
            }
        }
    }
}
