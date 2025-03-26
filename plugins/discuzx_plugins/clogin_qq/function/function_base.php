<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

function sqq_checkusername($username, $return = 0) {
    global $_G;
    $username = trim($username);
    $usernamelen = dstrlen($username);
    if($usernamelen < 3) {
        showmessage('profile_username_tooshort', '', array(), array('handle' => false));
    } elseif($usernamelen > 15) {
        showmessage('profile_username_toolong', '', array(), array('handle' => false));
    }

    if($username == $_G['username']) {
        $return && showmessage('succeed', '', array(), array('handle' => false));
        return $username;
    }


    loaducenter();
    $ucresult = uc_user_checkname($username);

    if($ucresult == -1) {
        showmessage('profile_username_illegal', '', array(), array('handle' => false));
    } elseif($ucresult == -2) {
        showmessage('profile_username_protect', '', array(), array('handle' => false));
    } elseif($ucresult == -3) {
        if(C::t('common_member')->fetch_by_username($username) || C::t('common_member_archive')->fetch_by_username($username)) {
            showmessage('register_check_found', '', array(), array('handle' => false));
        } else {
            showmessage('register_activation', '', array(), array('handle' => false));
        }
    }

    $censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')).')$/i';
    if($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
        showmessage('profile_username_protect', '', array(), array('handle' => false));
    }

    $return && showmessage('succeed', '', array(), array('handle' => false));
    return $username;
}

function sqqgetImage($url, $filename = '') {
    if(trim($url) == '') {
        return '';
    }
    if(strpos($url, '//') == 0) {
        $url = 'http:' . $url;
    }
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    $img = curl_exec($ch);
    curl_close($ch);

    file_put_contents($filename, $img);

    unset($img, $url);
}


function sqq_user_checkname($username) {    
    loaducenter();

    $ucresult = uc_user_checkname($username);
    if($ucresult < 0) {
       $username = 'qq' . random(9);
    }

    return $username;
}
