<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

global $_G, $swxconnect;
$op = !empty($_GET['op']) ? $_GET['op'] : '';
if(!in_array($op, array('bind', 'register'))) {
    showmessage('undefined_action');
}


require libfile('function/member');
require libfile('class/member');

$referer = dreferer();


if($op == 'bind') {


    $ctl_obj = new logging_ctl();
    $ctl_obj->setting = $_G['setting'];
    $_G['setting']['seccodestatus'] = 0;

    $ctl_obj->extrafile = DISCUZ_ROOT . '/source/plugin/clogin_wx/member/member_logging.php';
    $ctl_obj->template = 'member/login';
    $ctl_obj->on_login();


} elseif($op == 'register') {


    if(!$swxconnect['on_rsec']) {
        $_G['setting']['seccodestatus'] = 0;
    }

    
    $conopenid = $_G['cookie']['swxconnect_openid'];
    if(C::t('#clogin_wx#clogin_member_wxconnect')->fetch_fields_by_openid($conopenid)) {
        showmessage('clogin_wx:connect_register_bind_error', $referer);
    }

    
    if(!$swxconnect['on_sh']){$_G['setting']['regverify'] = 0;}
    $_G['setting']['newusergroupid'] = $swxconnect['on_gr'] ? $swxconnect['on_gr'] : $_G['setting']['newusergroupid'];
    $_G['setting']['regclosed'] =1;
    
    $ctl_obj = new register_ctl();

    $ctl_obj->setting = $_G['setting'];

    $ctl_obj->setting['ignorepassword'] = 1;
    $ctl_obj->setting['checkuinlimit'] = 1;
    $ctl_obj->setting['strongpw'] = 0;
    $ctl_obj->setting['pwlength'] = 0;

    $ctl_obj->extrafile = DISCUZ_ROOT . '/source/plugin/clogin_wx/member/member_register.php';
    $ctl_obj->template = 'member/register';
    $ctl_obj->on_register();


}
