<?php
require '../../../zb_system/function/c_system_base.php';

if (!$zbp->CheckPlugin('LayCenter') || !$lcp->app->Check('clogin')) {$zbp->ShowError(48);die();}

$config = $lcp->Config('clogin');
$callback = $zbp->host . 'zb_users/LayCenter/clogin/callback.php';

$type = isset($_GET['type'])?$_GET['type']:'qq';

require 'Oauth.class.php';
$Oauth=new Oauth($config->appurl, $config->appid, $config->appkey, $callback);
$arr = $Oauth->login($type);
if(isset($arr['code']) && $arr['code']==0){
    redirect($arr['url']);
}elseif(isset($arr['code'])){
    $zbp->ShowError('获取登录地址失败：'.$arr['msg']);
}else{
    $zbp->ShowError('获取登录地址失败');
}