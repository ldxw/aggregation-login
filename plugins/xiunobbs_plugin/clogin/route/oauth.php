<?php

!defined('DEBUG') AND exit('Access Denied.');

include _include(APP_PATH.'plugin/clogin/model/clogin.func.php');
include _include(APP_PATH.'plugin/clogin/model/Oauth.class.php');

$action = param(1);
$type = param('type');
if(!$type)message(-1, 'type不能为空');

$typearr = ['qq'=>'QQ','wx'=>'微信','sina'=>'微博','alipay'=>'支付宝'];
$typename = $typearr[$type];

if(empty($action)) {
	
	$oauth_config = kv_get('clogin');
	$callback = http_url_path().url('oauth-return');

	$Oauth=new Oauth($oauth_config['apiurl'], $oauth_config['appid'], $oauth_config['appkey'], $callback);
	$arr = $Oauth->login($type);
	if(isset($arr['code']) && $arr['code']==0){
		http_location($arr['url']);
	}elseif(isset($arr['code'])){
		message(-1, '获取登录地址失败：'.$arr['msg']);
	}else{
		message(-1, '获取登录地址失败');
	}

} elseif($action == 'return') {

	if($_GET['state'] != $_SESSION['Oauth_state']){
		message(-1, "The state does not match. You may be a victim of CSRF.");
	}

	$oauth_config = kv_get('clogin');

	$Oauth=new Oauth($oauth_config['apiurl'], $oauth_config['appid'], $oauth_config['appkey']);
	$arr = $Oauth->callback();
	if(isset($arr['code']) && $arr['code']==0){
		$type=$arr['type'];
		$openid=$arr['social_uid'];

		if (empty($uid)) { //未登录
			$user = clogin_read_user_by_openid($type, $openid);
			if(!$user) {
				if($oauth_config['autoreg']==1){
					$user = clogin_create_user($type, $openid, $arr['nickname'], $arr['faceimg']);
					!$user && message($errno, $errstr);

					$uid = $user['uid'];
					user_update($user['uid'], array('login_ip'=>$longip, 'login_date' =>$time , 'logins+'=>1));
					$_SESSION['uid'] = $uid;
					user_token_set($uid);

					message(0, jump('用户注册成功！', http_url_path(), 2));
				}else{
					message(-1, jump('该'.$typename.'账号未绑定本站用户，请使用用户名登录后绑定', http_url_path().url('user-login'), 2));
				}
			}else{
				$uid = $user['uid'];
				user_update($user['uid'], array('login_ip'=>$longip, 'login_date' =>$time , 'logins+'=>1));
				$_SESSION['uid'] = $uid;
				user_token_set($uid);

				message(0, jump('登录成功！', http_url_path(), 2));
			}
		}else{ //已登录
			$user = clogin_read_user_by_openid($type, $openid);
			if (!$user){
				clogin_bind_user($uid, $type, $openid);
				message(0, jump($typename.'账号绑定成功', http_url_path().url('my'), 2));
			}elseif($user['uid'] == $uid){
				message(0, jump($typename.'账号绑定成功', http_url_path().url('my'), 2));
			}else{
				message(-1, jump('该'.$typename.'账号已被本站其他用户绑定，请更换'.$typename.'账号重试', http_url_path().url('my'), 2));
			}
		}

	}elseif(isset($arr['code'])){
		message(-1, '登录失败，返回错误原因：'.$arr['msg']);
	}else{
		message(-1, '获取登录数据失败');
	}
	
}elseif ($action == 'unbind'){
	if (empty($uid)){
		message(-1, jump('请先登录本站', http_url_path().url('user-login'), 2));
	}else{
		db_delete('user_clogin_connect', array('uid'=>$uid, 'type'=>$type));
		message(0, jump($typename.'账号解绑成功', http_url_path().url('my'), 2));
	}
}
?>