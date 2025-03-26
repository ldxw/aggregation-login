<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once DISCUZ_ROOT . '/source/plugin/clogin_qq/class/Oauth.class.php';
require_once DISCUZ_ROOT . '/source/plugin/clogin_qq/function/function_base.php';


global $_G, $sqqconnect;
$referer = $_GET['dreferers'] ? urldecode($_GET['dreferers']) : dreferer();


$oauth = new SQQOauth($sqqconnect);
$arr = $oauth->callback();
$access_token = $arr['access_token'];
$openid = $arr['social_uid'];
$logintype = $_GET['type'] ? $_GET['type'] : 'qq';

$userinfo = array(
    'gender' => diconv($arr["gender"], 'UTF-8', CHARSET),
    'nickname' => diconv($arr["nickname"], 'UTF-8', CHARSET),
    'faceimg' => $arr['faceimg'],
    'location' => diconv($arr['location'], 'UTF-8', CHARSET),
);

$connect_member = C::t('#clogin_qq#clogin_member_qqconnect')->fetch_fields_by_openid($openid);

if($_G['uid']) { //已登录状态，直接绑定QQ号

    if($connect_member && $connect_member['uid'] != $_G['uid']) {
        showmessage('clogin_qq:connect_register_bind_already', $referer);
    }

    $connect_member = C::t('#clogin_qq#clogin_member_qqconnect')->fetch($_G['uid']);
    if($connect_member){
        C::t('#clogin_qq#clogin_member_qqconnect')->update($_G['uid'], array(
            'openid' => $openid,
            'token' => $access_token,
            'nickname' => $userinfo['nickname'],
            'faceimg' => $userinfo['faceimg'],
            'location' => $userinfo['location'],
            'gender' => $userinfo['gender'],
            'addtime' => $_G['timestamp'],
            'isreset' => '1',
            'isregister' => '0',
        ));
    }else{
        C::t('#clogin_qq#clogin_member_qqconnect')->insert(array(
            'uid' => $_G['uid'],
            'openid' => $openid,
            'token' => $access_token,
            'nickname' => $userinfo['nickname'],
            'faceimg' => $userinfo['faceimg'],
            'location' => $userinfo['location'],
            'gender' => $userinfo['gender'],
            'addtime' => $_G['timestamp'],
            'isreset' => '1',
            'isregister' => '0',
        ));
    }

    dsetcookie('sqqconnect_is_bind', '1', 8640000);
    $referer = $_G['siteurl'] . 'home.php?mod=spacecp&ac=plugin&id=clogin_qq:bind';
    showmessage('clogin_qq:connect_register_bind_success', $referer);

}else{ // 未登录状态
    if($connect_member) { //账号已存在，直接登录进论坛
        C::t('#clogin_qq#clogin_member_qqconnect')->update($connect_member['uid'], array(
            'token' => $access_token,
            'nickname' => $userinfo['nickname'],
            'faceimg' => $userinfo['faceimg'],
            'location' => $userinfo['location'],
            'gender' => $userinfo['gender'],
        ));
    
        $params['mod'] = 'login';
        sqconnect_login($connect_member['uid']);
    
        loadcache('usergroups');
        $usergroups = $_G['cache']['usergroups'][$_G['groupid']]['grouptitle'];
        $param = array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle']);
    
        C::t('common_member_status')->update($connect_member['uid'], array('lastip'=>$_G['clientip'], 'lastvisit'=>TIMESTAMP, 'lastactivity' => TIMESTAMP));
        $ucsynlogin = '';
        if($_G['setting']['allowsynlogin']) {
            loaducenter();
            $ucsynlogin = uc_user_synlogin($_G['uid']);
        }
    
        dsetcookie('stats_qc_login', 3, 86400);
        showmessage('login_succeed', $referer, $param, array('extrajs' => $ucsynlogin));
    
    } elseif($sqqconnect['on_autoregister']) { //账号不存在，并且开启了自动注册用户
        
        require_once libfile('function/member');

        
        $username = $userinfo["nickname"];

        loaducenter();
        $groupid = $sqqconnect['on_gr'] ? $sqqconnect['on_gr'] : $_G['setting']['newusergroupid'];
    
        $password = md5(random(10));
        $email = 'qqconnect_' . strtolower(random(10)) . '@null.null';

        $usernamelen = dstrlen($username);
        if($usernamelen < 3) {
            $username = $username . '_' . random(5);
        }

        $username = sqq_user_checkname($username);


        $censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')).')$/i';
        if($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
            if(!$return) {
                showmessage('profile_username_protect');
            } else {
                return;
            }
        }

        
        $uid = uc_user_register(addslashes($username), $password, $email, '', '', $_G['clientip']);
    
        if($uid <= 0) {
            if(!$return) {
                if($uid == -1) {
                    showmessage('profile_username_illegal');
                } elseif($uid == -2) {
                    showmessage('profile_username_protect');
                } elseif($uid == -3) {
                    showmessage('profile_username_duplicate');
                } elseif($uid == -4) {
                    showmessage('profile_email_illegal');
                } elseif($uid == -5) {
                    showmessage('profile_email_domain_illegal');
                } elseif($uid == -6) {
                    showmessage('profile_email_duplicate');
                } else {
                    showmessage('undefined_action');
                }
            } else {
                return;
            }
        }
        
        $init_arr = array('credits' => explode(',', $_G['setting']['initcredits']));
        C::t('common_member')->insert($uid, $username, $password, $email, $_G['clientip'], $groupid, $init_arr);

        if($_G['setting']['regctrl'] || $_G['setting']['regfloodctrl']) {
            C::t('common_regip')->delete_by_dateline($_G['timestamp'] - ($_G['setting']['regctrl'] > 72 ? $_G['setting']['regctrl'] : 72) * 3600);
            if($_G['setting']['regctrl']) {
                C::t('common_regip')->insert(array('ip' => $_G['clientip'], 'count' => -1, 'dateline' => $_G['timestamp']));
            }
        }

        if($_G['setting']['regverify'] == 2) {
            C::t('common_member_validate')->insert(array(
                'uid' => $uid,
                'submitdate' => $_G['timestamp'],
                'moddate' => 0,
                'admin' => '',
                'submittimes' => 1,
                'status' => 0,
                'message' => '',
                'remark' => '',
                ), false, true);
            manage_addnotify('verifyuser');
        }

        C::t('#clogin_qq#clogin_member_qqconnect')->insert(array(
            'uid' => $uid,
            'openid' => $openid,
            'token' => $access_token,
            'nickname' => $userinfo['nickname'],
            'faceimg' => $userinfo['faceimg'],
            'location' => $userinfo['location'],
            'gender' => $userinfo['gender'],
            'addtime' => $_G['timestamp'],
            'isreset' => '0',
            'isregister' => '1',
        ));

        if($userinfo['faceimg']) {       

            require_once DISCUZ_ROOT . '/source/plugin/clogin_qq/class/upload_base.class.php';

            $upload_avatar = new upload_base();

            $home = $upload_avatar->get_home($uid);

            if(!is_dir(DISCUZ_ROOT. 'uc_server/data/avatar/'. $home)) {
                $upload_avatar->set_home($uid, DISCUZ_ROOT. 'uc_server/data/avatar/');
            }
            $avatartype = 'virtual';
            $bigavatarfile =   DISCUZ_ROOT. 'uc_server/data/avatar/' .  $upload_avatar->get_avatar($uid, 'big', $avatartype);
            $middleavatarfile = DISCUZ_ROOT. 'uc_server/data/avatar/'. $upload_avatar->get_avatar($uid, 'middle', $avatartype);
            $smallavatarfile = DISCUZ_ROOT. 'uc_server/data/avatar/'. $upload_avatar->get_avatar($uid, 'small', $avatartype);


            $bigavatar = $userinfo['faceimg'];

            sqqgetImage($bigavatar, $bigavatarfile);
            sqqgetImage($bigavatar, $middleavatarfile);
            sqqgetImage($bigavatar, $smallavatarfile);

        }


        include_once libfile('function/stat');
        updatestat('register');
        
        $member = getuserbyuid($uid, 1);      
        
        setloginstatus($member, 1296000); 

        dheader("Location:$referer");
        exit;

    }else{ //账号不存在，跳转到引导用户绑定或注册新账号页面

        dsetcookie('sqqconnect_openid', $openid, 31536000);
        dsetcookie('sqqconnect_access_token', $access_token, 31536000);
        dsetcookie('sqqconnect_guestinfo_nickname', $userinfo["nickname"], 31536000);
        dsetcookie('sqqconnect_guestinfo_faceimg', $userinfo['faceimg'], 31536000);
        dsetcookie('sqqconnect_guestinfo_location', $userinfo['location'], 31536000);
        dsetcookie('sqqconnect_guestinfo_gender', $userinfo["gender"], 31536000);
    
        $referer = $_G['siteurl'] . 'member.php?mod='.$_G['setting']['regname'].'&sqcaction=sqconnect&referer=' . urlencode($referer);
    
        dheader("Location:$referer");
        exit;
    }
}



function sqconnect_login($uid) {
    global $_G;

    if(!($member = getuserbyuid($uid, 1))) {
        return false;
    } else {
        if(isset($member['_inarchive'])) {
            C::t('common_member_archive')->move_to_master($member['uid']);
        }
    }

    require_once libfile('function/member');
    $cookietime = 1296000;
    setloginstatus($member, $cookietime);

    dsetcookie('sqqconnect_guestinfo_gender');
    dsetcookie('sqqconnect_guestinfo_nickname');
    dsetcookie('sqqconnect_guestinfo_faceimg');
    dsetcookie('sqqconnect_access_token');
    dsetcookie('sqqconnect_openid');
    dsetcookie('sqqconnect_state');
    return true;
}
