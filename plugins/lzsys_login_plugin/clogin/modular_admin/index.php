<?php


$api_config = $plugin->api_config();
$addons_config = $plugin->addons_config();
$asset_data = $plugin->asset_data();
$post = $plugin->ide->php->post();
$get = $plugin->ide->php->get();
$app_data = $plugin->ide->lzsys_system->app_data();
$app_id = $plugin->ide->lzsys_system->app_id();
if ($get["act"] == "save") {
	$wx_token = $post["wx_token"];
	$qq_appid = $post["qq_appid"];
	$qq_appkey = $post["qq_appkey"];
	$qq_status = $post["qq_status"];
	$wx_status = $post["wx_status"];
	if (!$login_api) {
		$plugin->ide->mysql->add_db("null,'1','{$qq_appid}','{$qq_appkey}','{$qq_status}','','','{$wx_token}','{$wx_status}','0','0','{$app_id}'", "login_default");
		exit("True");
	} else {
		$up_sql = "qq_appid='" . $qq_appid . "'";
		$up_sql .= ",qq_appkey='" . $qq_appkey . "'";
		$up_sql .= ",qq_status='" . $qq_status . "'";
		$up_sql .= ",wx_token='" . $wx_token . "'";
		$up_sql .= ",wx_status='" . $wx_status . "'";
		$plugin->ide->mysql->up_db($up_sql, "login_default", "app_id='{$app_id}'");
		exit("True");
	}
}