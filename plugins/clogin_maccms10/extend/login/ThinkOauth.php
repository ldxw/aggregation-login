<?php
namespace login;

// 填写聚合登录接口地址
define("OAUTH_API_URL", "https://u.cccyun.cc/");


class ThinkOauth
{

    /**
     * 接口地址
     * @var string
     */
    private $AppUrl = OAUTH_API_URL.'connect.php';

    /**
     * 申请应用时分配的app_key
     * @var string
     */
    private $AppKey = '';

    /**
     * 申请应用时分配的 app_secret
     * @var string
     */
    private $AppSecret = '';

    /**
     * 授权后获取到的TOKEN信息
     * @var array
     */
    private $Token = null;

    /**
     * 调用接口类型
     * @var string
     */
    private $Type = '';

    /**
     * 构造方法，配置应用信息
     * @param array $token
     */
    public function __construct($type, $token = null)
    {
        $this->Type = $type;
        $this->Callback = THIRD_LOGIN_CALLBACK . $type;

        //获取应用配置
        $connect = config('maccms.connect');
        $tmp = $connect[$type];
        unset($config);
        $config['APP_KEY'] = $tmp['key'];
        $config['APP_SECRET'] = $tmp['secret'];

        if (empty($config['APP_KEY']) || empty($config['APP_SECRET'])) {
            throw new \think\Exception('请配置您申请的APP_KEY和APP_SECRET', 100001);
        } else {
            $this->AppKey = $config['APP_KEY'];
            $this->AppSecret = $config['APP_SECRET'];
            $this->Token = $token; //设置获取到的TOKEN
        }
    }

    /**
     * 取得Oauth实例
     * @static
     * @return mixed 返回Oauth
     */
    public static function getInstance($type, $token = null)
    {
        return new static($type, $token);
    }

    /**
     * 请求code
     */
    public function getRequestCodeURL()
    {
		$state = md5(uniqid(rand(), TRUE));
        if($this->Type == 'weixin') $this->Type = 'wx';

		//-------构造请求参数列表
		$params = array(
			"act" => "login",
			"appid" => $this->AppKey,
			"appkey" => $this->AppSecret,
			"type" => $this->Type,
			"redirect_uri" => $this->Callback,
			"state" => $state
		);
		$response = $this->http($this->AppUrl, $params);
		$arr = json_decode($response,true);
        if(isset($arr['code']) && $arr['code']==0){
            return $arr['url'];
        }elseif(isset($arr['code'])){
            throw new \think\Exception('登录接口返回：'.$arr['msg'],100003);
        }else{
            throw new \think\Exception('获取登录地址失败',100003);
        }
    }

    /**
     * 获取access_token
     * @param string $code 上一步请求到的code
     *      $code = $_GET['code']
     */
    public function getAccessToken($code, $extend = null)
    {

        //-------请求参数列表
		$params = array(
			"act" => "callback",
			"appid" => $this->AppKey,
			"appkey" => $this->AppSecret,
			"code" => $code
		);
		$response = $this->http($this->AppUrl, $params);
		$arr = json_decode($response,true);
        if(isset($arr['code']) && $arr['code']==0){
            return $arr;
        }elseif(isset($arr['code'])){
            throw new \think\Exception('登录失败，返回错误原因：'.$arr['msg'],100003);
        }else{
            throw new \think\Exception('获取登录数据失败',100003);
        }
    }

    /**
     * 组装接口调用参数 并调用接口
     */
    public function call($path)
    {
        if($this->Type == 'qq'){
            return ['ret'=>0, 'nickname'=>$this->Token['nickname'], 'figureurl_2'=>$this->Token['faceimg']];
        }elseif($this->Type == 'wx'){
            return ['errcode'=>0, 'nickname'=>$this->Token['nickname'], 'headimgurl'=>$this->Token['faceimg']];
        }
    }

    /**
     * 获取当前授权用户的SNS标识
     */
    public function openid()
    {
        return $this->Token['social_uid'];
    }

    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param  string $url 请求URL
     * @param  array $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @return string  $data   响应数据
     */
    protected function http($url, $params, $method = 'GET', $header = array(), $multi = false)
    {
        $opts = array(
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => $header
        );

        /* 根据请求类型设置特定参数 */
        switch (strtoupper($method)) {
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                //判断是否传输文件
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new \think\Exception('不支持的请求方式！',100005);
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error)
            throw new \think\Exception('请求发生错误：' . $error,100006);
        return $data;
    }

}
