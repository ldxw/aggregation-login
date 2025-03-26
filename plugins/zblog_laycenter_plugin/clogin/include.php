<?php
$GLOBALS['zbp']->table['lcp_clogin'] = '%pre%lcp_clogin';
$GLOBALS['zbp']->datainfo['lcp_clogin'] = array(     	    		    		 	 	 	
    'ID'  =>array('ID','integer','',0),    	 		 		     	  		 		      	     
    'UID' =>array('UID','integer','',0),
    'Type' =>array('Type','string',20,''),
    'Openid'=>array('Openid','string',100,''),
);

RegisterPlugin ("LayCenter_clogin","ActivePlugin_LayCenter_clogin"); 

function ActivePlugin_LayCenter_clogin(){
    global $lcp;
    $config = $lcp->Config('clogin');
    if($config->enable_qq == 1) Add_Filter_Plugin('Filter_LayCenter_oAuth_Connect','LayCenter_clogin_qqlogin');
    if($config->enable_wx == 1) Add_Filter_Plugin('Filter_LayCenter_oAuth_Connect','LayCenter_clogin_wxlogin');
    if($config->enable_sina == 1) Add_Filter_Plugin('Filter_LayCenter_oAuth_Connect','LayCenter_clogin_sinalogin');
    if($config->enable_alipay == 1) Add_Filter_Plugin('Filter_LayCenter_oAuth_Connect','LayCenter_clogin_alipaylogin');
}

function InstallPlugin_LayCenter_clogin(){
    global $zbp;
    if(!$zbp->db->ExistTable($zbp->table['lcp_clogin'])) {     	
        $zbp->db->QueryMulit($zbp->db->sql->CreateTable($zbp->table['lcp_clogin'],$zbp->datainfo['lcp_clogin']));    	    		       	 				     		  			
    }
}

function LayCenter_clogin_qqlogin(){
    global $zbp,$lcp;
    
    return array(
        'icon' => 'layui-icon layui-icon-login-qq',
        'name' => 'QQ',
        'login'=> $zbp->host.'zb_users/LayCenter/clogin/login.php?type=qq',
        'callback' => array(
            'bind' => function($member, $name, $openid, $nickname, $avatar)use($lcp){
                
                $sql = $lcp->sql('clogin');
                $sql->LoadInfoByFields(['UID',$member->ID, 'Type'=>'qq']);
                
                if ($sql->Openid && $sql->Openid != $openid){
                    throw new Exception('该账户已经绑定了其他QQ账号');
                }
                
                $sql->UID = $member->ID;
                $sql->Type = 'qq';
                $sql->Openid = $openid;
                $sql->Save();
                
                return true;
            },
            'isBind' => function($member)use($lcp){
                $sql = $lcp->sql('clogin');
                $sql->LoadInfoByFields(['UID',$member->ID, 'Type'=>'qq']);
                
                return (bool)$sql->ID;
            },
            'unBind' => function($member)use($lcp){
                $sql = $lcp->sql('clogin');
                $sql->LoadInfoByFields(['UID',$member->ID, 'Type'=>'qq']);
                return $sql->Del();
            }
        )
    );
}

function LayCenter_clogin_wxlogin(){
    global $zbp,$lcp;
    
    return array(
        'icon' => 'layui-icon layui-icon-login-wechat',
        'name' => '微信',
        'login'=> $zbp->host.'zb_users/LayCenter/clogin/login.php?type=wx',
        'callback' => array(
            'bind' => function($member, $name, $openid, $nickname, $avatar)use($lcp){
                
                $sql = $lcp->sql('clogin');
                $sql->LoadInfoByFields(['UID',$member->ID, 'Type'=>'wx']);
                
                if ($sql->Openid && $sql->Openid != $openid){
                    throw new Exception('该账户已经绑定了其他微信账号');
                }
                
                $sql->UID = $member->ID;
                $sql->Type = 'wx';
                $sql->Openid = $openid;
                $sql->Save();
                
                return true;
            },
            'isBind' => function($member)use($lcp){
                $sql = $lcp->sql('clogin');
                $sql->LoadInfoByFields(['UID',$member->ID, 'Type'=>'wx']);
                
                return (bool)$sql->ID;
            },
            'unBind' => function($member)use($lcp){
                $sql = $lcp->sql('clogin');
                $sql->LoadInfoByFields(['UID',$member->ID, 'Type'=>'wx']);
                return $sql->Del();
            }
        )
    );
}

function LayCenter_clogin_sinalogin(){
    global $zbp,$lcp;
    
    return array(
        'icon' => 'layui-icon layui-icon-login-weibo',
        'name' => '微博',
        'login'=> $zbp->host.'zb_users/LayCenter/clogin/login.php?type=sina',
        'callback' => array(
            'bind' => function($member, $name, $openid, $nickname, $avatar)use($lcp){
                
                $sql = $lcp->sql('clogin');
                $sql->LoadInfoByFields(['UID',$member->ID, 'Type'=>'sina']);
                
                if ($sql->Openid && $sql->Openid != $openid){
                    throw new Exception('该账户已经绑定了其他微博账号');
                }
                
                $sql->UID = $member->ID;
                $sql->Type = 'sina';
                $sql->Openid = $openid;
                $sql->Save();
                
                return true;
            },
            'isBind' => function($member)use($lcp){
                $sql = $lcp->sql('clogin');
                $sql->LoadInfoByFields(['UID',$member->ID, 'Type'=>'sina']);
                
                return (bool)$sql->ID;
            },
            'unBind' => function($member)use($lcp){
                $sql = $lcp->sql('clogin');
                $sql->LoadInfoByFields(['UID',$member->ID, 'Type'=>'sina']);
                return $sql->Del();
            }
        )
    );
}

function LayCenter_clogin_alipaylogin(){
    global $zbp,$lcp;
    
    return array(
        'icon' => 'iconfont icon-zhifubao',
        'name' => '支付宝',
        'login'=> $zbp->host.'zb_users/LayCenter/clogin/login.php?type=alipay',
        'callback' => array(
            'bind' => function($member, $name, $openid, $nickname, $avatar)use($lcp){
                
                $sql = $lcp->sql('clogin');
                $sql->LoadInfoByFields(['UID',$member->ID, 'Type'=>'alipay']);
                
                if ($sql->Openid && $sql->Openid != $openid){
                    throw new Exception('该账户已经绑定了其他支付宝账号');
                }
                
                $sql->UID = $member->ID;
                $sql->Type = 'alipay';
                $sql->Openid = $openid;
                $sql->Save();
                
                return true;
            },
            'isBind' => function($member)use($lcp){
                $sql = $lcp->sql('clogin');
                $sql->LoadInfoByFields(['UID',$member->ID, 'Type'=>'alipay']);
                
                return (bool)$sql->ID;
            },
            'unBind' => function($member)use($lcp){
                $sql = $lcp->sql('clogin');
                $sql->LoadInfoByFields(['UID',$member->ID, 'Type'=>'alipay']);
                return $sql->Del();
            }
        )
    );
}