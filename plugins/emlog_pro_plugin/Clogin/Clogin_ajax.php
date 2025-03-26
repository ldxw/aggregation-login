<?php
/**
 * ajax登录验证模块
 */
session_start();
require_once('../../../init.php');
//载入配置
$plugin_storage = Storage::getInstance('Clogin');
$OAuthConfig['apiurl'] = $plugin_storage->getValue('oauth_apiurl');
$OAuthConfig['appid'] = $plugin_storage->getValue('oauth_appid');
$OAuthConfig['appkey'] = $plugin_storage->getValue('oauth_appkey');

$a = Input::getStrVar('a');

if ($a == 'qq_login') {
    $OAuthConfig['callback'] = CLOGIN_URL . "/Clogin_ajax.php?a=callback_login";
    require_once("class/Oauth.class.php");
    $oauth = new Oauth($OAuthConfig);
    $result = $oauth->login('qq');
    if(isset($result['code']) && $result['code']==0){
        header('Location: '.$result['url']);
    }elseif(isset($arr['code'])){
		emMsg('登录接口返回：'.$arr['msg']);
	}else{
		emMsg('获取登录地址失败');
	}
}

if ($a == 'qq_bind') {
    $OAuthConfig['callback'] = CLOGIN_URL . "/Clogin_ajax.php?a=callback_bind";
    if (ISLOGIN) {
        require_once("class/Oauth.class.php");
        $oauth = new Oauth($OAuthConfig);
        $result = $oauth->login('qq');
        if(isset($result['code']) && $result['code']==0){
            header('Location: '.$result['url']);
        }elseif(isset($arr['code'])){
            emMsg('登录接口返回：'.$arr['msg']);
        }else{
            emMsg('获取登录地址失败');
        }
    } else {
        emMsg('请先登录再绑定QQ');
    }
}

if ($a == 'qq_unbind') {
    $DB = Database::getInstance();
    $sql = 'UPDATE `' . DB_PREFIX . 'user` SET `qq_login_openid`="" WHERE  `uid`=' . UID . ';';
    $r = $DB->query($sql);
    if ($r) {
        echo json_encode(array(
            'code' => '200',
            'data' => '解绑成功'
        ));
    } else {
        echo json_encode(array(
            'code' => '206',
            'data' => '解绑失败'
        ));
    }
    die;
}

// 绑定 QQ 回调
if ($a == 'callback_bind') {
    if (ISLOGIN) {
        if($_GET['state'] != $_SESSION['Oauth_state']){
            echo '<script>alert(\'state参数校验失败\');window.close();</script>';exit;
        }
        require_once("class/Oauth.class.php");
        global $user_cache;
        global $CACHE;
        $oauth = new Oauth($OAuthConfig);
        $result = $oauth->callback();
        if(isset($result['code']) && $result['code']==0){
            $openid=$result['social_uid'];
            $access_token=$result['access_token'];

            $DB = Database::getInstance();
            $data = $DB->once_fetch_array("SELECT * FROM " . DB_PREFIX . "user WHERE qq_login_openid = '" . $openid . "'");
            if (empty($data['qq_login_openid'])) {
                $User_Model = new User_Model();
                $User_Model->updateUser(array('qq_login_openid' => $openid), UID);
                echo '<script>window.close();window.opener.location.reload();</script>';
            } elseif($data['uid'] == UID) {
                echo '<script>window.close();window.opener.location.reload();</script>';
            } else {
                echo '<script>alert(\'该QQ已绑定其他账号\');window.close();</script>';
            }

        }elseif(isset($result['code'])){
            echo '<script>alert(\''.$result['msg'].'\');window.close();</script>';
        }else{
            echo '<script>alert(\'获取登录数据失败\');window.close();</script>';
        }
    }
}

//  登录回调
if ($a == 'callback_login') {
    require_once("class/Oauth.class.php");
    global $user_cache;
    global $CACHE;
    if($_GET['state'] != $_SESSION['Oauth_state']){
        emMsg('state参数校验失败', '/');
    }
    $oauth = new Oauth($OAuthConfig);
    $result = $oauth->callback();
    if(isset($result['code']) && $result['code']==0){
        $openid=$result['social_uid'];
        $access_token=$result['access_token'];

        $DB = Database::getInstance();
        $data = $DB->once_fetch_array("SELECT * FROM " . DB_PREFIX . "user WHERE qq_login_openid='$openid'");
        if ($data) {
            LoginAuth::setAuthCookie($data['username']);
            header('Location:' . BLOG_URL);
        } else {
            // 检查是否开启用户注册
            if (Option::get("is_signup") !== 'y') {
                emMsg('该QQ未绑定本站用户，请先到用户中心绑定', '/');
                exit;
            }

            $PHPASS = new PasswordHash(8, true);
            $password = $PHPASS->HashPassword(getRandStr(16));
            $username = getRandStr(8, false);
            $uid = Clogin_addUser($username, '', $password, 'writer', $openid);
            if (!$uid) {
                emMsg('用户注册失败', '/');
            }

            $CACHE = Cache::getInstance();
            $CACHE->updateCache(['sta', 'user']);

            LoginAuth::setAuthCookie($username, 1);
            header('Location:' . BLOG_URL);
        }
    }elseif(isset($result['code'])){
        emMsg('登录失败：'.$result['msg'], '/');
    }else{
        emMsg('登录失败：获取登录数据失败', '/');
    }
}

function Clogin_addUser($username, $mail, $password, $role, $openid) {
    $DB = Database::getInstance();
    $timestamp = time();
    $nickname = 'user-' . getRandStr(8, false);
    $sql = "insert into " . DB_PREFIX . "user (username,email,password,nickname,role,qq_login_openid,create_time,update_time) values('$username','$mail','$password','$nickname','$role','$openid',$timestamp,$timestamp)";
    $DB->query($sql);
    return $DB->insert_id();
}
