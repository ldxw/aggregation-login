<?php
defined('SWAP_ROOT') or die('非法操作');
function clogin_config()
{
    $config['swap_plug'] = '彩虹聚合登录';
    $config['swap_plug_version'] = '1.0';
    $config['swap_plug_explain'] = "彩虹聚合登录是彩虹旗下的社会化账号聚合登录系统，让网站的最终用户可以一站式选择使用包括微信、微博、QQ、百度等多种社会化帐号登录该站点。简化用户注册登录过程、改善用户浏览站点的体验、迅速提高网站注册量和用户数据量。";
    $config['swap_plug_author'] = '彩虹';
    $config['swap_plug_website'] = 'https://u.cccyun.cc/';
    return $config;
}
function clogin_eva($configvalue, $ne = null)
{
    if ($ne !== null || !empty($ne)) {
        plug_eva('clogin', $configvalue, $ne);
    }
    $nr = plug_eva('clogin', $configvalue);
    return $nr;
}
function clogin_admin_list(&$admin_list)
{
    $admin_list[] = array('name' => '彩虹聚合登录', 'link' => ''.WebStatic().'/plugin/clogin/admin/');
}
function clogin_user_list()
{
    echo '<li><a href="'.WebStatic().'/plugin/clogin/index/">第三方登录</a></li>';
}
function clogin_login_success($d)
{
    $uid = $d['uid'];
    if ($uid == '') {
        return '';
    }
	if(session("Oauth_openid") && session("Oauth_type")){
		$type = session("Oauth_type");
		$openid = session("Oauth_openid");
		insert_query("第三方登录账号", array("uid" => $uid, "type" => $type, "openid" => $openid, "addtime" => date('Y-m-d H:i:s')));
		session("Oauth_openid", null);
		session("Oauth_type", null);
	}
}
function clogin_login(){
	$login_qq = clogin_eva('开启QQ快捷登录');
	$login_wx = clogin_eva('开启微信快捷登录');
	$login_alipay = clogin_eva('开启支付宝快捷登录');
	$login_sina = clogin_eva('开启微博快捷登录');
	if($login_qq!='on' && $login_wx!='on' && $login_alipay!='on' && $login_sina!='on') return;
	echo '<style>.clogin{text-align:center;color:#a1a1a1}.clogin a{padding:10px}.clogin img{width:36px}</style>';
	echo '<div class="clogin">使用第三方登录<hr/>';
	if($login_qq=='on'){
		echo '<a href="'.WebStatic().'/plugin/clogin/connect/qq/" title="QQ快捷登录"><img src="/swap_mac/swap_plugins/clogin/icon/qq.png"></a>';
	}
	if($login_wx=='on'){
		echo '<a href="'.WebStatic().'/plugin/clogin/connect/wx/" title="微信快捷登录"><img src="/swap_mac/swap_plugins/clogin/icon/wx.png"></a>';
	}
	if($login_alipay=='on'){
		echo '<a href="'.WebStatic().'/plugin/clogin/connect/alipay/" title="支付宝快捷登录"><img src="/swap_mac/swap_plugins/clogin/icon/alipay.png"></a>';
	}
	if($login_sina=='on'){
		echo '<a href="'.WebStatic().'/plugin/clogin/connect/sina/" title="新浪微博快捷登录"><img src="/swap_mac/swap_plugins/clogin/icon/sina.png"></a>';
	}
	echo '</div>';
}
@(include_once SWAP_PLUGINS . 'clogin' . SWAP_DIR_END . 'lib' . SWAP_DIR_END . 'Oauth.class.php');
add_swap_plug('管理员菜单列表','clogin_admin_list');
add_swap_plug('用户页面列表','clogin_user_list');
add_swap_plug('登陆成功','clogin_login_success');
add_swap_plug('登入页底部','clogin_login');