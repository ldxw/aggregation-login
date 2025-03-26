<?php

$api_config = $plugin->api_config();
$addons_config = $plugin->addons_config();
$asset_data = $plugin->asset_data();
$post = $plugin->ide->php->post();
$get = $plugin->ide->php->get();
$app_data = $plugin->ide->lzsys_system->app_data();
$app_id = $plugin->ide->lzsys_system->app_id();
if ($get["act"] == "qqlogin" || $get["act"] == "wxlogin") {
	$login_type = $get["act"] == "qqlogin" ? 'qq' : 'wx';
	if(empty($login_api["wx_token"]) || empty($login_api["qq_appid"]) || empty($login_api["qq_appkey"])){
		exit("<div style='display: flex;justify-content: center;align-items: center; height: 50%;line-height: 50%;font-size: 22px;'>请先配置彩虹聚合登录参数</div>");
	}
	$http_type = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" || isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && $_SERVER["HTTP_X_FORWARDED_PROTO"] == "https" ? "https://" : "http://";
	$website = $http_type . $_SERVER["HTTP_HOST"] . "/index.php/qqnotify/qqnotify";
	$state = substr(md5(time()),0,6);
	$keysArr = array(
		"act" => "login",
		"appid" => $login_api["qq_appid"],
		"appkey" => $login_api["qq_appkey"],
		"type" => $login_type,
		"redirect_uri" => $website,
		"state" => $state
	);
	$login_url = $login_api["wx_token"].'connect.php?'.http_build_query($keysArr);
	$response = $plugin->ide->lzsys_system->curl_get($login_url);
	$arr = json_decode($response,true);
	if(isset($arr['code']) && $arr['code']==0){
		$_SESSION['login_type'] = $login_type;
		exit("<script>top.location.href='{$arr['url']}'</script>");
		exit;
	}else{
		exit("<div style='display: flex;justify-content: center;align-items: center; height: 50%;line-height: 50%;font-size: 22px;'>第三方登录请求失败：{$arr['msg']}。请点击 <a href='/index.php/member/login'>返回网站</a> 重新登录</div>");
	}
}
elseif ($get["act"] == "qqnotifys") {
	$params = $plugin->ide->php->get();
	if(empty($params['code'])){
		exit("<div style='display: flex;justify-content: center;align-items: center; height: 50%;line-height: 50%;font-size: 22px;'>参数错误，请点击 <a href='/index.php/member/login'>返回网站</a> 重新登录</div>");
	}
	$login_type = $_SESSION['login_type'] ?? 'qq';
	$keysArr = array(
		"act" => "callback",
		"appid" => $login_api["qq_appid"],
		"appkey" => $login_api["qq_appkey"],
		"code" => $params['code']
	);
	$token_url = $login_api["wx_token"].'connect.php?'.http_build_query($keysArr);
	$response = $plugin->ide->lzsys_system->curl_get($token_url);
	$arr = json_decode($response,true);
	if(isset($arr['code']) && $arr['code']==0){
		$openid = $arr['social_uid'];
		if($login_type == 'wx'){
			$login_data = $plugin->ide->mysql->get_db("open_id='{$openid}' and app_id='{$app_id}'", "user");
			if (!$login_data["user_id"]) {
				$_SESSION["openid"] = $openid;
				exit("<div style='display: flex;justify-content: center;align-items: center; height: 50%;line-height: 50%;font-size: 22px;'>你还未绑定账号，请选择 <a href='/index.php/member/login'>绑定账号</a> 或  <a href='/index.php/member/login?login_type=sms'>注册新账号</a></div>");
			}
			$tel = "微信登录-" . $login_data["username"];
		}else{
			$login_data = $plugin->ide->mysql->get_db("mpopen_id='{$openid}' and app_id='{$app_id}'", "user");
			if (!$login_data["user_id"]) {
				$_SESSION["mpopen_id"] = $openid;
				exit("<div style='display: flex;justify-content: center;align-items: center; height: 50%;line-height: 50%;font-size: 22px;'>你还未绑定账号，请选择 <a href='/index.php/member/login'>绑定账号</a> 或  <a href='/index.php/member/login?login_type=sms'>注册新账号</a></div>");
			}
			$tel = "QQ登录-" . $login_data["username"];
		}
		$user_id = $login_data["user_id"];
		$data_sql = "null,'{$user_id}','" . $plugin->ide->lzsys_system->get_ip() . "','" . $plugin->ide->lzsys_system->get_os() . "','" . $plugin->ide->lzsys_system->browse_info() . "'";
		$data_sql .= ",'" . time() . "'";
		$data_sql .= ",'1'";
		$data_sql .= ",'{$tel}'";
		$data_sql .= ",'" . $plugin->ide->lzsys_system->app_id() . "'";
		$plugin->ide->mysql->add_db($data_sql, "user_log");
		default_sms($plugin, $login_data, "login");
		default_email($plugin, $login_data, "login");
		$_SESSION["user_id"] = $login_data["user_id"];
		header("Location: /index.php/user/index");
		exit;
	}else{
		exit("<div style='display: flex;justify-content: center;align-items: center; height: 50%;line-height: 50%;font-size: 22px;'>{$arr['msg']}。请点击 <a href='/index.php/member/login'>返回网站</a> 重新登录</div>");
	}
}
function default_sms($plugin, $user, $type)
{
	$plugin->ide->lzsys_system->run_sms($plugin->ide(), $user, $type);
}
function default_email($plugin, $user, $type)
{
	$plugin->ide->lzsys_system->run_email($plugin->ide(), $user, $type);
}