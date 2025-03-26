<?php
namespace oauth\clogindingtalk;

use app\common\lib\Oauth;

class Clogindingtalk extends Oauth{
	const LOGIN_TYPE = "dingtalk";

	// 插件基础信息
    public $info = array(
        'name'        => 'Clogindingtalk', // 必填 插件标识(唯一)
        'title'       => '钉钉', // 必填 插件显示名称
        'description' => '彩虹聚合登录-钉钉登录', // 必填 插件功能描述
        'author'      => '彩虹聚合登录', // 必填 插件作者
        'version'     => '1.0.0',  // 必填 插件版本
        'help_url'    => 'https://u.cccyun.cc/', // 选填 申请链接
        'author_url'  => '', // 选填 作者链接
        'logo_url'    => 'dingtalk.svg', // 选填 图标地址(可以自定义支付图片地址)
    );

	//生成请求地址
	public function url($params){		
        $keysArr = array(
            "act" => "login",
			"appid" => $params['appid'],
			"appkey" => $params['appkey'],
			"type" => self::LOGIN_TYPE,
			"redirect_uri" => $params['callback'],
			"state" => $params['system_oauth_state']
        );
		$login_url = $params['appurl'].'connect.php?'.http_build_query($keysArr);
		$response = curl($login_url, null, 10, 'GET');
		$arr = json_decode($response['content'],true);
		if(isset($arr['code']) && $arr['code']==0){
			return $arr['url'];
		}else{
			exit('第三方登录请求失败：'.$arr['msg']);
		}
	}

	//回调地址
	public function callback($params){
        $keysArr = array(
			"act" => "callback",
			"appid" => $params['appid'],
			"appkey" => $params['appkey'],
			"code" => $params['code']
		);
		$token_url = $params['appurl'].'connect.php?'.http_build_query($keysArr);
		$response = curl($token_url, null, 10, 'GET');
		$arr = json_decode($response['content'],true);
		if(isset($arr['code']) && $arr['code']==0){
			return [
				'openid'=>$arr['social_uid'],
				'data'=>[
					'username'=>$arr['nickname'],
					'avatar'=>$arr['faceimg'],
				]
			];
		}else{
			return $arr['msg'];
		}
	}
}