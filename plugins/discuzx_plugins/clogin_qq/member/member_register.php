<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
global $_G, $sqqconnect;

if(empty($_POST)) {


} else {

    $userdata = array();
    $userdata['avatarstatus'] = 0;
    $userdata['conisbind'] = 1;
    $openid = $_G['cookie']['sqqconnect_openid'];
    $access_token = $_G['cookie']['sqqconnect_access_token'];
    if(!$openid || !$access_token){
        showmessage('undefined_action');
    }

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
        'isregister' => '1',
    ));

    if(!function_exists('build_cache_userstats')) {
        require_once libfile('cache/userstats', 'function');
    }
    build_cache_userstats();


    if($_G['cookie']['sqqconnect_guestinfo_faceimg']) {

        require_once DISCUZ_ROOT . '/source/plugin/clogin_qq/class/upload_base.class.php';

        $upload_avatar = new upload_base();

        $home = $upload_avatar->get_home($uid);

        if(!is_dir(DISCUZ_ROOT . 'uc_server/data/avatar/' . $home)) {
            $upload_avatar->set_home($uid, DISCUZ_ROOT . 'uc_server/data/avatar/');
        }
        $avatartype = 'virtual';
        $bigavatarfile = DISCUZ_ROOT . 'uc_server/data/avatar/' . $upload_avatar->get_avatar($uid, 'big', $avatartype);
        $middleavatarfile = DISCUZ_ROOT . 'uc_server/data/avatar/' . $upload_avatar->get_avatar($uid, 'middle', $avatartype);
        $smallavatarfile = DISCUZ_ROOT . 'uc_server/data/avatar/' . $upload_avatar->get_avatar($uid, 'small', $avatartype);
        $bigavatar = $_G['cookie']['sqqconnect_guestinfo_faceimg'];

        sqqgetImage($bigavatar, $bigavatarfile);
        sqqgetImage($bigavatar, $middleavatarfile);
        sqqgetImage($bigavatar, $smallavatarfile);

    }


    dsetcookie('sqqconnect_guestinfo_gender');
    dsetcookie('sqqconnect_guestinfo_nickname');
    dsetcookie('sqqconnect_guestinfo_faceimg');
    dsetcookie('sqqconnect_guestinfo_location');
    dsetcookie('sqqconnect_access_token');
    dsetcookie('sqqconnect_openid');
    dsetcookie('sqqconnect_state');


    C::t('common_member')->update($uid, $userdata);

    if($_G['setting']['connect']['register_addcredit']) {
        $addcredit = array('extcredits' . $_G['setting']['connect']['register_rewardcredit'] => $_G['setting']['connect']['register_addcredit']);
    }
    C::t('common_member_count')->increase($uid, $addcredit);


}

?>