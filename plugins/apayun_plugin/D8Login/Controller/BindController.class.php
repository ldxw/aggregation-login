<?php
/**
 * Created by PhpStorm.
 * User: xiaoye
 * Email: 415907483@qq.com
 * Date: 2021/9/25
 * Time: 17:26
 */
namespace Plugin\D8Login\Controller;

use libs\Curl;
use Plugin\D8Login\Utils\Oauth;

class BindController extends \Common\Controller\UserController
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
        $redirect_uri = SITE_DOMAIN . "/ApiNotify/Bind/index/plugin/" . $this->code;
        $arr = $this->Oauth->login($type, $state, $redirect_uri);
        if(isset($arr['code']) && $arr['code']==0){
            redirect($arr['url']);
        }elseif(isset($arr['code'])){
            $this->error('登录接口返回：'.$arr['msg'], '/login');
        }else{
            $this->error('获取登录地址失败', '/login');
        }
    }

    public function index()
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

        $url   = '/user/oauth/index';
        $authLoginModel   = M('auth_login');
        $where['_string'] = "(user_id = '{$this->userinfo['id']}' or auth_user = '{$openid}') and type = '{$type}'";
        $authLogin        = $authLoginModel->where($where)->find();
        if ($authLogin) {
            $this->assign('customSecond', 3);
            $this->error('您已经绑定了'.$this->login_types[$type].'账号或者该'.$this->login_types[$type].'账号绑定了其他用户', $url);
        }
        $add = [
            'user_id'     => $this->userinfo['id'],
            'auth_user'   => $openid,
            'type'        => $type,
            'create_time' => date('Y-m-d H:i:s'),
            'other'       => '',
        ];
        $id  = $authLoginModel->add($add);
        if ($id === false) {
            $this->error('绑定失败', $url);
            return;
        }
        $this->success('绑定成功', $url);
    }

}