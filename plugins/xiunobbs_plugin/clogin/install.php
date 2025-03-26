<?php

/*
	Xiuno BBS 4.0 插件实例：QQ 登陆安装
	admin/plugin-install-sifoucn_friendlink.htm
*/

!defined('DEBUG') AND exit('Forbidden');

$tablepre = $db->tablepre;
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}user_clogin_connect (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`uid` int(11) unsigned NOT NULL,
	`type` varchar(20) NOT NULL,
	`openid` varchar(100) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `uid` (`uid`),
	KEY `openid` (`openid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

$r = db_exec($sql);

// 初始化
$kv = kv_get('clogin');
if(!$kv) {
	$kv = array('apiurl'=>'https://u.cccyun.cc/', 'appid'=>'', 'appkey'=>'', 'autoreg'=>'0', 'opentype'=>'');
	kv_set('clogin', $kv);
}

?>