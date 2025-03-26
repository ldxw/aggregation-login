<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once DISCUZ_ROOT . '/source/plugin/clogin_wx/function/function_base.php';

if(!$_G['uid']) {
    showmessage('not_loggedin', null, array(), array('login' => 1));
}
loaducenter();
$pluginop = !empty($_GET['pluginop']) ? $_GET['pluginop'] : 'unbind';
if(!in_array($pluginop, array(
    'unbind',
    'bind',
    'new','reset'))) {
    showmessage('undefined_action');
}

$connect_member = C::t('#clogin_wx#clogin_member_wxconnect')->fetch($_G['uid']);


if(submitcheck('connectsubmit') and $pluginop == 'reset') {
    $newusername = swx_checkusername($_GET['newusername']);
    if(strlen($_GET['newpassword1']) < 6) {
        showmessage('profile_password_tooshort', '', array('pwlength' => 6));
    }

    if($_G['setting']['strongpw']) {
        $strongpw_str = array();
        if(in_array(1, $_G['setting']['strongpw']) && !preg_match("/\d+/", $_GET['newpassword1'])) {
            $strongpw_str[] = lang('member/template', 'strongpw_1');
        }
        if(in_array(2, $_G['setting']['strongpw']) && !preg_match("/[a-z]+/", $_GET['newpassword1'])) {
            $strongpw_str[] = lang('member/template', 'strongpw_2');
        }
        if(in_array(3, $_G['setting']['strongpw']) && !preg_match("/[A-Z]+/", $_GET['newpassword1'])) {
            $strongpw_str[] = lang('member/template', 'strongpw_3');
        }
        if(in_array(4, $_G['setting']['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $_GET['newpassword1'])) {
            $strongpw_str[] = lang('member/template', 'strongpw_4');
        }
        if($strongpw_str) {
            showmessage(lang('member/template', 'password_weak') . implode(',', $strongpw_str));
        }
    }
    if($_GET['newpassword1'] !== $_GET['newpassword2']) {
        showmessage('profile_passwd_notmatch');
    }
    if(!$_GET['newpassword1'] || $_GET['newpassword1'] != addslashes($_GET['newpassword1'])) {
        showmessage('profile_passwd_illegal');
    }

    require_once libfile('function/member');
    if($swxconnect['reset_email']){
        $email = $_POST['newemail'];
        checkemail($email);
    }else{
        $email = $_G['member']['email'];
    }

    uc_user_edit(addslashes($_G['member']['username']), null, $_GET['newpassword1'], $email, 1);
    //C::t('common_member')->update($_G['uid'], array('password' => md5(random(10))));


    DB::query('UPDATE %t SET username = %s , email = %s WHERE uid = %d', array('common_member',$newusername,$email,$_G['member']['uid']));
    DB::query('UPDATE '.UC_DBTABLEPRE.'members SET username = %s , email = %s WHERE uid = %d',array($newusername,$email,$_G['member']['uid']));
    C::t('common_member')->update_cache($_G['uid'], array('username' => $newusername,'email'=>$email));
    C::t('#clogin_wx#clogin_member_wxconnect')->update($connect_member['uid'], array('isreset' => 1, 'isregister' => 0));


    showmessage('clogin_wx:connect_config_success',dreferer());
}else if(submitcheck('connectsubmit') and $pluginop == 'unbind'){

    if($connect_member['isreset'] == 0){
        showmessage('clogin_wx:connect_bind_need_password');
    }

    if($connect_member['isregister'] == 1){
        if($_GET['newpassword1'] !== $_GET['newpassword2']) {
            showmessage('profile_passwd_notmatch', $referer);
        }
        if(!$_GET['newpassword1'] || $_GET['newpassword1'] != addslashes($_GET['newpassword1'])) {
            showmessage('profile_passwd_illegal', $referer);
        }
    
        C::t('#clogin_wx#clogin_member_wxconnect')->delete($_G['uid']);
        C::t('common_member')->update($_G['uid'], array('conisbind' => 0));

        loaducenter();
        uc_user_edit(addslashes($_G['member']['username']), null, $_GET['newpassword1'], null, 1);
    }else{
        C::t('#clogin_wx#clogin_member_wxconnect')->delete($_G['uid']);
        C::t('common_member')->update($_G['uid'], array('conisbind' => 0));
    }

    dsetcookie('swxconnect_is_bind', '2', 8640000);

    showmessage('clogin_wx:connect_config_unbind_success',dreferer());
} else if($pluginop == 'bind') {
    require_once DISCUZ_ROOT . '/source/plugin/clogin_wx/class/Oauth.class.php';

    global $_G, $swxconnect;

    $oauth = new SWXOauth($swxconnect);

    $loginurl = $oauth->login($swxconnect['logintype']);

    header("Location:$loginurl");
} else {
    if(defined('IN_MOBILE')) {
        global $_G, $swxconnect;
        include_once template('clogin_wx:bind');
        exit();
    }

    if(CURMODULE != 'spacecp') {
        header("Location:$_G[siteurl]");
    }
}

?>