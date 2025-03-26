<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once DISCUZ_ROOT . '/source/plugin/clogin_qq/class/Oauth.class.php';

global $_G, $sqqconnect;

$oauth = new SQQOauth($sqqconnect);

$loginurl = $oauth->login($sqqconnect['logintype']);

header("Location:$loginurl");
