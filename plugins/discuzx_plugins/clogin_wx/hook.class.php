<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once DISCUZ_ROOT . '/source/plugin/clogin_wx/class/Oauth.class.php';
require_once DISCUZ_ROOT . '/source/plugin/clogin_wx/function/function_base.php';

class plugin_clogin_wx {

    public function common() {
        global $_G, $swxconnect, $sectpl, $secqaacheck, $seccodecheck;
      
        $_G['cache']['plugin']['clogin_wx']['logintype'] = 'wx';
        
        $swxconnect = $_G['cache']['plugin']['clogin_wx'];
        include_once template('clogin_wx:module');

        if(!$_G['uid'] and $_G['cookie']['swxconnect_guestinfo_nickname']) {
            $_G['connectguest'] = 1;
            $_G['member']['username'] = $_G['cookie']['swxconnect_guestinfo_nickname'];
        }

        if(!$_G['cookie']['swxconnect_is_bind'] and $_G['uid']) {
            $connect_member = C::t('#clogin_wx#clogin_member_wxconnect')->fetch($_G['uid']);
            if($connect_member) {
                dsetcookie('swxconnect_is_bind', '1', 8640000);
            } else {
                dsetcookie('swxconnect_is_bind', '2', 8640000);
            }
        }
        if($_G['uid'] && in_array(CURSCRIPT,array('forum','portal')) && $swxconnect['forcebind']){
            dheader('location: home.php?mod=spacecp&ac=plugin&id=clogin_wx:bind');
            exit;
        }

        if($_G['basescript'] == 'member' and $_GET['mod'] == 'connect' and $_GET['ac'] == 'bind') {
            dheader('Location:member.php?mod='.$_G['setting']['regname'].'&sqcaction=swxconnect&bind=yes');
            exit;
        } elseif($_G['basescript'] == 'member' and $_GET['mod'] == 'connect') {
            dheader('Location:member.php?mod='.$_G['setting']['regname'].'&sqcaction=swxconnect');
            exit;
        }


        if($_GET['sqcaction'] == 'swxconnect') {
            $_G['setting']['regclosed'] = 1;
        }


    }


    function global_login_extra() {
        global $_G, $swxconnect, $login_url;
        if(!$swxconnect['on_glb']) {
            return '';
        }
        $login_url = $_G['siteurl'] . 'plugin.php?id=clogin_wx:login';
        return swxtpl_global_login_extra();
    }

    function global_usernav_extra1() {
        global $_G, $swxconnect;
        if(!$swxconnect['on_glb']) {
            return '';
        }
        if(!$_G['uid'] and !$_G['cookie']['swxconnect_guestinfo_nickname']) {
            return '';
        }
        if($_G['uid'] and $_G['cookie']['swxconnect_is_bind'] == '1') {
            return '';
        }

        return swxtpl_global_usernav_extra1();

    }


}

class plugin_clogin_wx_member extends plugin_clogin_wx {


    function logging_method() {

        if($_GET['sqcaction'] == 'swxconnect') {
            return '';
        }
        return swxtpl_login_bar();
    }

    function register_logging_method() {
        global $_G, $swxconnect;
        if(!$swxconnect['on_ln']) {
            return '';
        }
        if($swxconnect['on_default'] and !$_G['cookie']['swxconnect_guestinfo_nickname']) {
            header("Location:" . $_G['siteurl'] . "plugin.php?id=clogin_wx:login");
        }
        if($_GET['sqcaction'] == 'swxconnect') {
            return '';
        }
        return swxtpl_login_bar();
    }


    public function register_bottom() {
        global $_G, $swxconnect, $sectpl, $secqaacheck, $seccodecheck;
        if(!$swxconnect['on_rn']) {
            return '';
        }
        if($_GET['sqcaction'] != 'swxconnect' or $_G['uid'] or !$_G['cookie']['swxconnect_guestinfo_nickname'] and !$_GET['state']) {
            return '';
        }
        if(!$swxconnect['on_rsec']) {
            $secqaacheck = $seccodecheck = 0;
        }

        return swxtpl_register_bottom();
    }
}


class mobileplugin_clogin_wx {

    public function mobileplugin_clogin_wx() {

    }

    public function common() {
        global $_G, $swxconnect, $sectpl, $secqaacheck, $seccodecheck;
        $_G['cache']['plugin']['clogin_wx']['logintype'] = 'wx';
        $swxconnect = $_G['cache']['plugin']['clogin_wx'];
        if(!$_G['uid'] and $_G['cookie']['swxconnect_guestinfo_nickname']) {
            //$_G['connectguest'] = 1;
            //$_G['member']['username'] = $_G['cookie']['swxconnect_guestinfo_nickname'];
        }

        if($_GET['sqcaction'] == 'swxconnect') {
            $_G['setting']['regclosed'] = 1;
        }


        include_once template('clogin_wx:module');
    }


    function global_footer_mobile() {

        global $_G, $swxconnect, $sectpl, $secqaacheck, $seccodecheck;
        list($seccodecheck, $secqaacheck) = seccheck('register');
        if(!$swxconnect['on_rsec']) {
            $secqaacheck = $seccodecheck = 0;
        }



        if($swxconnect['on_default'] and !$_G['uid'] and CURMODULE == 'register' and !$_G['cookie']['swxconnect_guestinfo_nickname'] and !$_GET['state']) {
            header("Location:" . $_G['siteurl'] . "plugin.php?id=clogin_wx:login");
        }

        if($_G['uid'] and CURMODULE == 'space' and $_GET['do'] == 'profile' and $_GET['mycenter'] == 1) {
            if(!$swxconnect['on_mbind']) {
                return '';
            }
            return swxtpl_mobile_profilemenu();
        }
        if(!$_G['uid'] and CURMODULE == 'register' and $_GET['sqcaction'] == 'swxconnect') {
            return swxtpl_mobile_register();
        }

    }

}

class mobileplugin_clogin_wx_member extends mobileplugin_clogin_wx {
    public function logging_bottom_mobile() {
        global $_G, $swxconnect, $loginurl;

        if(!$swxconnect['on_mobile']) {
            return '';
        }


        $loginurl = $_G['siteurl'] . 'plugin.php?id=clogin_wx:login&referer=' . urlencode(dreferer());
        return swxtpl_mobile_login();
    }


}
