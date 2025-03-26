<?php

use Illuminate\Database\Capsule\Manager as Capsule;


add_hook('ClientAreaPage', 1, function ($vars){
	$typearr = ['qq'=>'QQ','wx'=>'微信','alipay'=>'支付宝','sina'=>'微博'];

	$settings = [];
	$settingdata = Capsule::table('tbladdonmodules')->where('module', 'clogin')->get();
	foreach($settingdata as $row){
		$settings[$row->setting] = $row->value;
	}
	$systemurl = \WHMCS\Config\Setting::getValue('SystemURL');

	//print_r($settings);die();
	$connect = "";
    if ($settings['qq']=='on'||$settings['wx']=='on'||$settings['alipay']=='on'||$settings['sina']=='on') {
	    $userID = $_SESSION['uid'];
	    
        if (isset($userID)) { //已登录，显示绑定和解绑
			$avatar = '';
			$nickname = '';

			$uinfo_list = Capsule::table('mod_clogin_user')->where(['uid'=>$userID])->get();
			$uinfos = [];
			foreach($uinfo_list as $row){
				$uinfos[$row->type] = $row;
			}

			$connect .= '<div class="panel panel-default card"><div class="panel-heading card-header"><h3 class="panel-title card-title m-0"><i class="fas fa-user-circle"></i>&nbsp;&nbsp;第三方登录账号绑定</h3></div><div class="panel-body card-body"><div class="list-group">';
			foreach($typearr as $typecode=>$typename){
				if($settings[$typecode]=='on'){
					$uinfo = $uinfos[$typecode];
					$connect .= '<div class="list-group-item"><img src="'.$systemurl.'/modules/addons/clogin/icon/'.$typecode.'.png" width="30px">&nbsp;&nbsp;'.$typename.'登录：'.($uinfo?'<font color="green">已绑定</font>&nbsp;&nbsp;<a href="'.$systemurl.'/modules/addons/clogin/oauth/?unbind=1&type='.$typecode.'" onclick="return confirm(\'解绑后将无法通过'.$typename.'一键登录，是否确定解绑？\');" class="btn btn-sm btn-danger pull-right float-right">解绑</a>':'<font color="blue">未绑定</font>&nbsp;&nbsp;<a href="'.$systemurl.'/modules/addons/clogin/oauth/?type='.$typecode.'" class="btn btn-sm btn-success pull-right float-right">绑定</a>').'</div>';
					if($uinfo){
						$avatar = $avatar?$avatar:$uinfo->avatar;
						$nickname = $nickname?$nickname:$uinfo->nickname;
					}
				}
			}
			$connect .= '</div></div></div>';

        } else { //未登录，显示快捷登录
			$connect = '<style>.clogin{text-align:center;color:#a1a1a1}.clogin a{padding:10px}.clogin img{width:36px}</style>';
			$connect .= '<div class="clogin">使用第三方登录<br/><br/>';
			foreach($typearr as $typecode=>$typename){
				if($settings[$typecode]=='on'){
					$connect .= '<a href="'.$systemurl.'/modules/addons/clogin/oauth/?type='.$typecode.'" title="'.$typename.'登录"><img src="'.$systemurl.'/modules/addons/clogin/icon/'.$typecode.'.png"></a>';
				}
			}
			$connect .= '<br/><br/></div>';
        }
    }

    return [
        'connect' => $connect,
        'avatar' => $avatar,
        'nickname' => $nickname,
    ];
});
