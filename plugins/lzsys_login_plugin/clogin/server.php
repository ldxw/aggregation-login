<?php

//error_reporting(E_ALL);
if ($addons_config["path"] != "clogin") {
	error();
}
function clogin_status_vhost($plugin)
{
	$status = ["status" => true, "msg" => "接口通讯成功", "data" => []];
	return $plugin->system("status", $plugin->ide->lzsys_system->json($status));
}
function clogin_admin_panel($plugin)
{
	$asset_data = $plugin->asset_data();
	$api_config = $plugin->api_config();
	$addons_config = $plugin->addons_config();
	$get = $plugin->ide->php->get();
	if ($get["view_file"] == "") {
		$url = "?view_file=index.html";
		header("location: {$url}");
		$get["view_file"] = "index.html";
	}
	if (!file_exists($plugin->ide->views->template_dir[0] . $get["view_file"])) {
		$plugin->views->display("notice.html");
		exit;
	}
	if ($plugin->ide->mysql->R($plugin->ide->mysql->M("SHOW TABLES LIKE  'lzsys_login_default'")) != 1) {
		$plugin->ide->mysql->M("CREATE TABLE `lzsys_login_default` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_id` int(11) DEFAULT NULL COMMENT '该登录插件只有一个渠道，所以这里渠道ID为1',
  `qq_appid` varchar(255) DEFAULT NULL COMMENT 'QQ互联APPID',
  `qq_appkey` text,
  `qq_status` int(11) DEFAULT NULL COMMENT 'QQ登录状态 1= 开启 2=关闭',
  `wx_appid` varchar(255) DEFAULT NULL COMMENT '微信开发者ID',
  `wx_appkey` text,
  `wx_token` varchar(255) DEFAULT NULL COMMENT '令牌Token',
  `wx_status` int(11) DEFAULT NULL COMMENT '微信登录状态 1= 开启 2=关闭',
  `stoptime` int(11) DEFAULT NULL COMMENT 'assess_token',
  `access_token` text,
  `app_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;");
	}
	$login_api = $plugin->ide->mysql->get_db("app_id='" . $plugin->ide->lzsys_system->app_id() . "'", "login_default");
	$modular = "modular_admin/" . str_replace(".html", ".php", $get["view_file"]);
	$plugin->ide->views->assign("login_api", $login_api, true);
	require $modular;
	$plugin->ide->views->assign("plugins_config", $plugins_config, true);
	$plugin->ide->views->assign("addons_config", $addons_config, true);
	$plugin->ide->views->assign("api_config", $api_config, true);
	$plugin->ide->views->assign("asset_data", $asset_data, true);
	$plugin->views->display($get["view_file"]);
	exit;
}