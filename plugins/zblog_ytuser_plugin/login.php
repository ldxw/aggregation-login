<?php
require_once('global.php');
require_once('Oauth.class.php');

$apiurl = 'https://u.cccyun.cc/'; //接口地址

$config = $zbp->Config('YtUser');
$callback = $zbp->host."zb_users/plugin/YtUser/login.php";
$type = 'qq';

$Oauth=new Oauth($apiurl, $config->appid, $config->appkey, $callback);

if (!isset($_GET['code'])) {
    $arr = $Oauth->login($type);
    if(isset($arr['code']) && $arr['code']==0){
        redirect($arr['url']);
    }elseif(isset($arr['code'])){
        $zbp->ShowError('获取登录地址失败：'.$arr['msg']);
    }else{
        $zbp->ShowError('获取登录地址失败');
    }
} else {
    $arr = $Oauth->callback();
    if(isset($arr['code']) && $arr['code']==0){
        $openid=$arr['social_uid'];
        $access_token=$arr['access_token'];
        $nickname=$arr['nickname'];
        $avatar=$arr['faceimg'];
        /* 处理用户登录逻辑 */

        $ytuser = new Ytuser();
        $array = $ytuser->YtInfoByField('Oid',$openid);
        if($zbp->user->ID){
            if ($array) {
                Redirect($zbp->host."?Binding");die();
            }else{
                $ytuser->YtInfoByField('Uid',$zbp->user->ID);
                $ytuser->Oid=$openid;
                $ytuser->Save();
                Redirect($zbp->host."?Binding");
            }
        }
        if ($array) {
            $member = new Member;
            $member->LoadInfoByID($ytuser->Uid);
            if ($member->Status == ZC_MEMBER_STATUS_AUDITING) {
                $zbp->ShowError(79, __FILE__, __LINE__);
                die();
            }
            if ($member->Status == ZC_MEMBER_STATUS_LOCKED) {
                $zbp->ShowError(80, __FILE__, __LINE__);
                die();
            }
            $zbp->user = $member;
            SetLoginCookie($member, 0);
            Redirect($zbp->host);
        }else{
            $guid=GetGuid();
            $member=new Member;
            $member->Guid=$guid;
            $member->Name="yt_".$guid;
            $member->Alias=$nickname;
            $member->Password="0e681aa506fc191c5f2fa9be6abddd01";
            $member->HomePage="";
            $member->Level=5;
            $member->PostTime=time();
            $member->IP=GetGuestIP();
            $member->Metas->Img="";
            $member->Save();
            $get=$member->ID;
            $get=(int)$get;
            $ytuser = new YtUser();
            $ytuser->Uid = $member->ID;
            $ytuser->Oid = $openid;
            if ($zbp->Config('YtUser')->inv_on == 1){
                $rootid=(int)$_COOKIE["uuu"];
                $memberrid= new Member();
                $memberrid->LoadInfoByID($rootid);
                $ytuser->roodid=$memberrid->ID;
            }else{
                $ytuser->roodid=0;
            }
            $ytuser->Save();
            $zbp->user = $member;
            SetLoginCookie($member, 0);
            Redirect($zbp->host);
        }

    }elseif(isset($arr['code'])){
        $zbp->ShowError('登录失败，返回错误原因：'.$arr['msg']);
    }else{
        $zbp->ShowError('获取登录数据失败');
    }
}
?>