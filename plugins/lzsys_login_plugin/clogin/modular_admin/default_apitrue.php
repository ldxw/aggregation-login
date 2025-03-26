<?php

$api_config = $plugin->api_config();
$addons_config = $plugin->addons_config();
$asset_data = $plugin->asset_data();
$post = $plugin->ide->php->post();
$get = $plugin->ide->php->get();
$app_data = $plugin->ide->lzsys_system->app_data();
$hash = $app_data["app_hash"];
if ($get["act"] == "get_sms_code") {
	if ($post["tel"] == "") {
		exit("请输入手机号");
	}
	if ($post["sms_type"] == "") {
		$post["sms_type"] = "login_code";
	}
	default_get_time($plugin);
	$user["mobile"] = $post["tel"];
	$user["imgcode"] = $post["imgcode"];
	$data = default_login_code($plugin, $user, $post["sms_type"]);
	$data = $plugin->ide->lzsys_system->_array($data);
	if ($data["status"] == true) {
		$plugin->ide->php->session("vcode_time", time());
		exit("True");
	}
	exit($data["msg"]);
}
if ($get["act"] == "get_email_code") {
	if ($post["email"] == "") {
		exit("请输入邮箱账号");
	}
	email_off($post["email"]);
	if ($post["sms_type"] == "") {
		$post["sms_type"] = "login_code";
	}
	default_get_time($plugin);
	$user["email"] = $post["email"];
	$data = default_login_code_email($plugin, $user, $post["sms_type"]);
	$data = $plugin->ide->lzsys_system->_array($data);
	if ($data["status"] == true) {
		$plugin->ide->php->session("vcode_time", time());
		exit("True");
	}
	exit($data["msg"]);
}
if ($get["act"] == "login_a") {
	if ($post["tel"] == "") {
		exit("请输入手机号");
	}
	if ($post["vcode"] == "") {
		exit("请输入手机验证码");
	}
	$post = $plugin->ide->php->post();
	foreach ($post as $k => $v) {
		if ($v == "") {
			$post[$k] = "0";
		}
	}
	$tel = $post["tel"];
	$vcode = $post["vcode"];
	$pass = set_pass($post["pass"], $plugin);
	$reg_pass = set_pass($post["pass"], $plugin);
	$qq = $post["qq"];
	$email = $post["email"];
	if ($pass == "0" || $pass == "") {
		$post["pass"] = default_getpass(8);
		$pass = md5(md5(set_pass($post["pass"], $plugin) . $hash) . $hash);
	} else {
		$pass = md5(md5($pass . $hash) . $hash);
	}
	$smscode = $plugin->ide->php->session("vcode");
	$mobile_tel = $plugin->ide->php->session("mobile");
	if ($mobile_tel != $tel) {
		exit("验证码与手机号不匹配");
	}
	if ($vcode != $smscode) {
		$data = "null,'0','" . $plugin->ide->lzsys_system->get_ip() . "','" . $plugin->ide->lzsys_system->get_os() . "','" . $plugin->ide->lzsys_system->browse_info() . "'";
		$data .= ",'" . time() . "'";
		$data .= ",'2'";
		$data .= ",'{$tel}'";
		$data .= ",'" . $plugin->ide->lzsys_system->app_id() . "'";
		$plugin->ide->mysql->add_db($data, "user_log");
		$user_login = $plugin->ide->mysql->get_db("mobile='" . $tel . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'", "user");
		$user_login["mobile"] = $tel;
		default_sms($plugin, $user_login, "login_error");
		default_email($plugin, $user_login, "login_error");
		exit("手机验证码错误");
	}
	default_vcode_time($plugin);
	$create_time = time();
	$update_time = time();
	$user_login = $plugin->ide->mysql->get_db("mobile='" . $tel . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'", "user");
	if ($user_login) {
		if ($user_login["is_forbidden"] == "1") {
			exit("该账号被禁用");
		}
		if ($user_login["is_delete"] == "1") {
			exit("该账号被移到回收站");
		}
		$user_id = $user_login["user_id"];
		$plugin->ide->php->session("user_id", $user_id);
		$data = "null,'{$user_id}','" . $plugin->ide->lzsys_system->get_ip() . "','" . $plugin->ide->lzsys_system->get_os() . "','" . $plugin->ide->lzsys_system->browse_info() . "'";
		$data .= ",'" . time() . "'";
		$data .= ",'1'";
		$data .= ",'{$tel}'";
		$data .= ",'" . $plugin->ide->lzsys_system->app_id() . "'";
		$plugin->ide->mysql->add_db($data, "user_log");
		if ($_SESSION["openid"]) {
			$plugin->ide->mysql->up_db("open_id='" . $_SESSION["openid"] . "'", "user", "user_id='" . $user_id . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'");
		}
		unset($_SESSION["openid"]);
		if ($_SESSION["mpopen_id"]) {
			$plugin->ide->mysql->up_db("mpopen_id='" . $_SESSION["mpopen_id"] . "'", "user", "user_id='" . $user_id . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'");
		}
		unset($_SESSION["mpopen_id"]);
		default_sms($plugin, $user_login, "login");
		default_email($plugin, $user_login, "login");
		exit("True");
	} else {
		if (!$reg_pass) {
			exit("reg_pass");
		}
		if (strlen($reg_pass) < 6) {
			exit("登录密码不能低于6位数");
		}
		if ($post["email"] == "" || $post["email"] == "0") {
			exit("请输入邮箱账号");
		}
		if ($post["qq"] == "" || $post["qq"] == "0") {
			exit("请输入QQ账号");
		}
		email_off($post["email"]);
		if ($post["email"] != "" && $post["email"] != "0") {
			if ($plugin->ide->mysql->get_db("email='" . $post["email"] . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'", "user")) {
				exit("该邮箱已经被他人使用");
			}
		}
		$session = $plugin->ide->php->session();
		if ($session["url"] != "") {
			$reg_url = $session["url"];
		} else {
			$reg_url = "default-web";
		}
		$app_id = $plugin->ide->lzsys_system->app_id();
		$sql_query = "null,'0','0','0','{$reg_url}','{$post["tel"]}','{$post["tel"]}','0','0','0','0','0','0','0','0','0.00','0.00','0.00','0.00','0','0.00','0.00','23','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','" . $plugin->ide->lzsys_system->app_id() . "','{$create_time}','{$update_time}','0','{$pass}','{$post["tel"]}','{$post["email"]}','2','0','{$post["qq"]}'";
		$plugin->ide->mysql->add_db($sql_query, "user");
		$user_id = $plugin->ide->mysql->id();
		$plugin->ide->php->session("user_id", $user_id);
		$user_aff = $plugin->ide->mysql->get_db("user_id='" . $reg_url . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'", "user");
		if ($user_aff) {
			$time = time();
			$plugin->ide->mysql->add_db("null,'{$app_id}','" . $reg_url . "','{$user_id}','0','0','{$time}','客户注册','0'", "user_aff");
		}
		$data = "null,'{$user_id}','" . $plugin->ide->lzsys_system->get_ip() . "','" . $plugin->ide->lzsys_system->get_os() . "','" . $plugin->ide->lzsys_system->browse_info() . "'";
		$data .= ",'" . time() . "'";
		$data .= ",'1'";
		$data .= ",'{$tel}'";
		$data .= ",'" . $plugin->ide->lzsys_system->app_id() . "'";
		$plugin->ide->mysql->add_db($data, "user_log");
		if ($_SESSION["openid"]) {
			$plugin->ide->mysql->up_db("open_id='" . $_SESSION["openid"] . "'", "user", "user_id='" . $user_id . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'");
		}
		unset($_SESSION["openid"]);
		if ($_SESSION["mpopen_id"]) {
			$plugin->ide->mysql->up_db("mpopen_id='" . $_SESSION["mpopen_id"] . "'", "user", "user_id='" . $user_id . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'");
		}
		unset($_SESSION["mpopen_id"]);
		$user_data["mobile"] = $post["tel"];
		$user_data["email"] = $post["email"];
		$user_data["pass"] = set_pass($post["pass"], $plugin);
		default_sms($plugin, $user_data, "reg");
		default_email($plugin, $user_data, "reg");
		exit("True");
	}
}
if ($get["act"] == "login_b") {
	if ($post["tel"] == "") {
		exit("请输入账号");
	}
	$tel = $post["tel"];
	if (set_pass($post["pass"], $plugin) == "") {
		exit("请输入登录密码");
	}
	$pass = md5(md5(set_pass($post["pass"], $plugin) . $hash) . $hash);
	$user_login = $plugin->ide->mysql->get_db("(password='" . $pass . "' and username='" . $tel . "') or (password='" . $pass . "' and email='" . $tel . "') or (password='" . $pass . "' and mobile='" . $tel . "') and app_id='" . $plugin->ide->lzsys_system->app_id() . "'", "user");
	if ($user_login) {
		if ($user_login["is_forbidden"] == "1") {
			exit("该账号被禁用");
		}
		if ($user_login["is_delete"] == "1") {
			exit("该账号被移到回收站");
		}
		$user_id = $user_login["user_id"];
		$plugin->ide->php->session("user_id", $user_id);
		$data = "null,'{$user_id}','" . $plugin->ide->lzsys_system->get_ip() . "','" . $plugin->ide->lzsys_system->get_os() . "','" . $plugin->ide->lzsys_system->browse_info() . "'";
		$data .= ",'" . time() . "'";
		$data .= ",'1'";
		$data .= ",'{$tel}'";
		$data .= ",'" . $plugin->ide->lzsys_system->app_id() . "'";
		$plugin->ide->mysql->add_db($data, "user_log");
		if ($_SESSION["openid"]) {
			$plugin->ide->mysql->up_db("open_id='" . $_SESSION["openid"] . "'", "user", "user_id='" . $user_id . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'");
		}
		unset($_SESSION["openid"]);
		if ($_SESSION["mpopen_id"]) {
			$plugin->ide->mysql->up_db("mpopen_id='" . $_SESSION["mpopen_id"] . "'", "user", "user_id='" . $user_id . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'");
		}
		unset($_SESSION["mpopen_id"]);
		default_sms($plugin, $user_login, "login");
		default_email($plugin, $user_login, "login");
		exit("True");
	}
	$user_login = $plugin->ide->mysql->get_db("(username='" . $tel . "' or email='" . $tel . "' or mobile='" . $tel . "') and app_id='" . $plugin->ide->lzsys_system->app_id() . "'", "user");
	$data = "null,'0','" . $plugin->ide->lzsys_system->get_ip() . "','" . $plugin->ide->lzsys_system->get_os() . "','" . $plugin->ide->lzsys_system->browse_info() . "'";
	$data .= ",'" . time() . "'";
	$data .= ",'2'";
	$data .= ",'{$tel}'";
	$data .= ",'" . $plugin->ide->lzsys_system->app_id() . "'";
	$plugin->ide->mysql->add_db($data, "user_log");
	default_sms($plugin, $user_login, "login_error");
	default_email($plugin, $user_login, "login_error");
	exit("账号或密码错误");
}
if ($get["act"] == "user_pass") {
	if ($post["tel"] == "") {
		exit("请输入手机号");
	}
	if ($post["vcode"] == "") {
		exit("请输入手机验证码");
	}
	if ($post["pass"] == "") {
		exit("请输入新密码");
	}
	$post = $plugin->ide->php->post();
	foreach ($post as $k => $v) {
		if ($v == "") {
			$post[$k] = "0";
		}
	}
	$tel = $post["tel"];
	$vcode = $post["vcode"];
	$pass = set_pass($post["pass"], $plugin);
	if ($pass == "0" || $pass == "") {
		$post["pass"] = default_getpass(8);
		$pass = md5(md5(set_pass($post["pass"], $plugin) . $hash) . $hash);
	} else {
		$pass = md5(md5($pass . $hash) . $hash);
	}
	$smscode = $plugin->ide->php->session("vcode");
	if ($vcode != $smscode) {
		exit("手机验证码错误");
	}
	$mobile_tel = $plugin->ide->php->session("mobile");
	if ($mobile_tel != $tel) {
		exit("验证码与手机号不匹配");
	}
	default_vcode_time($plugin);
	$plugin->ide->mysql->up_db("password='" . $pass . "'", "user", "mobile='" . $tel . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'");
	exit("True");
}
if ($get["act"] == "login_c") {
	if ($post["email"] == "") {
		exit("请输入邮箱");
	}
	if ($post["ecode"] == "") {
		exit("请输入邮箱验证码");
	}
	$post = $plugin->ide->php->post();
	foreach ($post as $k => $v) {
		if ($v == "") {
			$post[$k] = "0";
		}
	}
	$email = $post["email"];
	$ecode = $post["ecode"];
	email_off($post["email"]);
	$email = $post["email"];
	if ($pass == "0" || $pass == "") {
		$post["pass"] = default_getpass(8);
		$pass = md5(md5(set_pass($post["pass"], $plugin) . $hash) . $hash);
	} else {
		$pass = md5(md5($pass . $hash) . $hash);
	}
	$emailcode = $plugin->ide->php->session("ecode");
	$email_a = $plugin->ide->php->session("email");
	if ($email_a != $email) {
		exit("验证码与邮箱不匹配");
	}
	if ($ecode != $emailcode) {
		$data = "null,'0','" . $plugin->ide->lzsys_system->get_ip() . "','" . $plugin->ide->lzsys_system->get_os() . "','" . $plugin->ide->lzsys_system->browse_info() . "'";
		$data .= ",'" . time() . "'";
		$data .= ",'2'";
		$data .= ",'{$email}'";
		$data .= ",'" . $plugin->ide->lzsys_system->app_id() . "'";
		$plugin->ide->mysql->add_db($data, "user_log");
		exit("邮件验证码错误");
	}
	default_vcode_time($plugin);
	$create_time = time();
	$update_time = time();
	$user_login = $plugin->ide->mysql->get_db("email='" . $email . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'", "user");
	if ($user_login) {
		if ($user_login["is_forbidden"] == "1") {
			exit("该账号被禁用");
		}
		if ($user_login["is_delete"] == "1") {
			exit("该账号被移到回收站");
		}
		$user_id = $user_login["user_id"];
		$plugin->ide->php->session("user_id", $user_id);
		$data = "null,'{$user_id}','" . $plugin->ide->lzsys_system->get_ip() . "','" . $plugin->ide->lzsys_system->get_os() . "','" . $plugin->ide->lzsys_system->browse_info() . "'";
		$data .= ",'" . time() . "'";
		$data .= ",'1'";
		$data .= ",'{$email}'";
		$data .= ",'" . $plugin->ide->lzsys_system->app_id() . "'";
		$plugin->ide->mysql->add_db($data, "user_log");
		if ($_SESSION["openid"]) {
			$plugin->ide->mysql->up_db("open_id='" . $_SESSION["openid"] . "'", "user", "user_id='" . $user_id . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'");
		}
		unset($_SESSION["openid"]);
		if ($_SESSION["mpopen_id"]) {
			$plugin->ide->mysql->up_db("mpopen_id='" . $_SESSION["mpopen_id"] . "'", "user", "user_id='" . $user_id . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'");
		}
		unset($_SESSION["mpopen_id"]);
		default_sms($plugin, $user_login, "login");
		default_email($plugin, $user_login, "login");
		exit("True");
	} else {
		exit("账号或密码错误");
	}
}
if ($get["act"] == "login_d") {
	if ($post["email"] == "") {
		exit("请输入邮箱账号");
	}
	if ($post["ecode"] == "") {
		exit("请输入邮箱验证码");
	}
	if ($post["tel"] == "") {
		exit("请输入手机号");
	}
	$post = $plugin->ide->php->post();
	foreach ($post as $k => $v) {
		if ($v == "") {
			$post[$k] = "0";
		}
	}
	$email = $post["email"];
	email_off($post["email"]);
	$vcode = $post["ecode"];
	$pass = set_pass($post["pass"], $plugin);
	$reg_pass = set_pass($post["pass"], $plugin);
	$qq = $post["qq"];
	$email = $post["email"];
	if ($pass == "0" || $pass == "") {
		$post["pass"] = default_getpass(8);
		$pass = md5(md5(set_pass($post["pass"], $plugin) . $hash) . $hash);
	} else {
		$pass = md5(md5($pass . $hash) . $hash);
	}
	$tel = $post["tel"];
	$ecode = $plugin->ide->php->session("ecode");
	$login_email = $plugin->ide->php->session("email");
	if ($login_email != $email) {
		exit("验证码与邮箱账号不匹配");
	}
	if ($vcode != $ecode) {
		$data = "null,'0','" . $plugin->ide->lzsys_system->get_ip() . "','" . $plugin->ide->lzsys_system->get_os() . "','" . $plugin->ide->lzsys_system->browse_info() . "'";
		$data .= ",'" . time() . "'";
		$data .= ",'2'";
		$data .= ",'{$email}'";
		$data .= ",'" . $plugin->ide->lzsys_system->app_id() . "'";
		$plugin->ide->mysql->add_db($data, "user_log");
		$user_login = $plugin->ide->mysql->get_db("email='" . $email . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'", "user");
		$user_login["mobile"] = $tel;
		$user_login["email"] = $post["email"];
		default_sms($plugin, $user_login, "login_error");
		default_email($plugin, $user_login, "login_error");
		exit("邮箱验证码错误");
	}
	default_vcode_time($plugin);
	$create_time = time();
	$update_time = time();
	$user_login = $plugin->ide->mysql->get_db("email='" . $email . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'", "user");
	if ($user_login) {
		if ($user_login["is_forbidden"] == "1") {
			exit("该账号被禁用");
		}
		if ($user_login["is_delete"] == "1") {
			exit("该账号被移到回收站");
		}
		$user_id = $user_login["user_id"];
		$plugin->ide->php->session("user_id", $user_id);
		$data = "null,'{$user_id}','" . $plugin->ide->lzsys_system->get_ip() . "','" . $plugin->ide->lzsys_system->get_os() . "','" . $plugin->ide->lzsys_system->browse_info() . "'";
		$data .= ",'" . time() . "'";
		$data .= ",'1'";
		$data .= ",'{$email}'";
		$data .= ",'" . $plugin->ide->lzsys_system->app_id() . "'";
		$plugin->ide->mysql->add_db($data, "user_log");
		if ($_SESSION["openid"]) {
			$plugin->ide->mysql->up_db("open_id='" . $_SESSION["openid"] . "'", "user", "user_id='" . $user_id . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'");
		}
		unset($_SESSION["openid"]);
		if ($_SESSION["mpopen_id"]) {
			$plugin->ide->mysql->up_db("mpopen_id='" . $_SESSION["mpopen_id"] . "'", "user", "user_id='" . $user_id . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'");
		}
		unset($_SESSION["mpopen_id"]);
		default_sms($plugin, $user_login, "login");
		default_email($plugin, $user_login, "login");
		exit("True");
	} else {
		if (!$reg_pass) {
			exit("reg_pass");
		}
		if (strlen($reg_pass) < 6) {
			exit("登录密码不能低于6位数");
		}
		if ($post["email"] == "" || $post["email"] == "0") {
			exit("请输入邮箱账号");
		}
		if ($post["qq"] == "" || $post["qq"] == "0") {
			exit("请输入QQ账号");
		}
		if ($post["email"] != "" && $post["email"] != "0") {
			if ($plugin->ide->mysql->get_db("email='" . $post["email"] . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'", "user")) {
				exit("该邮箱已经被注册");
			}
			if ($plugin->ide->mysql->get_db("mobile='" . $post["tel"] . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'", "user")) {
				exit("该手机已经被注册");
			}
		}
		$session = $plugin->ide->php->session();
		if ($session["url"] != "") {
			$reg_url = $session["url"];
		} else {
			$reg_url = "default-web";
		}
		$app_id = $plugin->ide->lzsys_system->app_id();
		$sql_query = "null,'0','0','0','{$reg_url}','{$post["email"]}','{$post["tel"]}','{$post["tel"]}','0','0','0','0','0','0','0','0.00','0.00','0.00','0.00','0','0.00','0.00','23','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','" . $plugin->ide->lzsys_system->app_id() . "','{$create_time}','{$update_time}','0','{$pass}','{$post["email"]}','{$post["email"]}','2','0','{$post["qq"]}'";
		$plugin->ide->mysql->add_db($sql_query, "user");
		$user_id = $plugin->ide->mysql->id();
		$plugin->ide->php->session("user_id", $user_id);
		$user_aff = $plugin->ide->mysql->get_db("user_id='" . $reg_url . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'", "user");
		if ($user_aff) {
			$time = time();
			$plugin->ide->mysql->add_db("null,'{$app_id}','" . $reg_url . "','{$user_id}','0','0','{$time}','客户注册','0'", "user_aff");
		}
		$data = "null,'{$user_id}','" . $plugin->ide->lzsys_system->get_ip() . "','" . $plugin->ide->lzsys_system->get_os() . "','" . $plugin->ide->lzsys_system->browse_info() . "'";
		$data .= ",'" . time() . "'";
		$data .= ",'1'";
		$data .= ",'{$email}'";
		$data .= ",'" . $plugin->ide->lzsys_system->app_id() . "'";
		$plugin->ide->mysql->add_db($data, "user_log");
		if ($_SESSION["openid"]) {
			$plugin->ide->mysql->up_db("open_id='" . $_SESSION["openid"] . "'", "user", "user_id='" . $user_id . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'");
		}
		unset($_SESSION["openid"]);
		if ($_SESSION["mpopen_id"]) {
			$plugin->ide->mysql->up_db("mpopen_id='" . $_SESSION["mpopen_id"] . "'", "user", "user_id='" . $user_id . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'");
		}
		unset($_SESSION["mpopen_id"]);
		$user_data["mobile"] = $post["tel"];
		$user_data["email"] = $post["email"];
		$user_data["pass"] = $reg_pass;
		default_sms($plugin, $user_data, "reg");
		default_email($plugin, $user_data, "reg");
		exit("True");
	}
}
function default_getpass($len, $chars = null)
{
	if (is_null($chars)) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	}
	mt_srand(10000000 * floatval(microtime()));
	$i = 0;
	$str = "";
	$lc = strlen($chars) - 1;
	while ($i < $len) {
		$str .= $chars[mt_rand(0, $lc)];
		$i++;
	}
	return $str;
}
function default_sms($plugin, $user, $type)
{
	$plugin->ide->lzsys_system->run_sms($plugin->ide(), $user, $type);
}
function set_pass($str, $plugin)
{
	if ($plugin->ide->lzsys_system->if_chek($str, "裙")) {
		exit("非法注册");
	}
	if ($plugin->ide->lzsys_system->if_chek($str, "加拿大")) {
		exit("非法注册");
	}
	if ($plugin->ide->lzsys_system->if_chek($str, "_977221")) {
		exit("非法注册");
	}
	return preg_replace("/[\x7f-\xff]+/", "", $str);
}
function default_email($plugin, $user, $type)
{
	$plugin->ide->lzsys_system->run_email($plugin->ide(), $user, $type);
}
function default_login_code($plugin, $user, $type)
{
	$user["code"] = $plugin->ide->lzsys_system->number(4);
	$user["code_time"] = "10分钟";
	$imgcode = $plugin->ide->php->session("imgcode");
	if (!$imgcode) {
		$imgcode = rand(1000, 5000) . "a";
	}
	if ($imgcode != "") {
		if ($imgcode != $user["imgcode"]) {
			exit("请先输入正确的图形验证码");
		}
	}
	$plugin->ide->php->session("vcode", $user["code"]);
	$plugin->ide->php->session("mobile", $user["mobile"]);
	return $plugin->ide->lzsys_system->run_sms($plugin->ide(), $user, $type);
}
function default_login_code_email($plugin, $user, $type)
{
	if (!$plugin->ide->mysql->get_db("email='" . $user["email"] . "' and app_id='" . $plugin->ide->lzsys_system->app_id() . "'", "user")) {
		if ($plugin->ide->php->get("acts") != "login_d") {
			exit("该邮箱未注册或未绑定本站");
		}
	}
	$user["code"] = $plugin->ide->lzsys_system->number(4);
	$user["code_time"] = "10分钟";
	$plugin->ide->php->session("ecode", $user["code"]);
	$plugin->ide->php->session("email", $user["email"]);
	return $plugin->ide->lzsys_system->run_email($plugin->ide(), $user, $type);
}
function default_get_time($plugin)
{
	if ($plugin->ide->php->session("vcode_time") != "") {
		$vcode_time = $plugin->ide->php->session("vcode_time");
		$now_time = time();
		$times = $now_time - $vcode_time;
		if ($times < 60) {
			exit("你发送过于频繁，请60秒后再试");
		}
	}
}
function email_off($str)
{
	$regex = "/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
	$result = preg_match($regex, $str);
	if (!$result) {
		exit("请输入正确的电子邮箱");
	}
	return $result;
}
function default_vcode_time($plugin)
{
	if ($plugin->ide->php->session("vcode_time") != "") {
		$vcode_time = $plugin->ide->php->session("vcode_time");
		$now_time = time();
		$times = $now_time - $vcode_time;
		if ($times > 600) {
			exit("验证码已过期");
		}
	}
}