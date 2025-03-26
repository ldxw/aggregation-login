<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class SQQOauth {

    private $apiurl;
    private $appid;
    private $appkey;
    const CALLBACK_URL = "plugin.php?id=clogin_qq";

    function __construct($sqqconnect) {
        if(!$sqqconnect['appid'] || !$sqqconnect['appkey']){
            showmessage('clogin_qq:connect_closed', dreferer());
        }
        $this->apiurl = $sqqconnect['appurl'].'connect.php';
        $this->appid = $sqqconnect['appid'];
        $this->appkey = $sqqconnect['appkey'];
    }

    public function login($type = 'qq') {
        global $_G;
        
        $state = md5(uniqid(rand(), true));
        dsetcookie('sqqconnect_state', $state, 31536000);

        $callback = $_G['siteurl'] . self::CALLBACK_URL . '&dreferers='.urlencode(dreferer());

        $keysArr = array(
            "act" => "login",
            "appid" => $this->appid,
            "appkey" => $this->appkey,
            "type" => $type,
            "redirect_uri" => $callback,
            "state" => $state
        );
        $login_url = $this->apiurl.'?'.http_build_query($keysArr);
        $response = $this->get_curl($login_url);
        $arr = json_decode($response,true);
        if (isset($arr['code']) && $arr['code'] == 0) {
            return $arr['url'];
        }elseif(isset($arr['code'])){
            showmessage('clogin_qq:connect_login_failed_code', dreferer(), array('msg'=>$arr['msg']));
        }else{
            showmessage('clogin_qq:connect_login_failed', dreferer());
        }
    }

    public function callback() {
        global $_G;

        $state = $_G['cookie']['sqqconnect_state'];

        if(!$state || $_GET['state'] != $state) {
            showmessage('The state does not match. You may be a victim of CSRF.');
        }

        $keysArr = array(
            "act" => "callback",
            "appid" => $this->appid,
            "appkey" => $this->appkey,
            "code" => $_GET['code']
        );

        $token_url = $this->apiurl.'?'.http_build_query($keysArr);
        $response = $this->get_curl($token_url);

        $arr = json_decode($response,true);

        if(isset($arr['code']) && $arr['code'] == 0){
            return $arr;
        }elseif(isset($arr['code'])){
            showmessage('clogin_qq:connect_callback_failed_code', dreferer(), array('msg'=>$arr['msg']));
        }else{
            showmessage('clogin_qq:connect_callback_failed', dreferer());
        }
    }

    private function get_curl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36");
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }
}
