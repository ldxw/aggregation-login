<?php
/**
 * Plugin Name: 彩虹聚合登录
 * Version: 1.0
 * Plugin URL: https://u.cccyun.cc/
 * Description: 彩虹聚合登录可以免申请实现QQ、微信等快捷登录
 * Author: 彩虹
 * Author Email: net909@163.com
 * Author URL: http://blog.cccyun.cn/
***/
!defined('EMLOG_ROOT') && exit('access deined!');
if (!defined('CLOGIN_ROOT')) {
	define('CLOGIN_ROOT',EMLOG_ROOT.'/content/plugins/clogin/');
}

function clogin_adminmenu(){
	echo '<div class="sidebarsubmenu" id="clogin"><a href="./plugin.php?plugin=clogin">彩虹聚合登录</a></div>';
}
addAction('adm_sidebar_ext', 'clogin_adminmenu');

function clogin_loginbtn(){
	require_once(CLOGIN_ROOT.'clogin_config.php');
	echo '<div style="text-align:center;margin-top:10px">';
	if ($clogin_config['openqq']==1) {
		echo '<a href="'.BLOG_URL.'?plugin=clogin&type=qq"><img src="/content/plugins/clogin/images/qq_login.png"></a>&nbsp&nbsp';
	}
	if ($clogin_config['openwx']==1) {
		echo '<a href="'.BLOG_URL.'?plugin=clogin&type=wx"><img src="/content/plugins/clogin/images/wechat_login.png"></a>&nbsp&nbsp';
	}
	echo '</div>';
}
addAction('login_ext', 'clogin_loginbtn');
