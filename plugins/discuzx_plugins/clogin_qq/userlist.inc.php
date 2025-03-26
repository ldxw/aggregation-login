<?php
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}

$perpage = 50;
$start = $perpage * ($_G['page'] - 1);
$order = ' uid desc';
$numcount = DB::result_first("SELECT COUNT(*) FROM %t", array('clogin_member_qqconnect'));
$userdata = DB::fetch_all("SELECT * FROM %t  order by $order LIMIT $start,$perpage",
    array('clogin_member_qqconnect'));


$multipage = multi($numcount, $perpage, $_G['page'],
    ADMINSCRIPT."?action=plugins&operation=config&do=$plugin[pluginid]&identifier=$plugin[identifier]&pmod=$module[name]");
showformheader("plugins&operation=config&do=$plugin[pluginid]&identifier=$plugin[identifier]&pmod=$module[name]");
showtableheader('', '', '', 7);
showtablerow('class="header"', '',array('uid','&#29992;&#25143;&#21517;','OpenId','&#26165;&#31216;','&#22836;&#20687;','&#26102;&#38388;'));
foreach($userdata as $key=>$data){
    $userinfo = getuserbyuid($data['uid']);
    $userinfo['username'] = $userinfo['username'] ? '<a href="home.php?mod=space&uid='.$data['uid'].'" target="_blank">'.$userinfo['username'] : '<font style="color:#f00;">null</font>';
    $data['faceimg'] = '<a href="'.$data['faceimg'].'" target="_blank">[点击查看]</a>';
    $data['addtime'] = date("Y-m-d H:i:s",$data['addtime']);
    showtablerow('', '',array($data['uid'],$userinfo['username'],$data['openid'],$data['nickname'],$data['faceimg'],$data['addtime']));
}
showtablefooter();
showformfooter();
echo $multipage;
