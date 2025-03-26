<div class="col-md-6 col-sm-offset-3" style="margin-top: 30px;">
<div class="panel panel-default">
	<div class="panel-heading font-bold">
		<i class="fa fa-user-circle-o fa-fw"></i>&nbsp;&nbsp;第三方登录账号绑定
	</div>
	<div class="panel-body">
	<div class="list-group">
		{if $L['变量']['login_qq']=='on'}<div class="list-group-item"><img src="/swap_mac/swap_plugins/clogin/icon/qq.png" width="30px">&nbsp;&nbsp;QQ登录{if $L['变量']['qq_uid']}<a href="/index.php/plugin/clogin/unbind/qq/" onclick="return confirm('解绑后将无法通过QQ一键登录，是否确定解绑？');" class="btn btn-sm btn-danger pull-right">解绑</a>{else}<a href="/index.php/plugin/clogin/connect/qq/" class="btn btn-sm btn-success pull-right">绑定</a>{/if}</div>{/if}

		{if $L['变量']['login_wx']=='on'}<div class="list-group-item"><img src="/swap_mac/swap_plugins/clogin/icon/wx.png" width="30px">&nbsp;&nbsp;微信登录{if $L['变量']['wx_uid']}<a href="/index.php/plugin/clogin/unbind/wx/" onclick="return confirm('解绑后将无法通过微信一键登录，是否确定解绑？');" class="btn btn-sm btn-danger pull-right">解绑</a>{else}<a href="/index.php/plugin/clogin/connect/wx/" class="btn btn-sm btn-success pull-right">绑定</a>{/if}</div>{/if}

		{if $L['变量']['login_alipay']=='on'}<div class="list-group-item"><img src="/swap_mac/swap_plugins/clogin/icon/alipay.png" width="30px">&nbsp;&nbsp;支付宝登录{if $L['变量']['alipay_uid']}<a href="/index.php/plugin/clogin/unbind/alipay/" onclick="return confirm('解绑后将无法通过支付宝一键登录，是否确定解绑？');" class="btn btn-sm btn-danger pull-right">解绑</a>{else}<a href="/index.php/plugin/clogin/connect/alipay/" class="btn btn-sm btn-success pull-right">绑定</a>{/if}</div>{/if}

		{if $L['变量']['login_sina']=='on'}<div class="list-group-item"><img src="/swap_mac/swap_plugins/clogin/icon/sina.png" width="30px">&nbsp;&nbsp;微博登录{if $L['变量']['sina_uid']}<a href="/index.php/plugin/clogin/unbind/sina/" onclick="return confirm('解绑后将无法通过微博一键登录，是否确定解绑？');" class="btn btn-sm btn-danger pull-right">解绑</a>{else}<a href="/index.php/plugin/clogin/connect/sina/" class="btn btn-sm btn-success pull-right">绑定</a>{/if}</div>{/if}

	</div>
	</div>
</div>
</div>