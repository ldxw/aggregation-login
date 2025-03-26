<?php
/**
 * Created by PhpStorm.
 * User: xiaoye
 * Email: 415907483@qq.com
 * Date: 2021/9/25
 * Time: 17:27
 */
namespace Plugin\D8Login\Controller;

use libs\Curl;
use Plugin\D8Login\Utils\Oauth;

class NotifyController extends \Common\Controller\HomeController
{
    private $Oauth;
    private $login_types;

    /**
     * 插件标识
     * @var string
     */
    public $code = 'D8Login';

    public function __construct()
    {
        $this->login_types = (new \Plugin\D8Login\D8LoginPlugin())->login_types;
        $plugin = M('plugin')->where(['code' => $this->code ,'status'=>1])->field('id,config')->find();
        if (!$plugin) {
            $this->error('登录方式暂未开启', '/login');exit();
        }
        $config = json_decode($plugin['config'], true);
        $this->Oauth = new Oauth($config);

        $mobile = new \Detection\MobileDetect();
        /** 检测是否是手机网站 **/
        if ($mobile->isMobile()) {
            C('TMPL_ACTION_ERROR', REAL_PATH . '/app/Plugin/'. $this->code .'/View/Mobile/dispatch_jump.html');
            C('TMPL_ACTION_SUCCESS', REAL_PATH . '/app/Plugin/'. $this->code .'/View/Mobile/dispatch_jump.html');
        } else {
            C('TMPL_ACTION_ERROR', REAL_PATH . '/app/Plugin/'. $this->code .'/View/Pc/dispatch_jump.html');
            C('TMPL_ACTION_SUCCESS', REAL_PATH . '/app/Plugin/'. $this->code .'/View/Pc/dispatch_jump.html');
        }
        parent::__construct();
    }

    public function url()
    {
        $type = I('get.type');
        $state = md5(uniqid() .time());
        session('Oauth_state',$state);
        $redirect_uri = SITE_DOMAIN . "/ApiNotify/Notify/login/plugin/" . $this->code;
        $arr = $this->Oauth->login($type, $state, $redirect_uri);
        if(isset($arr['code']) && $arr['code']==0){
            redirect($arr['url']);
        }elseif(isset($arr['code'])){
            $this->error('登录接口返回：'.$arr['msg'], '/login');
        }else{
            $this->error('获取登录地址失败', '/login');
        }
    }

    public function login()
    {
        $code = I('get.code');
        $state = I('get.state');
        if (empty($code) || empty($state)){
            $this->error('参数不能为空', '/login');
            return;
        }
        if (session('Oauth_state')!=$state){
            $this->error('非法访问', '/login');
            return;
        }

        $arr = $this->Oauth->callback($code);
        if(isset($arr['code']) && $arr['code']==0){
            $type=$arr['type'];
            $openid=$arr['social_uid'];
            $access_token=$arr['access_token'];

        }elseif(isset($arr['code'])){
            $this->error('登录失败，返回错误：'.$arr['msg'], "/login");
            return;
        }else{
            $this->error('获取登录数据失败', '/login');
            return;
        }

        $where     = [
            'type'      => $type,
            'auth_user' => $openid,
        ];
        $authLogin = M('auth_login')->where($where)->find();
        if (empty($authLogin)) {
            $this->error('账户没有被绑定，请登录后在 <strong>账号设置>第三方账号绑定</strong> 绑定后重试', "/login");
            return;
        }
        $userinfo = M('users')->where(['id' => $authLogin['user_id'], 'status' => 1])->find();
        if (empty($userinfo)) {
            $this->error('此'.$this->login_types[$type].'绑定的用户状态异常或不存在', "/login");
            return;
        }
        $usersObj = new \Niaoyun\User\Users();
        $result   = $usersObj->loginSuccess($userinfo, 5);
        if (\Niaoyun\User\Users::IDENTITY_STATUS_YES == $userinfo['identitystatus']) {
            $userinfo['name'] = encrypteAuthName($userinfo['name']);
        }
        $notice = $userinfo["name"] ? $userinfo["name"] : encryptionMobile($userinfo['mobile']);
        if ($result['result']) {
            $this->assign('customSecond', 0);
            $this->success('亲爱的' . $notice . '，欢迎回到用户中心！', '/user');
            return;
        } else {
            $this->error($result['text'], '/login');
            return;
        }
    }

}