<?php

/*
	Xiuno BBS 4.0 插件实例：QQ 登陆插件设置
	admin/plugin-setting-clogin.htm
*/

!defined('DEBUG') AND exit('Access Denied.');

$typearr = ['qq'=>'QQ','wx'=>'微信','sina'=>'微博','alipay'=>'支付宝'];

if($method == 'GET') {
	
	$kv = kv_get('clogin');
	
	$input = array();
	$input['apiurl'] = form_text('apiurl', $kv['apiurl']);
	$input['appid'] = form_text('appid', $kv['appid']);
	$input['appkey'] = form_text('appkey', $kv['appkey']);
    $input['autoreg'] = form_radio_yes_no('autoreg', $kv['autoreg']);
	$input['opentype'] = form_multi_checkbox('opentype[]', $typearr, explode(',',$kv['opentype']));

	include _include(APP_PATH.'plugin/clogin/setting.htm');
	
} else {

	$kv = array();
	$kv['apiurl'] = param('apiurl');
	$kv['appid'] = param('appid');
	$kv['appkey'] = param('appkey');
    $kv['autoreg'] = param('autoreg');
	$restype = [];
	$arr = $_POST['opentype'];
	$i = 0;
	foreach($typearr as $type=>$value){
		if($arr[$i++] == 1) $restype[] = $type;
	}
	$kv['opentype'] = implode(',',$restype);

	kv_set('clogin', $kv);
	
	message(0, '修改成功');
}
	
?>