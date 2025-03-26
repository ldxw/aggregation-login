<?php
class Clogin_Widget extends Widget_Abstract_Users
{
    private $referer = ''; // 来源页面

    public function __construct($request, $response, $params = null)
    {
        parent::__construct($request, $response, $params);
    }
    /**
     * 获取用户绑定信息
     *
     * @access public
     */
    public function userinfo(){
        $data = ['code'=>0];
        if ($this->user->hasLogin()) {
            $types = Clogin_Plugin::LOGIN_TYPE;
            $opentype = Clogin_Plugin::getoptions()->opentype;
            $list = [];
            foreach ($opentype as $type) {
                $user = $this->db->fetchRow($this->db->select()->from('table.connect_user')->where('uid = ?', $this->user->uid)->where('type = ?', $type)->limit(1));
                $url = Typecho_Common::url('/connect?type='.$type, Typecho_Widget::Widget('Widget_Options')->index);
                $list[] = ['type'=>$type, 'name'=>$types[$type], 'isbind'=>$user?true:false, 'url'=>$url, 'openid'=>$user['openid']];
            }
            $data['data'] = $list;
        }else{
            $data = ['code'=>-1,'msg'=>'no login'];
        }
        $this->response->throwJson($data);
    }
    /**
     * 获取Oauth登录地址，重定向
     *
     * @access public
     * @param string $type 第三方登录类型
     */
    public function connect()
    {
        $type = $this->request->get('type');
        if (is_null($type)) throw new Typecho_Widget_Exception("请选择登录方式!");

        $type = strtolower($type);
        $options = Clogin_Plugin::getoptions();

        if (!in_array($type,$options->opentype)) {
            throw new Typecho_Widget_Exception("不支持该登录方式!");
        }

        $callback = Typecho_Common::url('/connect_callback?type='.$type, Typecho_Widget::Widget('Widget_Options')->index);
        require_once dirname(__FILE__) . '/lib/Oauth.class.php';
        $Oauth = new Oauth($options->apiurl, $options->appid, $options->appkey, $callback);

        //开户session
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        // 登录前页面
        $this->referer = $this->request->getReferer();
        if (strpos($this->referer, 'profile.php')) {
            // 站内来源页放入session
            $_SESSION['Clogin_Referer'] = $this->referer;
        }
        $arr = $Oauth->login($type);
        if(isset($arr['code']) && $arr['code']==0){
            $this->response->redirect($arr['url']);
        }elseif(isset($arr['code'])){
            throw new Typecho_Widget_Exception('获取登录地址失败：'.$arr['msg']);
        }else{
            throw new Typecho_Widget_Exception('获取登录地址失败');
        }
    }
    /**
     * 第三方登录回调
     *
     * @access public
     * @param array $do POST来的用户绑定数据
     * @param string $type 第三方登录类型
     */
    public function callback()
    {
        //开户session
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
        // session内取出来源页
        $this->referer = isset($_SESSION['Clogin_Referer']) ? $_SESSION['Clogin_Referer'] : '';
        unset($_SESSION['Clogin_Referer']);
        $types = Clogin_Plugin::LOGIN_TYPE;

        $options = Clogin_Plugin::getoptions();
        $code = $this->request->get('code', '');
        $state = $this->request->get('state', '');
        if (empty($code) || empty($state)) {
            $this->response->redirect($this->options->index);
        }
        if($state != $_SESSION['Oauth_state']){
            throw new Typecho_Widget_Exception("The state does not match. You may be a victim of CSRF.");
        }

        require_once dirname(__FILE__) . '/lib/Oauth.class.php';
        $Oauth = new Oauth($options->apiurl, $options->appid, $options->appkey);
        $arr = $Oauth->callback();
        if (isset($arr['code']) && $arr['code']==0) {
            $type=$arr['type'];
            $typename = $types[$type];
            $openid=$arr['social_uid'];
            if ($this->user->hasLogin()) {
                $user = $this->findUser($openid, $type);
                if($user && $user['uid']!=$this->user->uid){
                    $this->widget('Widget_Notice')->set(array('该'.$typename.'账号已被本站其他用户绑定，请更换'.$typename.'账号重试'));
                    $this->response->redirect(empty($this->referer) ? $this->options->index : $this->referer);
                }elseif(!$user){
                    $this->bindUser($this->user->uid, $type, $openid);
                }
                $this->widget('Widget_Notice')->set(array('绑定'.$typename.'账号成功'));
                $this->response->redirect(empty($this->referer) ? $this->options->index : $this->referer);
            }else{
                $user = $this->findUser($openid, $type);
                if($user){
                    //已经绑定，直接登录
                    $this->useUidLogin($user['uid']);
                    //提示，并跳转
                    $this->widget('Widget_Notice')->set(array('登录成功'));
                    $this->response->redirect(empty($this->referer) ? $this->options->index : $this->referer);
                }else{
                    if($options->autoreg == 1){
                        //新注册账号
                        $uid = $this->regUser($type, $openid, $arr['nickname']);
                        if ($uid) {
                            $this->widget('Widget_Notice')->set(array('已成功注册并登陆'));
                        } else {
                            $this->widget('Widget_Notice')->set(array('注册用户失败'), 'error');
                        }
                        $this->response->redirect(empty($this->referer) ? $this->options->index : $this->referer);
                    }else{
                        $this->widget('Widget_Notice')->set(array('该'.$typename.'账号未绑定本站用户，请使用用户名登录后绑定'));
                        $this->response->redirect(empty($this->referer) ? $this->options->index : $this->referer);
                    }
                }
            }
        }elseif(isset($arr['code'])){
            throw new Typecho_Widget_Exception('登录失败，返回错误原因：'.$arr['msg']);
        }else{
            throw new Typecho_Widget_Exception('获取登录数据失败');
        }
    }

    //注册用户
    private function regUser($type, $openid, $nickname)
    {
        $data = array(
            'name' => 'o'.Typecho_Common::randString(9),
            'screenName'=>  $nickname,
            'created'   =>  $this->options->gmtTime,
            'group'     =>  'subscriber'
        );
        $insertId = $this->insert($data);
        if ($insertId) {
            $this->bindUser($insertId, $type, $openid);
            $this->useUidLogin($insertId);
            return $insertId;
        } else {
            return false;
        }
    }

    //绑定用户
    private function bindUser($uid, $type, $openid)
    {
        $oauth_user = ['uid'=>$uid, 'type'=>$type, 'openid'=>$openid, 'addtime'=>date("Y-m-d H:i:s")];
        $this->db->query($this->db->insert('table.connect_user')->rows($oauth_user));
    }

    //查找第三方账号
    private function findUser($openid, $type)
    {
        return $this->db->fetchRow($this->db->select()
            ->from('table.connect_user')
            ->where('openid = ?', $openid)
            ->where('type = ?', $type)
            ->limit(1));
    }

    //使用用户uid登录
    private function useUidLogin($uid, $expire = 0)
    {
        $authCode = function_exists('openssl_random_pseudo_bytes') ?
        bin2hex(openssl_random_pseudo_bytes(16)) : sha1(Typecho_Common::randString(20));
        $user = array('uid'=>$uid,'authCode'=>$authCode);

        Typecho_Cookie::set('__typecho_uid', $uid, $expire);
        Typecho_Cookie::set('__typecho_authCode', Typecho_Common::hash($authCode), $expire);

        //更新最后登录时间以及验证码
        $this->db->query($this->db
            ->update('table.users')
            ->expression('logged', 'activated')
            ->rows(array('authCode' => $authCode))
            ->where('uid = ?', $uid));
    }

}
