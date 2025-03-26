<?php
!defined('EMLOG_ROOT') && exit('access deined!');
if (!defined('CLOGIN_ROOT')) {
	define('CLOGIN_ROOT',EMLOG_ROOT.'/content/plugins/clogin/');
}
require_once(CLOGIN_ROOT.'clogin_config.php');
include CLOGIN_ROOT.'lib/Oauth.class.php';
session_start();

$DB = Database::getInstance();

$type = isset($_GET['type'])?$_GET['type']:exit('no type');
$typearr = ['qq'=>'QQ','wx'=>'微信'];
$typename = $typearr[$type];
if($type == 'wx'){
	$column = 'wx_openid';
}else{
	$column = 'qq_openid';
}

if(ISLOGIN && $_GET['action'] == 'unbind'){
	$sql = "UPDATE `".DB_PREFIX."user` SET `$column`=NULL WHERE `uid`=".UID."";
	$DB->query($sql);
	exit("<script language='javascript'>alert('解绑{$typename}账号成功！');window.location.href='./admin/plugin.php?plugin=clogin';</script>");
}
elseif(isset($_GET['code']))
{
	if($_GET['state'] != $_SESSION['Oauth_state']){
		emMsg("The state does not match. You may be a victim of CSRF.");
	}
	$Oauth=new Oauth($clogin_config['apiurl'], $clogin_config['appid'], $clogin_config['appkey']);
	$arr = $Oauth->callback();
	if (isset($arr['code']) && $arr['code']==0) {
		$type=$arr['type'];
		$openid=$arr['social_uid'];
		if(ISLOGIN){
			$user=$DB->once_fetch_array('SELECT * FROM `'.DB_PREFIX.'user` WHERE `'.$column.'`=\''.$openid.'\' LIMIT 1');
			if($user && $user['uid']!=UID){
				emMsg('该'.$typename.'账号已被本站其他用户绑定，请更换'.$typename.'账号重试','./admin/plugin.php?plugin=clogin');
			}else{
				$DB->query('UPDATE `'.DB_PREFIX.'user` SET `'.$column.'`=\''.$openid.'\' WHERE `uid`='.UID.'');
				exit("<script language='javascript'>alert('绑定{$typename}账号成功！');window.location.href='./admin/plugin.php?plugin=clogin';</script>");
			}
		}else{
			$user=$DB->once_fetch_array('SELECT * FROM `'.DB_PREFIX.'user` WHERE `'.$column.'`=\''.$openid.'\' LIMIT 1');
			if($user){
				LoginAuth::setAuthCookie($user['username'], true);
				emDirect('./admin/');
			}else{
				emMsg('该'.$typename.'账号未绑定本站用户，请使用用户名登录后绑定','./admin/');
			}
		}
	}elseif(isset($arr['code'])){
		emMsg('登录失败，返回错误原因：'.$arr['msg']);
	}else{
		emMsg('获取登录数据失败');
	}
}
else
{
	$callback = BLOG_URL.'?plugin=clogin';
	$Oauth=new Oauth($clogin_config['apiurl'], $clogin_config['appid'], $clogin_config['appkey'], $callback);
	$arr = $Oauth->login($type);
	if(isset($arr['code']) && $arr['code']==0){
		emDirect($arr['url']);
	}elseif(isset($arr['code'])){
		emMsg('获取登录地址失败：'.$arr['msg']);
	}else{
		emMsg('获取登录地址失败');
	}
}
