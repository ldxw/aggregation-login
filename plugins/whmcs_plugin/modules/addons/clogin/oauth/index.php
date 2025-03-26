<?php
require_once( "../../../../init.php" );
define( "CLIENTAREA", false );
require("Oauth.class.php");

use \Illuminate\Database\Capsule\Manager as Capsule;

$userID = \WHMCS\Session::get('uid');
$systemurl = \WHMCS\Config\Setting::getValue('SystemURL');

$type = isset($_GET['type'])?$_GET['type']:'qq';

$typearr = ['qq'=>'QQ','wx'=>'微信','alipay'=>'支付宝','sina'=>'微博'];

if($userID && isset($_GET['unbind']) && $_GET['unbind'] == '1'){
	$uinfo = Capsule::table('mod_clogin_user')->where(['uid'=>$userID, 'type'=>$type])->first();
	if($uinfo){
		Capsule::table('mod_clogin_user')->where('id', $uinfo->id)->delete();
	}
	exit("<script language='javascript'>alert('解绑{$typearr[$type]}账号成功！');window.location.href='{$systemurl}/clientarea.php';</script>");
}else{

	$settings = [];
	$settingdata = Capsule::table('tbladdonmodules')->where('module', 'clogin')->get();
	foreach($settingdata as $row){
		$settings[$row->setting] = $row->value;
	}

	$callback = $systemurl.'/modules/addons/clogin/oauth/callback.php';

	$Oauth=new Oauth($settings['appurl'], $settings['appid'], $settings['appkey'], $callback);
	$arr = $Oauth->login($type);
	if(isset($arr['code']) && $arr['code']==0){
		header("Location: ".$arr['url']);
	}elseif(isset($arr['code'])){
		exit('获取登录地址失败：'.$arr['msg']);
	}else{
		exit('获取登录地址失败：接口通信失败');
	}
}
