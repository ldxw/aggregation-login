<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once DISCUZ_ROOT . '/source/plugin/clogin_wx/class/Oauth.class.php';

global $_G, $swxconnect;

$oauth = new SWXOauth($swxconnect);

$loginurl = $oauth->login($swxconnect['logintype']);

dheader("Location:$loginurl");
