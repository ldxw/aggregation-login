<?php
!defined('EMLOG_ROOT') && exit('access deined!');

function callback_init(){
	$DB = Database::getInstance();
	if($DB->num_rows($DB->query("show columns from ".DB_PREFIX."user like 'qq_openid'")) == 0){
		$DB->query("ALTER TABLE ".DB_PREFIX."user ADD qq_openid VARCHAR(40) DEFAULT NULL");
	}
	if($DB->num_rows($DB->query("show columns from ".DB_PREFIX."user like 'wx_openid'")) == 0){
		$DB->query("ALTER TABLE ".DB_PREFIX."user ADD wx_openid VARCHAR(40) DEFAULT NULL");
	}
}

function callback_rm() {
	$DB = Database::getInstance();
	$DB->query("ALTER TABLE ".DB_PREFIX."user DROP COLUMN qq_openid");
	$DB->query("ALTER TABLE ".DB_PREFIX."user DROP COLUMN wx_openid");
}