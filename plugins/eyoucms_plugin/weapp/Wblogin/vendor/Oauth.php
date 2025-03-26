<?php

namespace weapp\Wblogin\vendor;
/**
 * 彩虹聚合登录SDK
 * 聚合登录请求类
 * 1.0
**/

class Oauth{
	private $apiurl;
	private $appid;
	private $appkey;
	private $callback;

	function __construct($apiurl, $appid, $appkey, $callback = null){
		$this->apiurl = $apiurl.'connect.php';
		$this->appid = $appid;
		$this->appkey = $appkey;
		$this->callback = $callback;
	}

	//获取登录跳转url
	public function login($type){

		//-------生成唯一随机串防CSRF攻击
		$state = md5(uniqid(rand(), TRUE));
		session('Oauth_state', $state);

		//-------构造请求参数列表
		$keysArr = array(
			"act" => "login",
			"appid" => $this->appid,
			"appkey" => $this->appkey,
			"type" => $type,
			"redirect_uri" => $this->callback,
			"state" => $state
		);
		$login_url = $this->apiurl.'?'.http_build_query($keysArr);
		$response = httpRequest($login_url);
		$arr = json_decode($response,true);
		return $arr;
	}

	//登录成功返回网站
	public function callback(){
		if($_GET['state'] != session('Oauth_state')){
			exit("The state does not match. You may be a victim of CSRF.");
		}
		//-------请求参数列表
		$keysArr = array(
			"act" => "callback",
			"appid" => $this->appid,
			"appkey" => $this->appkey,
			"code" => $_GET['code']
		);

		//------构造请求access_token的url
		$token_url = $this->apiurl.'?'.http_build_query($keysArr);
		$response = httpRequest($token_url);

		$arr = json_decode($response,true);
		return $arr;
	}

	//查询用户信息
	public function query($type, $social_uid){
		//-------请求参数列表
		$keysArr = array(
			"act" => "query",
			"appid" => $this->appid,
			"appkey" => $this->appkey,
			"type" => $type,
			"social_uid" => $social_uid
		);

		//------构造请求access_token的url
		$token_url = $this->apiurl.'?'.http_build_query($keysArr);
		$response = httpRequest($token_url);

		$arr = json_decode($response,true);
		return $arr;
	}
}
