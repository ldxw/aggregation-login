
function is_clogin_bind($uid, $type){
    $r = db_find_one('user_clogin_connect', array('uid'=>$uid, 'type'=>$type));
    if (!$r){
        return false;
    }else{
        return $r['openid'];
    }
}