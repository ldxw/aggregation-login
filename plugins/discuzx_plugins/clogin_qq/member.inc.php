<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

global $_G, $sqqconnect;
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

    $ctl_obj->extrafile = DISCUZ_ROOT . '/source/plugin/clogin_qq/member/member_logging.php';
    $ctl_obj->template = 'member/login';
    $ctl_obj->on_login();


} elseif($op == 'register') {


    if(!$sqqconnect['on_rsec']) {
        $_G['setting']['seccodestatus'] = 0;
    }

    
    $conopenid = $_G['cookie']['sqqconnect_openid'];
    if(C::t('#clogin_qq#clogin_member_qqconnect')->fetch_fields_by_openid($conopenid)) {
        showmessage('clogin_qq:connect_register_bind_error', $referer);
    }

    
    if(!$sqqconnect['on_sh']){$_G['setting']['regverify'] = 0;}
    $_G['setting']['newusergroupid'] = $sqqconnect['on_gr'] ? $sqqconnect['on_gr'] : $_G['setting']['newusergroupid'];
    $_G['setting']['regclosed'] =1;
    
    $ctl_obj = new register_ctl();

    $ctl_obj->setting = $_G['setting'];

    $ctl_obj->setting['ignorepassword'] = 1;
    $ctl_obj->setting['checkuinlimit'] = 1;
    $ctl_obj->setting['strongpw'] = 0;
    $ctl_obj->setting['pwlength'] = 0;

    $ctl_obj->extrafile = DISCUZ_ROOT . '/source/plugin/clogin_qq/member/member_register.php';
    $ctl_obj->template = 'member/register';
    $ctl_obj->on_register();


}
