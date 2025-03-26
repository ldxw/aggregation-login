<?php
!defined('EMLOG_ROOT') && exit('access deined!');
if (!defined('CLOGIN_ROOT')) {
	define('CLOGIN_ROOT',EMLOG_ROOT.'/content/plugins/clogin/');
}
function plugin_setting() {
	if (ROLE != ROLE_ADMIN) {
		emDirect("./plugin.php?plugin=clogin");
	}
	LoginAuth::checkToken();
	$apiurl = trim(addslashes($_POST['apiurl']));
	$appid = trim(addslashes($_POST['appid']));
	$appkey = trim(addslashes($_POST['appkey']));
	$openqq = intval($_POST['openqq']);
	$openwx = intval($_POST['openwx']);
	$wdir=str_replace("'", "\\'", str_replace('/', '\\/', $_POST['wh']));
	$data = '<?php $clogin_config=array (
  "apiurl" => "'.$apiurl.'",
  "appid" => "'.$appid.'",
  "appkey" => "'.$appkey.'",
  "openqq" => "'.$openqq.'",
  "openwx" => "'.$openwx.'",
) ?>';
	file_put_contents(CLOGIN_ROOT.'clogin_config.php', $data);
}

function plugin_setting_view() {
	global $user_cache;
	require_once(CLOGIN_ROOT.'clogin_config.php');
	$userinfo = LoginAuth::getUserDataByLogin($user_cache[UID]['name']);
	$bind_qq = !empty($userinfo['qq_openid']);
	$bind_wx = !empty($userinfo['wx_openid']);
	if (ROLE == ROLE_ADMIN){
?>
<div class="containertitle"><b>彩虹聚合登录 - 设置</b>
<?php if(isset($_GET['setting'])):?><span class="actived">插件设置完成</span><?php endif;?>
</div>
<div class="line"></div>
<form action="plugin.php?plugin=clogin&action=setting" method="post">
<div class="item_edit" style="margin-left:30px;">
	<li><label>接口地址：</label><br /><input style="width:300px;" class="input" value="<?php echo $clogin_config['apiurl']?>" name="apiurl" /> </li>
	<li><label>APPID：</label><br /><input style="width:300px;" class="input" value="<?php echo $clogin_config['appid']?>" name="appid" /> </li>
	<li><label>APPKEY：</label><br /><input style="width:300px;" class="input" value="<?php echo $clogin_config['appkey']?>" name="appkey" /> </li>
	<li><label>是否开启QQ登录：</label><input type="checkbox" style="vertical-align:middle;" value="1" name="openqq" id="openqq" <?php if($clogin_config['openqq']==1) echo 'checked';?>> </li>
	<li><label>是否开启微信登录：</label><input type="checkbox" style="vertical-align:middle;" value="1" name="openwx" id="openwx" <?php if($clogin_config['openwx']==1) echo 'checked';?>> </li>
	<li>
		<input name="token" id="token" value="<?php echo LoginAuth::genToken(); ?>" type="hidden" />
		<input type="submit" value="保存设置" class="button" />
	</li>
</div>
</form>
<br/><br/>
<?php }
if($clogin_config['openqq']==1||$clogin_config['openwx']==1){?>
<div class="containertitle"><b>第三方账号绑定</b></div><div class="line"></div>
<div class="item_edit" style="margin-left:30px;">
<?php if($clogin_config['openqq']==1){?>
<li><b>QQ登录：</b><?php echo $bind_qq?'<font color="green" title="'.$userinfo['qq_openid'].'">已绑定</font>  [<a href="../?plugin=clogin&action=unbind&type=qq" onclick="return confirm(\'解绑后将无法通过QQ一键登录，是否确定解绑？\');">解绑</a>]':'[<a href="../?plugin=clogin&type=qq">立即绑定</a>]';?><br/></li>
<?php } if($clogin_config['openwx']==1){?>
<li><b>微信登录：</b><?php echo $bind_wx?'<font color="green" title="'.$userinfo['wx_openid'].'">已绑定</font>  [<a href="../?plugin=clogin&action=unbind&type=wx" onclick="return confirm(\'解绑后将无法通过微信一键登录，是否确定解绑？\');">解绑</a>]':'[<a href="../?plugin=clogin&type=wx">立即绑定</a>]';?><br/></li>
<?php }?>
</div>
<?php
}
}