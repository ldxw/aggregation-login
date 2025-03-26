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
    $openid = $_G['cookie']['sqqconnect_openid'];
    $access_token = $_G['cookie']['sqqconnect_access_token'];
    if(!$openid || !$access_token){
        showmessage('undefined_action');
    }
    $connect_member = C::t('#clogin_qq#clogin_member_qqconnect')->fetch($uid);
    if($connect_member){
        C::t('#clogin_qq#clogin_member_qqconnect')->update($uid, array(
            'openid' => $openid,
            'token' => $access_token,
            'nickname' => $_G['cookie']['sqqconnect_guestinfo_nickname'],
            'faceimg' => $_G['cookie']['sqqconnect_guestinfo_faceimg'],
            'location' => $_G['cookie']['sqqconnect_guestinfo_location'],
            'gender' => $_G['cookie']['sqqconnect_guestinfo_gender'],
            'addtime' => $_G['timestamp'],
            'isreset' => '1',
            'isregister' => '0',
        ));
    }else{
        C::t('#clogin_qq#clogin_member_qqconnect')->insert(array(
            'uid' => $uid,
            'openid' => $openid,
            'token' => $access_token,
            'nickname' => $_G['cookie']['sqqconnect_guestinfo_nickname'],
            'faceimg' => $_G['cookie']['sqqconnect_guestinfo_faceimg'],
            'location' => $_G['cookie']['sqqconnect_guestinfo_location'],
            'gender' => $_G['cookie']['sqqconnect_guestinfo_gender'],
            'addtime' => $_G['timestamp'],
            'isreset' => '1',
            'isregister' => '0',
        ));
    }

    C::t('common_member')->update($uid, array('conisbind' => '1'));

    dsetcookie('sqqconnect_guestinfo_gender');
    dsetcookie('sqqconnect_guestinfo_nickname');
    dsetcookie('sqqconnect_guestinfo_faceimg');
    dsetcookie('sqqconnect_guestinfo_location');
    dsetcookie('sqqconnect_access_token');
    dsetcookie('sqqconnect_openid');
    dsetcookie('sqqconnect_state');
}

?>