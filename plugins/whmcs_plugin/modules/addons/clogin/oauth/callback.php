<?php
require_once( "../../../../init.php" );
define( "CLIENTAREA", false );
require("Oauth.class.php");

use \Illuminate\Database\Capsule\Manager as Capsule;

$type = isset($_GET['type'])?$_GET['type']:'qq';

$typearr = ['qq'=>'QQ','wx'=>'微信','alipay'=>'支付宝','sina'=>'微博'];

$settings = [];
$settingdata = Capsule::table('tbladdonmodules')->where('module', 'clogin')->get();
foreach($settingdata as $row){
	$settings[$row->setting] = $row->value;
}
$systemurl = \WHMCS\Config\Setting::getValue('SystemURL');
$callback = $systemurl.'/modules/addons/clogin/oauth/callback.php';

if(!$_GET['code'])exit('code not exists');

$Oauth_state = \WHMCS\Session::get('Oauth_state');
if($_GET['state'] != $Oauth_state){
	exit('The state does not match. You may be a victim of CSRF.');
}

$Oauth=new Oauth($settings['appurl'], $settings['appid'], $settings['appkey'], $callback);
$arr = $Oauth->callback();
if(isset($arr['code']) && $arr['code']==0){
	$type=$arr['type'];
	$openid=$arr['social_uid'];

	$uinfo = Capsule::table('mod_clogin_user')->where(['openid'=>$openid, 'type'=>$type])->first();
	if($uinfo){
		$uid = $uinfo->uid;

		// 更新内容到数据库
		Capsule::table('mod_clogin_user')->where('id', $uinfo->id)->update([
			'nickname' 	=> $arr['nickname'],
			'avatar'	=> $arr['faceimg'],
		]);

		$username = Capsule::table('tblclients')->where('id', $uid)->value('email');
		if(!$username)exit('账号信息不存在！');
		$user = \WHMCS\User\User::username($username)->first();
		\Auth::login($user);
		\Auth::setRememberCookie();
		
		header("Location: {$systemurl}/clientarea.php");
	}else{
		// 判断当前是否登录
		$userID = \WHMCS\Session::get('uid');
		if ($userID) {	
			Capsule::table('mod_clogin_user')->insert(array(
				'uid'		=> $userID,
				'type'		=> $type,
				'openid' 	=> $openid,
				'nickname'	=> $arr['nickname'],
				'avatar'	=> $arr['faceimg'],
				'addtime'	=> date("Y-m-d H:i:s"),
			));
			exit("<script language='javascript'>alert('绑定{$typearr[$type]}账号成功！');window.location.href='{$systemurl}/clientarea.php';</script>");
			
		} else {
		// 未登录
			exit("<script language='javascript'>alert('尚未绑定{$typearr[$type]}账号，请前往用户中心进行绑定！');window.location.href='{$systemurl}/clientarea.php';</script>");
		}
	}

}elseif(isset($arr['code'])){
	exit('登录失败，返回错误原因：'.$arr['msg']);
}else{
	exit('获取登录数据失败：接口通信失败');
}
