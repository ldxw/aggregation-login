<?php
require '../../../zb_system/function/c_system_base.php';
$zbp->Load();

if (!$zbp->CheckPlugin('LayCenter') || !$lcp->app->Check('clogin')) {$zbp->ShowError(48);die();}
if (!isset($_GET['code'])){
    redirect($lcp->BuildUrl('Login'));
}
$config = $lcp->Config('clogin');

$type = isset($_GET['type'])?$_GET['type']:'qq';

require 'Oauth.class.php';
$Oauth=new Oauth($config->appurl, $config->appid, $config->appkey);
$arr = $Oauth->callback();
if(isset($arr['code']) && $arr['code']==0){
    $openid=$arr['social_uid'];
    $access_token=$arr['access_token'];
    $nickname=$arr['nickname'];
    $avatar=$arr['faceimg'];
    /* 处理用户登录逻辑 */

    $sql = $lcp->sql('clogin');
    $sql->LoadInfoByFields(['Type'=>$type, 'Openid'=>$openid]);

    $member = new Member;
    if ($sql->ID){
        $member->LoadInfoByID($sql->UID);
    }

    if($type == 'qq') $name = 'QQ';
    else if($type == 'wx') $name = '微信';
    else if($type == 'sina') $name = '微博';
    else if($type == 'alipay') $name = '支付宝';

    $lcp->login->oAuthCallback($member, $name, $openid, $nickname, $avatar);

}elseif(isset($arr['code'])){
    $zbp->ShowError('登录失败，返回错误原因：'.$arr['msg']);
}else{
    $zbp->ShowError('获取登录数据失败');
}
