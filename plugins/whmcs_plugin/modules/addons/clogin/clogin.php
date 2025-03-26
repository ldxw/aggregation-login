<?php
use WHMCS\Database\Capsule;

function clogin_config() {
	$configarray = array(
		'name' 			=> '彩虹聚合登录',
		'description' 	=> '彩虹聚合登录可以让用户使用QQ、微信、支付宝、微博等方式快捷登录到WHMCS',
		'version' 		=> '1.0',
		'author' 		=> '<a href="https://u.cccyun.cc/" target="_blank">彩虹聚合登录</a>',
		'fields' 		=> []
	);
	
	$configarray['fields']['appurl'] = [
		'FriendlyName' 	=> '接口地址',
		'Type' 			=> 'text',
		'Size' 			=> '128',
		'Description' 	=> ''
	];

	$configarray['fields']['appid'] = [
		'FriendlyName' 	=> '应用APPID',
		'Type' 			=> 'text',
		'Size' 			=> '32',
		'Description' 	=> ''
	];

	$configarray['fields']['appkey'] = [
        "FriendlyName" 	=> "应用APPKEY",
        "Type" 			=> "text",
        "Size" 			=> "32",
        "Description" 	=> "",
	];

	$configarray['fields']['qq'] = [
        "FriendlyName" 	=> "",
        "Type" 			=> "yesno",
        "Description" 	=> "开启QQ快捷登录",
	];

	$configarray['fields']['wx'] = [
        "FriendlyName" 	=> "",
        "Type" 			=> "yesno",
        "Description" 	=> "开启微信快捷登录",
	];

	$configarray['fields']['alipay'] = [
        "FriendlyName" 	=> "",
        "Type" 			=> "yesno",
        "Description" 	=> "开启支付宝快捷登录",
	];

	$configarray['fields']['sina'] = [
        "FriendlyName" 	=> "",
        "Type" 			=> "yesno",
        "Description" 	=> "开启微博快捷登录",
	];
	
	return $configarray;
}

function clogin_activate() {
	try {
		if (!Capsule::schema()->hasTable('mod_clogin_user')) {
			Capsule::schema()->create('mod_clogin_user', function ($table) {
				$table->increments('id');
				$table->unsignedInteger('uid');
				$table->string('type',10);
				$table->string('openid',100);
				$table->string('nickname',150);
				$table->string('avatar',250);
				$table->dateTime('addtime');
				$table->index('uid', 'uid');
				$table->index(['openid','type'], 'openid');
			});
		}
	} catch (Exception $e) {
		return [
			'status' => 'error',
			'description' => '不能创建表 mod_clogin_user: ' . $e->getMessage()
		];
	}
	return [
		'status' => 'success',
		'description' => '模块激活成功. 点击 配置 对模块进行设置。'
	];
}

function clogin_deactivate() {

	Capsule::schema()->dropIfExists('mod_clogin_user');
	
	return [
		'status' => 'success',
		'description' => '模块卸载成功'
	];
}

function clogin_output($vars) {
    $systemurl = \WHMCS\Config\Setting::getValue('SystemURL');
    $modulelink = $vars['modulelink'];
    $result = '<div class="alert alert-info"><strong>插件使用说明：</strong><hr/><p>请在模板文件 clientareahome.tpl 和 login.tpl 中合适的地方加入 </p>
                        	<p>{$connect} 在登录页面是显示快捷登录组件，在用户中心页面是显示绑定与解绑组件，一个变量多用。</p>
                        	<p>{$avatar} 是头像，{$nickname} 是昵称，例如</p>
                        	<code style="margin-top: 10px;">{if $avatar}
&lt;span class="avatars"&gt;
&lt;img src="{$avatar}" alt="{$nickname}" /&gt;
{/if}
</code></div>';

    echo $result;
}