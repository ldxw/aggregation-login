<?php
/**
 * 易优CMS
 * ============================================================================
 * 版权所有 2016-2028 海南赞赞网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.eyoucms.com
 * ----------------------------------------------------------------------------
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 * Author: 小虎哥 <1105415366@qq.com>
 * Date: 2018-4-3
 */

namespace app\plugins\controller;

use weapp\QqLogin\vendor\Oauth;
use think\Db;

class QqLogin extends Base
{
    private $oauth;
    private $isbind;
    const LoginType = 'qq';

    /**
     * 构造方法
     */
    public function __construct(){
        parent::__construct();
        $config = Db::name('weapp')->where('code', 'QqLogin')->find();
        if (empty($config['status'])) {
            $this->error('请后台启用QQ快捷登录插件！');
        }
        $data = unserialize($config['data']);
        if (empty($data['appurl']) || empty($data['appid']) || empty($data['appkey'])){
            $this->error("登录失败，请联系管理员配置QQ快捷登录信息");
        }
        $callback = $this->request->domain().$this->root_dir.'/index.php?m=plugins&c=QqLogin&a=callback&lang='.$this->home_lang;
        $this->oauth = new Oauth($data['appurl'],$data['appid'],$data['appkey'],$callback);
        $this->isbind = $data['bind'];
    }

    //登陆
    public function login(){
        $arr = $this->oauth->login(self::LoginType);
        if(isset($arr['code']) && $arr['code']==0){
            exit("<script language='javascript'>window.location.href='{$arr['url']}';</script>");
        }elseif(isset($arr['code'])){
            $this->error('获取登录地址失败：'.$arr['msg']);
        }else{
            $this->error('获取登录地址失败');
        }
    }

    //返回
    public function callback(){
        $arr = $this->oauth->callback();
        if(isset($arr['code']) && $arr['code']==0){
            $openid=$arr['social_uid'];
            $access_token=$arr['access_token'];
            /* 处理用户登录逻辑 */

            $connect = Db::name("weapp_qqlogin")->where('openid', $openid)->find();    //判断绑定关系是否存在
            if($connect){ //绑定关系存在
                $users = Db::name("users")->where(['users_id' => $connect['users_id']])->find();
                if (empty($users)) { //用户不存在，自动注册账号，并且登陆
                    $users = $this->setReg($openid, $arr['nickname'], $arr['faceimg']);
                    if (!empty($users['users_id'])) {
                        Db::name('weapp_qqlogin')->where('id', $connect['id'])->update([
                            'users_id'  => $users['users_id'],
                            'update_time' => getTime(),
                        ]);
                    }
                }
            }else{ //绑定关系不存在
                $users = $this->setReg($openid, $arr['nickname'], $arr['faceimg']);
                if (!empty($users)) {
                    Db::name('weapp_qqlogin')->insert([
                        'users_id'   => $users['users_id'],
                        'openid'     => $openid,
                        'nickname'   => $arr['nickname'],
                        'add_time'   => getTime(),
                    ]);
                }
            }
            $this->setLogin($users);
        }elseif(isset($arr['code'])){
            $this->error('登录失败，返回错误原因：'.$arr['msg']);
        }else{
            $this->error('获取登录数据失败');
        }
    }

    //注册
    public function setReg($openid, $nickname, $faceimg){
        $users = [];
        $head_pic = !empty($faceimg) ? $faceimg : ROOT_DIR . '/public/static/common/images/dfboy.png';

        $username = 'QQ'.substr($openid,-8);
        $username = $this->createUsername($username);
        $data['username']       = $username;
        $data['nickname']       = !empty($nickname) ? $nickname : $username;
        $data['level']  = 1;
        $data['thirdparty'] = 2;
        $data['openid']       = $openid;
        $data['register_place']       = 2; // 注册位置，后台注册不受注册验证影响，1为后台注册，2为前台注册。
        $data['open_level_time']       = getTime();
        $data['level_maturity_days']       = 0;
        $data['reg_time']       = getTime();
        $data['head_pic']       = $head_pic;
        $data['lang'] = $this->home_lang;
        $data['add_time']     = getTime();
        $users_id =  Db::name('users')->insertGetId($data);
        if (!empty($users_id)) {
            $users = Db::name('users')->find($users_id);
        }
        return $users;
    }

    //登陆
    public function setLogin($users){

        if (empty($users['is_activation'])) {
            $this->error('该会员尚未激活，请联系管理员！', url('user/Users/login'));
        }

        if (!empty($users)) {
            session('Oauth_state', null);
            model('EyouUsers')->loginAfter($users);
            // 跳转链接
            $referurl = cookie('referurl');
            if($referurl){
                \think\Cookie::delete('referurl');
            }else{
                $referurl = $this->request->domain().$this->root_dir.'/';
            }
            $this->redirect($referurl);
        }else{
            $this->error('登录失败', null, ['status'=>1]);
        }
    }

    /**
     * 生成用户名，确保唯一性
     */
    public function createUsername($username = '')
    {
        if (empty($username)) {
            $username = 'QQ'.get_rand_str(8,0,1);
        }
        $username = strtoupper($username);
        $count = Db::name('users')->where('username', $username)->count();
        if (!empty($count)) {
            $username = 'QQ'.get_rand_str(8,0,1);
            return $this->createUsername($username);
        }

        return $username;
    }
}