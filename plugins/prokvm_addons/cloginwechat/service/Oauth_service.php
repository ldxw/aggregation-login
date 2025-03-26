<?php if (!defined('BASEPATH')) exit('No direct access allowed.');
class Oauth_service extends MY_Service
{
    private $appurl;
    private $appid;
    private $appkey;

    public function __construct()
    {
        parent::__construct();
        $this->appurl = get_addon_config('appurl','cloginwechat');
        $this->appid = get_addon_config('appid','cloginwechat');
        $this->appkey = get_addon_config('appkey','cloginwechat');
    }

    public function getAuthorizeUrl($referer_url)
    {
        $state = substr(md5(time()),0,6);
        $keysArr = array(
            "act" => "login",
			"appid" => $this->appid,
			"appkey" => $this->appkey,
			"type" => 'wx',
			"redirect_uri" => $referer_url,
			"state" => $state
        );
		$login_url = $this->appurl.'connect.php?'.http_build_query($keysArr);
		$response = http($login_url, 'get');
		$arr = json_decode($response,true);
		if(isset($arr['code']) && $arr['code']==0){
			return $arr['url'];
		}else{
            show_error('第三方登录请求失败：'.$arr['msg']);
		}
    }

    public function getUserInfo($params = [])
    {
        $params = !empty($params) ? $params : $this->input->get();
        if(!empty($params['code'])){
            $keysArr = array(
                "act" => "callback",
                "appid" => $this->appid,
			    "appkey" => $this->appkey,
                "code" => $params['code']
            );
            $token_url = $this->appurl.'connect.php?'.http_build_query($keysArr);
            $response = http($token_url, 'get');
            $arr = json_decode($response,true);
            if(isset($arr['code']) && $arr['code']==0){
                $oauth = [
                    'openid'=>$arr['social_uid'],
                    'nickname'=>$arr['nickname'],
                    'headimg'=>$arr['faceimg'],
                    'parame' => json_encode($arr)
                ];
                return response(['data'=>$oauth]);
            }else{
                show_error($arr['msg']);
            }
        }
        return [];
    }
}