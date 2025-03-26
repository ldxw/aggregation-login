<?php
namespace oauth\cloginweixin;

class cloginweixin{
	const LOGIN_TYPE = "wx";

	function __construct(){
		if(!session_id()) session_start();
    }
	
	//插件信息
	public function meta(){
		return [
			'name'        => '微信登录',
			'description' => '彩虹聚合登录-微信登录',
			'author'      => '彩虹聚合登录',
			'logo_url'=> 'weixin.svg',
		];
	}
	//插件接口配置信息
	public function config(){
		return [
			'接口网址'=> [
				'type' => 'text',
				'name' => 'appurl',
				'desc' => '接口网址以http://开头，以/结尾'
			],
			'应用APPID'=> [
				'type' => 'text',
				'name' => 'appid',
				'desc' => '申请应用之后的APPID'
			],
			'应用APPKEY'=> [
				'type' => 'text',
				'name' => 'appkey',
				'desc' => '申请应用之后的APPKEY'
			],
		];
	}

	//生成请求地址
	public function url($params){		
        //-------生成唯一随机串防CSRF攻击
        $state = md5(uniqid(rand(), TRUE));
		$_SESSION['Oauth_state']=$state;
		
        //-------构造请求参数列表
        $keysArr = array(
            "act" => "login",
			"appid" => $params['appid'],
			"appkey" => $params['appkey'],
			"type" => self::LOGIN_TYPE,
			"redirect_uri" => $params['callback'],
			"state" => $state
        );
		$login_url = $params['appurl'].'connect.php?'.http_build_query($keysArr);
		$response = $this->get_curl($login_url);
		$arr = json_decode($response,true);
		if(isset($arr['code']) && $arr['code']==0){
			return $arr['url'];
		}else{
			exit('第三方登录请求失败：'.$arr['msg']);
		}
	}

	//回调地址
	public function callback($params){
		//判断state
		if($_SESSION['Oauth_state']!=$params['state'] || empty($params['code'])){
			return 'error';
		}
		//获取 access_token 
        $keysArr = array(
			"act" => "callback",
			"appid" => $params['appid'],
			"appkey" => $params['appkey'],
			"code" => $params['code']
		);
		$token_url = $params['appurl'].'connect.php?'.http_build_query($keysArr);
		$response = $this->get_curl($token_url);
		$arr = json_decode($response,true);
		if(isset($arr['code']) && $arr['code']==0){
			$callback=[
				'openid'=>$arr['social_uid'],
				'data'=>[
					'username'=>$arr['nickname'],
					'sex'=>$arr['gender']=='女'?2:1,
					'avatar'=>$arr['faceimg'],
				]
			];
			unset($_SESSION['Oauth_state']);
			return $callback;
		}else{
			return $arr['msg'];
		}
	}

	private function get_curl($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$httpheader[] = "Accept: */*";
		$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
		$httpheader[] = "Connection: close";
		curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; U; Android 4.0.4; es-mx; HTC_One_X Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$ret = curl_exec($ch);
		curl_close($ch);
		return $ret;
	}
}