<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: member_connect_logging.php 33543 2013-07-03 06:01:33Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

if(!empty($_POST)) {
    $userdata = array();
    $userdata['avatarstatus'] = 0;
    $userdata['conisbind'] = 1;
    $openid = $_G['cookie']['swxconnect_openid'];
    $access_token = $_G['cookie']['swxconnect_access_token'];
    if(!$openid || !$access_token){
        showmessage('undefined_action');
    }
    $connect_member = C::t('#clogin_wx#clogin_member_wxconnect')->fetch($uid);
    if($connect_member){
        C::t('#clogin_wx#clogin_member_wxconnect')->update($uid, array(
            'openid' => $openid,
            'token' => $access_token,
            'nickname' => $_G['cookie']['swxconnect_guestinfo_nickname'],
            'faceimg' => $_G['cookie']['swxconnect_guestinfo_faceimg'],
            'location' => $_G['cookie']['swxconnect_guestinfo_location'],
            'gender' => $_G['cookie']['swxconnect_guestinfo_gender'],
            'addtime' => $_G['timestamp'],
            'isreset' => '1',
            'isregister' => '0',
        ));
    }else{
        C::t('#clogin_wx#clogin_member_wxconnect')->insert(array(
            'uid' => $uid,
            'openid' => $openid,
            'token' => $access_token,
            'nickname' => $_G['cookie']['swxconnect_guestinfo_nickname'],
            'faceimg' => $_G['cookie']['swxconnect_guestinfo_faceimg'],
            'location' => $_G['cookie']['swxconnect_guestinfo_location'],
            'gender' => $_G['cookie']['swxconnect_guestinfo_gender'],
            'addtime' => $_G['timestamp'],
            'isreset' => '1',
            'isregister' => '0',
        ));
    }

    C::t('common_member')->update($uid, array('conisbind' => '1'));

    dsetcookie('swxconnect_guestinfo_gender');
    dsetcookie('swxconnect_guestinfo_nickname');
    dsetcookie('swxconnect_guestinfo_faceimg');
    dsetcookie('swxconnect_guestinfo_location');
    dsetcookie('swxconnect_access_token');
    dsetcookie('swxconnect_openid');
    dsetcookie('swxconnect_state');
}

?>