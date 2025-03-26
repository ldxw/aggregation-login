<?php
/**
 * 执行程序
 */

define("OAUTH_API_URL", 'https://u.cccyun.cc/');

define("OAUTH_API_TYPE", 'sina');

require FCPATH.'ThirdParty/OAuth/Weibo/Oauth.class.php';
$Oauth = new \Oauth($appid, $appkey, $callback_url);
if ($action == 'callback') {
    // 表示回调返回
    if (isset($_GET['code'])) {
        $arr = $Oauth->callback();
        if(isset($arr['code']) && $arr['code']==0){
            $openid=$arr['social_uid'];
            $access_token=$arr['access_token'];
            // 入库oauth表
            $rt = \Phpcmf\Service::M('member')->insert_oauth($this->uid, $type, [
                'oid' => $arr['social_uid'],
                'oauth' => 'weibo',
                'avatar' => $arr['faceimg'],
                'unionid' => '',
                'nickname' => dr_emoji2html($arr['nickname']),
                'expire_at' => SYS_TIME,
                'access_token' => 0,
                'refresh_token' => '',
            ], null, $back);
            if (!$rt['code']) {
                $this->_msg(0, $rt['msg']);exit;
            } else {
                dr_redirect($rt['msg']);
            }
        }elseif(isset($arr['code'])){
            $this->_msg(0, dr_lang('登录失败：'.$arr['msg']));exit;
        }else{
            $this->_msg(0, dr_lang('获取登录数据失败'));exit;
        }
    } else {
        $this->_msg(0, dr_lang('回调参数code不存在'));exit;
    }
} else {
    // 跳转授权页面
    $arr = $Oauth->login(OAUTH_API_TYPE);
    if(isset($arr['code']) && $arr['code']==0){
        dr_redirect($arr['url']);
	}elseif(isset($arr['code'])){
        $this->_msg(0, dr_lang('获取登录地址失败：'.$arr['msg']));exit;
	}else{
        $this->_msg(0, dr_lang('获取登录地址失败'));exit;
	}
}