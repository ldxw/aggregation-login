<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once DISCUZ_ROOT . '/source/plugin/clogin_qq/class/Oauth.class.php';
require_once DISCUZ_ROOT . '/source/plugin/clogin_qq/function/function_base.php';

class plugin_clogin_qq {

    public function common() {
        global $_G, $sqqconnect, $sectpl, $secqaacheck, $seccodecheck;
      
        $_G['cache']['plugin']['clogin_qq']['logintype'] = 'qq';
        
        $sqqconnect = $_G['cache']['plugin']['clogin_qq'];
        include_once template('clogin_qq:module');

        if(!$_G['uid'] and $_G['cookie']['sqqconnect_guestinfo_nickname']) {
            $_G['connectguest'] = 1;
            $_G['member']['username'] = $_G['cookie']['sqqconnect_guestinfo_nickname'];
        }

        if(!$_G['cookie']['sqqconnect_is_bind'] and $_G['uid']) {
            $connect_member = C::t('#clogin_qq#clogin_member_qqconnect')->fetch($_G['uid']);
            if($connect_member) {
                dsetcookie('sqqconnect_is_bind', '1', 8640000);
            } else {
                dsetcookie('sqqconnect_is_bind', '2', 8640000);
            }
        }
        if($_G['uid'] && in_array(CURSCRIPT,array('forum','portal')) && $sqqconnect['forcebind']){
            dheader('location: home.php?mod=spacecp&ac=plugin&id=clogin_qq:bind');
            exit;
        }

        if($_G['basescript'] == 'member' and $_GET['mod'] == 'connect' and $_GET['ac'] == 'bind') {
            dheader('Location:member.php?mod='.$_G['setting']['regname'].'&sqcaction=sqconnect&bind=yes');
            exit;
        } elseif($_G['basescript'] == 'member' and $_GET['mod'] == 'connect') {
            dheader('Location:member.php?mod='.$_G['setting']['regname'].'&sqcaction=sqconnect');
            exit;
        }


        if($_GET['sqcaction'] == 'sqconnect') {
            $_G['setting']['regclosed'] = 1;
        }


    }


    function global_login_extra() {
        global $_G, $sqqconnect, $login_url;
        if(!$sqqconnect['on_glb']) {
            return '';
        }
        $login_url = $_G['siteurl'] . 'plugin.php?id=clogin_qq:login';
        return sqqtpl_global_login_extra();
    }

    function global_usernav_extra1() {
        global $_G, $sqqconnect;
        if(!$sqqconnect['on_glb']) {
            return '';
        }
        if(!$_G['uid'] and !$_G['cookie']['sqqconnect_guestinfo_nickname']) {
            return '';
        }
        if($_G['uid'] and $_G['cookie']['sqqconnect_is_bind'] == '1') {
            return '';
        }

        return sqqtpl_global_usernav_extra1();

    }


}

class plugin_clogin_qq_member extends plugin_clogin_qq {


    function logging_method() {

        if($_GET['sqcaction'] == 'sqconnect') {
            return '';
        }
        return sqqtpl_login_bar();
    }

    function register_logging_method() {
        global $_G, $sqqconnect;
        if(!$sqqconnect['on_ln']) {
            return '';
        }
        if($sqqconnect['on_default'] and !$_G['cookie']['sqqconnect_guestinfo_nickname']) {
            header("Location:" . $_G['siteurl'] . "plugin.php?id=clogin_qq:login");
        }
        if($_GET['sqcaction'] == 'sqconnect') {
            return '';
        }
        return sqqtpl_login_bar();
    }


    public function register_bottom() {
        global $_G, $sqqconnect, $sectpl, $secqaacheck, $seccodecheck;
        if(!$sqqconnect['on_rn']) {
            return '';
        }
        if($_GET['sqcaction'] != 'sqconnect' or $_G['uid'] or !$_G['cookie']['sqqconnect_guestinfo_nickname'] and !$_GET['state']) {
            return '';
        }
        if(!$sqqconnect['on_rsec']) {
            $secqaacheck = $seccodecheck = 0;
        }

        return sqqtpl_register_bottom();
    }
}


class mobileplugin_clogin_qq {

    public function mobileplugin_clogin_qq() {

    }

    public function common() {
        global $_G, $sqqconnect, $sectpl, $secqaacheck, $seccodecheck;
        $_G['cache']['plugin']['clogin_qq']['logintype'] = 'qq';
        $sqqconnect = $_G['cache']['plugin']['clogin_qq'];
        if(!$_G['uid'] and $_G['cookie']['sqqconnect_guestinfo_nickname']) {
            //$_G['connectguest'] = 1;
            //$_G['member']['username'] = $_G['cookie']['sqqconnect_guestinfo_nickname'];
        }

        if($_GET['sqcaction'] == 'sqconnect') {
            $_G['setting']['regclosed'] = 1;
        }


        include_once template('clogin_qq:module');
    }


    function global_footer_mobile() {

        global $_G, $sqqconnect, $sectpl, $secqaacheck, $seccodecheck;
        list($seccodecheck, $secqaacheck) = seccheck('register');
        if(!$sqqconnect['on_rsec']) {
            $secqaacheck = $seccodecheck = 0;
        }



        if($sqqconnect['on_default'] and !$_G['uid'] and CURMODULE == 'register' and !$_G['cookie']['sqqconnect_guestinfo_nickname'] and !$_GET['state']) {
            header("Location:" . $_G['siteurl'] . "plugin.php?id=clogin_qq:login");
        }

        if($_G['uid'] and CURMODULE == 'space' and $_GET['do'] == 'profile' and $_GET['mycenter'] == 1) {
            if(!$sqqconnect['on_mbind']) {
                return '';
            }
            return sqqtpl_mobile_profilemenu();
        }
        if(!$_G['uid'] and CURMODULE == 'register' and $_GET['sqcaction'] == 'sqconnect') {
            return sqqtpl_mobile_register();
        }

    }

}

class mobileplugin_clogin_qq_member extends mobileplugin_clogin_qq {
    public function logging_bottom_mobile() {
        global $_G, $sqqconnect, $loginurl;

        if(!$sqqconnect['on_mobile']) {
            return '';
        }


        $loginurl = $_G['siteurl'] . 'plugin.php?id=clogin_qq:login&referer=' . urlencode(dreferer());
        return sqqtpl_mobile_login();
    }


}
