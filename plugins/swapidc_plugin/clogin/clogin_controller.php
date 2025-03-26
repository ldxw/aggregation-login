<?php
defined('SWAP_ROOT') or die('非法操作');
class clogin extends controller
{
    function config()
    {
        return array('swap_no_login' => array('connect' => '1', 'admin' => '1'), 'index' => '1', 'unbind' => '0');
    }
    function index()
    {
        $uid = session("uid");
		$L['变量']['login_qq'] = clogin_eva('开启QQ快捷登录');
		$L['变量']['login_wx'] = clogin_eva('开启微信快捷登录');
		$L['变量']['login_alipay'] = clogin_eva('开启支付宝快捷登录');
		$L['变量']['login_sina'] = clogin_eva('开启微博快捷登录');
		if($L['变量']['login_qq'] == 'on'){
			$L['变量']['qq_uid'] = get_query_val('第三方登录账号', 'openid', array('uid' => $uid, 'type' => 'qq'));
		}
		if($L['变量']['login_wx'] == 'on'){
			$L['变量']['wx_uid'] = get_query_val('第三方登录账号', 'openid', array('uid' => $uid, 'type' => 'wx'));
		}
		if($L['变量']['login_alipay'] == 'on'){
			$L['变量']['alipay_uid'] = get_query_val('第三方登录账号', 'openid', array('uid' => $uid, 'type' => 'alipay'));
		}
		if($L['变量']['login_sina'] == 'on'){
			$L['变量']['sina_uid'] = get_query_val('第三方登录账号', 'openid', array('uid' => $uid, 'type' => 'sina'));
		}
        TEMPLATE::assign('L', $L);
        TEMPLATE::display('index.tpl');
    }
	function connect()
    {
		$type = mac_url_get(1);
		$callback = (is_ssl() ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].'/index.php/plugin/clogin/connect/';
		$Oauth_config['apiurl'] = clogin_eva("聚合登录接口地址");
		$Oauth_config['appid'] = clogin_eva("聚合登录APPID");
		$Oauth_config['appkey'] = clogin_eva("聚合登录APPKEY");
		$Oauth_config['callback'] = $callback;
		if(empty($Oauth_config['apiurl']) || empty($Oauth_config['appid']) || empty($Oauth_config['appkey'])){
			redirect($this->cakurl() . '/index/login/?error='.'请先配置好聚合登录接口信息');
		}
		$Oauth = new Oauth($Oauth_config);
		if(_GET('code')){
			$arr=$Oauth->callback();
			if(isset($arr['code']) && $arr['code']==0){
				$type=$arr['type'];
				$openid=$arr['social_uid'];
				if($type == 'qq') $typename = 'QQ';
				elseif($type == 'wx') $typename = '微信';
				elseif($type == 'alipay') $typename = '支付宝';
				elseif($type == 'sina') $typename = '微博';
				/* 处理用户登录逻辑 */
				$uid = session('uid');
				if($uid){
					$this->conn->select('第三方登录账号', '*', "type='{$type}' and openid='{$openid}'");
					if ($this->conn->db_num_rows() == 0) {
						insert_query("第三方登录账号", array("uid" => $uid, "type" => $type, "openid" => $openid, "addtime" => date('Y-m-d H:i:s')));
						redirect($this->cakurl() . '/plugin/clogin/index/?success='.'绑定'.$typename.'成功，你已经可以通过'.$typename.'快捷登录到本站了！');
					} else {
						$s = $this->conn->fetch_array();
						if($s['uid'] == $uid){
							redirect($this->cakurl() . '/user/index/?success=' . '登陆成功!!欢迎回来!');
						}else{
							redirect($this->cakurl() . '/plugin/clogin/index/?warning='.'该'.$typename.'已经绑定其他用户，无法重复绑定');
						}
					}
				}else{
					$this->conn->select('第三方登录账号', '*', "type='{$type}' and openid='{$openid}'");
					if ($this->conn->db_num_rows() == 0) {
						session("Oauth_type", $type);
						session("Oauth_openid", $openid);
						redirect($this->cakurl() . '/index/login/?info='.'请绑定已有账号或注册新用户');
					} else {
						$s = $this->conn->fetch_array();
						session('uid', $s['uid']);
						if (function_exists('regkz_yhjl_log')){
							if (regkz_eva("开启用户登陆日志") == 'on') {
								regkz_yhjl_log($s['uid'], 'QQ登陆');
							}
						}
						if (_GET('referer') == '') {
							redirect($this->cakurl() . '/user/index/?success=' . '登陆成功!!欢迎回来!');
						} else {
							if (strstr(_GET('referer'), '?')) {
								redirect(_GET('referer') . '&success=' . '登陆成功!!欢迎回来!');
							} else {
								redirect(_GET('referer') . '?success=' . '登陆成功!!欢迎回来!');
							}
						}
					}
				}
			}elseif(isset($arr['code'])){
				exit('登录失败，返回错误原因：'.$arr['msg']);
			}else{
				exit('获取登录数据失败');
			}
		}else{
			$Oauth->login($type);
			$arr = $Oauth->login($type);
			if(isset($arr['code']) && $arr['code']==0){
				exit("<script language='javascript'>window.location.replace('{$arr['url']}')</script>");
			}elseif(isset($arr['code'])){
				exit('登录接口返回：'.$arr['msg']);
			}else{
				exit('获取登录地址失败');
			}
		}
    }
	function unbind()
    {
		$type = mac_url_get(1);
		if($type == 'qq') $typename = 'QQ';
		elseif($type == 'wx') $typename = '微信';
		elseif($type == 'alipay') $typename = '支付宝';
		elseif($type == 'sina') $typename = '微博';
		$uid = session('uid');
		$this->conn->select('第三方登录账号', '*', "uid='$uid' and type='{$type}'");
		if ($this->conn->db_num_rows() > 0) {
			$s = $this->conn->fetch_array();
			$this->conn->delete('第三方登录账号', "id='{$s['id']}'");
		}
		redirect($this->cakurl() . '/plugin/clogin/index/?success='.'解绑'.$typename.'成功！');
    }
    function admin()
    {
        need_admin();
        $OSWAP_6886e668575a783d9cc8f077ceb9d6e1 = "clogin";
        $OSWAP_9a27379461d34f4ee1cfae59ab2e4251 = "彩虹聚合登录插件";
        $OSWAP_a7ea0261cc34effc65a5870ef0c92893 = mac_url_get(1);
        if ($OSWAP_a7ea0261cc34effc65a5870ef0c92893 == "") {
            $OSWAP_a7ea0261cc34effc65a5870ef0c92893 = "list";
        }
        $id = "";
        $id = mac_url_get(2);
        $ok = "";
        $ok = mac_url_get(3);
        $OSWAP_1ea1dd3f425d411f8927efc1df70bbe1 = array(array('聚合登录接口地址', 'text', '必须以http://或https://开头，以/结尾'), array('聚合登录APPID', 'text'), array('聚合登录APPKEY', 'text'), array('开启QQ快捷登录', 'yesno'), array('开启微信快捷登录', 'yesno'), array('开启支付宝快捷登录', 'yesno'), array('开启微博快捷登录', 'yesno'));
        if ($OSWAP_a7ea0261cc34effc65a5870ef0c92893 == "editok") {
            foreach ($OSWAP_1ea1dd3f425d411f8927efc1df70bbe1 as $OSWAP_ede1d7ba65591dc126a99682ddad5150 => $OSWAP_00ebd91e1752b1185656fe7f1095ee9f) {
                plug_eva($OSWAP_6886e668575a783d9cc8f077ceb9d6e1, $OSWAP_00ebd91e1752b1185656fe7f1095ee9f[0], _POST($OSWAP_00ebd91e1752b1185656fe7f1095ee9f[0]));
            }
            die("修改完成ok");
        }
        AdminT::header($OSWAP_9a27379461d34f4ee1cfae59ab2e4251, '');
        AdminT::search();
        echo '<main class="page-content content-wrap">';
        AdminT::navbar();
        AdminT::sidebar();
        echo '<div class="page-inner">';
        AdminT::title($OSWAP_9a27379461d34f4ee1cfae59ab2e4251, '<li>网站设置</li>');
        ?>	
<div id="main-wrapper" class="container"><div class="row"><div class="col-md-12"><div class="panel panel-primary"><div class="panel-body"><form role="form" id="settingfrom" class="form-horizontal form-groups-bordered">

<?php 
        foreach ($OSWAP_1ea1dd3f425d411f8927efc1df70bbe1 as $OSWAP_ede1d7ba65591dc126a99682ddad5150 => $OSWAP_c32700024b9354565a6a008cd34fc421) {
            $OSWAP_c32700024b9354565a6a008cd34fc421[3] = plug_eva($OSWAP_6886e668575a783d9cc8f077ceb9d6e1, $OSWAP_c32700024b9354565a6a008cd34fc421[0]);
            if ($OSWAP_c32700024b9354565a6a008cd34fc421[1] == 'text') {
                $OSWAP_98bfb4d30d2536a9d8bd9a0ec2fe196e = "<input type=\"{$OSWAP_c32700024b9354565a6a008cd34fc421[1]}\" value=\"{$OSWAP_c32700024b9354565a6a008cd34fc421[3]}\" name=\"{$OSWAP_c32700024b9354565a6a008cd34fc421[0]}\" class=\"form-control\">";
            } elseif ($OSWAP_c32700024b9354565a6a008cd34fc421[1] == 'yesno') {
                if ($OSWAP_c32700024b9354565a6a008cd34fc421[3] == 'on') {
                    $OSWAP_c32700024b9354565a6a008cd34fc421['yes_yesno'] = 'checked="checked"';
                } else {
                    $OSWAP_c32700024b9354565a6a008cd34fc421['no_yesno'] = 'checked="checked"';
                }
                $OSWAP_98bfb4d30d2536a9d8bd9a0ec2fe196e = "<label><input type=\"radio\" name=\"{$OSWAP_c32700024b9354565a6a008cd34fc421[0]}\" value=\"on\"{$OSWAP_c32700024b9354565a6a008cd34fc421['yes_yesno']}/ >是 </label><label><input type=\"radio\" name=\"{$OSWAP_c32700024b9354565a6a008cd34fc421[0]}\" value=\"off\" {$OSWAP_c32700024b9354565a6a008cd34fc421['no_yesno']} />否</label> ";
            } else {
                $OSWAP_98bfb4d30d2536a9d8bd9a0ec2fe196e = "{$OSWAP_c32700024b9354565a6a008cd34fc421[1]} {$OSWAP_c32700024b9354565a6a008cd34fc421[3]}";
            }
            echo "<div class=\"form-group\"><label class=\"col-sm-3 control-label\">{$OSWAP_c32700024b9354565a6a008cd34fc421[0]}</label><div class=\"col-sm-5\">{$OSWAP_98bfb4d30d2536a9d8bd9a0ec2fe196e}{$OSWAP_c32700024b9354565a6a008cd34fc421[2]}</div></div>";
        }
        ?> <div class="form-group"><div class="col-sm-offset-3 col-sm-5"><a href="javascript:void(0)" onclick="$.post('/index.php/plugin/<?php echo $OSWAP_6886e668575a783d9cc8f077ceb9d6e1;?>/admin/editok/',$('#settingfrom').serialize(),function(data){if(data.match('ok')=='ok') swap_alert('success','保存成功',data); else swap_alert('error','保存失败',data);});" class="btn btn-success">保存更改</a></div></div></form></div></div></div></div></div>

	</div><?php 
        AdminT::page_footer();
        echo '</div></main>';
        AdminT::cd_nav();
        AdminT::pjs();
        echo '<script src="https://admin.down.swap.wang/assets/plugins/waypoints/jquery.waypoints.min.js"></script><script src="https://admin.down.swap.wang/assets/plugins/jquery-counterup/jquery.counterup.min.js"></script><script src="https://admin.down.swap.wang/assets/plugins/toastr/toastr.min.js"></script><script src="https://admin.down.swap.wang/assets/plugins/flot/jquery.flot.min.js"></script><script src="https://admin.down.swap.wang/assets/plugins/flot/jquery.flot.time.min.js"></script><script src="https://admin.down.swap.wang/assets/plugins/flot/jquery.flot.symbol.min.js"></script><script src="https://admin.down.swap.wang/assets/plugins/flot/jquery.flot.resize.min.js"></script><script src="https://admin.down.swap.wang/assets/plugins/flot/jquery.flot.tooltip.min.js"></script><script src="https://admin.down.swap.wang/assets/plugins/curvedlines/curvedLines.js"></script><script src="https://admin.down.swap.wang/assets/plugins/metrojs/MetroJs.min.js"></script><script src="https://admin.down.swap.wang/assets/plugins/morris/raphael.min.js"></script><script src="https://admin.down.swap.wang/assets/plugins/morris/morris.min.js"></script><script src="https://admin.down.swap.wang/assets/js/modern.min.js"></script><script src="https://admin.down.swap.wang/assets/plugins/datatables/js/jquery.dataTables.min.js"></script><script>var extable;$(document).ready(function() {extable=$(\'#example\').DataTable({"language":{"url":"https://cdn.datatables.net/plug-ins/e9421181788/i18n/Chinese.json"}});});</script></body></html>';
    }
}