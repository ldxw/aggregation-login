<?php

function clogin_read_user_by_openid($type, $openid) {
	$arr = db_find_one('user_clogin_connect', array('type'=>$type, 'openid'=>$openid));
	if($arr) {
		$arr2 = user_read($arr['uid']);
		if($arr2) {
			$arr = array_merge($arr, $arr2);
		} else {
			db_delete('user_clogin_connect', array('id'=>$arr['id']));
			return FALSE;
		}
	}
	return $arr;
}

function clogin_create_user($type, $openid, $username, $faceimg) {
	global $conf, $time, $longip;

	// 自动产生一个用户名
	$r = user_read_by_username($username);
	if($r) {
		// 特殊字符过滤
		$username = xn_substr($username.'_'.$time, 0, 31);
		$r = user_read_by_username($username);
		if($r) return xn_error(-1, '用户名被占用。');
	}
	// 自动产生一个 Email
	$email = $type.xn_rand(9).'@bbs.com';
	$r = user_read_by_email($email);
	if($r) return xn_error(-1, 'Email 被占用');
	// 随机密码
	$password = md5(rand(1000000000, 9999999999).$time);
	$user = array(
		'username'=>$username,
		'email'=>$email,
		'password'=>$password,
		'gid'=>101,
		'salt'=>rand(100000, 999999),
		'create_date'=>$time,
		'create_ip'=>$longip,
		'avatar'=>0,
		'logins' => 1,
		'login_date' => $time,
		'login_ip' => $longip,
	);
	$uid = user_create($user);
	if(empty($uid)) return xn_error(-1, '注册失败');
	
	$user = user_read($uid);

	$r = db_insert('user_clogin_connect', array('uid'=>$uid, 'type'=>$type, 'openid'=>$openid));
	if(empty($r)) return xn_error(-1, '注册失败');
	
	runtime_set('users+', '1');
	runtime_set('todayusers+', '1');

	if(!empty($faceimg)) {
		$filename = "$uid.png";
		$dir = substr(sprintf("%09d", $uid), 0, 3).'/';
		$path = $conf['upload_path'].'avatar/'.$dir;
		!is_dir($path) AND mkdir($path, 0777, TRUE);
		
		$data = file_get_contents($faceimg);
		file_put_contents($path.$filename, $data);
		
		user_update($uid, array('avatar'=>$time));
	}
	return $user;
}

function clogin_bind_user($uid, $type, $openid){
	$r = db_insert('user_clogin_connect', array('uid'=>$uid, 'type'=>$type, 'openid'=>$openid));
	if(empty($r)){} return xn_error(-1, '绑定失败');
}
?>