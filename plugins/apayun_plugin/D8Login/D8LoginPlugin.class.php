<?php
/**
 * Created by PhpStorm.
 * User: xiaoye
 * Email: 415907483@qq.com
 * Date: 2021/9/25
 * Time: 17:26
 */

namespace Plugin\D8Login;

use Plugin\Plugin;
use Detection\MobileDetect;

class D8LoginPlugin extends Plugin
{
    /**
     * 插件标识
     * @var string
     */
    public $code = 'D8Login';

    /**
     * @var string 自定义参数配置模板路径
     */
    public $custom_config = "View/config.html";

    /**
     * 插件信息
     * @var array
     */
    public $info
        = array(
            'name'        => '聚合登录',
            'title'       => '彩虹聚合登录',
            'description' => '彩虹聚合登录',
        );

    /**
     * 插件挂载钩子 -- 示例钩子： plugin_config
     * @var array
     */
    public $hooks = ['addLogin', 'loginAuth', 'loginPluginCode','plugin_config'];

    /**
     * 登录方式
     * @var array
     */
    public $login_types = [
        'qq' => 'QQ',
        'wx' => '微信',
        'alipay' => '支付宝',
        'sina' => '微博',
        'baidu' => '百度',
    ];


    /**
     * 安装程序
     * @return bool|mixed
     */
    public function install()
    {
        S('hooks', null);
        return true;
    }

    /**
     * 卸载程序
     * @return bool|mixed
     */
    public function uninstall()
    {
        return true;
    }

    private function getOpenType()
    {
        $list = [];
        $config = $this->getConfig();
        foreach($this->login_types as $key=>$value){
            if($config[$key] == 1) $list[] = $key;
        }
        return $list;
    }

    /**
     * 登录方式加载
     *
     * @param array $param
     */
    public function addLogin(&$param)
    {
        if ($this->verify()) {
            $mobile = new MobileDetect();
            $isMobile = $mobile->isMobile();
            $url = SITE_DOMAIN . "/ApiNotify/Notify/url/plugin/" . $this->code;
            $this->assign('auth_url', $url);
            $this->assign('auth_code', $this->code);
            $this->assign('opentype', $this->getOpenType());
            if ($isMobile) {
                $this->display('View/Mobile/login');
            } else {
                $this->display('View/Pc/login');
            }
        }
    }

    public function loginAuth(&$param = [])
    {
        if ($this->verify()) {
            $url = SITE_DOMAIN . "/ApiNotify/Bind/url/plugin/" . $this->code;
            $list = $this->getOpenType();
            foreach($list as $type){
                $param[$type] = [
                    'url'  => $url.'?type='.$type,
                    'name' => $this->login_types[$type],
                    'code' => $type,
                ];
            }
        }
    }

    public function loginPluginCode(&$params)
    {
        if ($this->verify()) {
            $list = $this->getOpenType();
            foreach($list as $type){
                $params[] = $type;
            }
        }
    }


    /**
     * 实现钩子 -- 示例钩子：plugin_config
     * @param mixed $params
     */
    public function plugin_config(&$params)
    {
        $this->SetPlugins();

        $params['login_types'] = $this->login_types;

        $plugin = M('plugin')->where(['code' => $this->code])->field('id,config,status')->find();
        $config = json_decode($plugin['config'], true);
        if ($plugin) {
            $params['config']=$config;
        }
    }

    private function SetPlugins()
    {
        foreach($this->login_types as $key=>$value){
            if(!M('plugin')->where(['plugin_type'=>'open', 'code'=>$key])->find())
                M('plugin')->add(['plugin_type'=>'open', 'code'=>$key, 'status'=>1]);
        }
        return;
        //吊云奇葩不会自动写hook入库 这里不存在，我们添加下
        for ($x=1; $x<=3; $x++) {
            switch ($x)
            {
                case 1:
                    $name ='addLogin';
                    break;
                case 2:
                    $name = 'loginAuth';
                    break;
                case 3:
                    $name ='loginPluginCode';
                    break;

            }
            $hook =  M('hook')->where(['name'=>$name])->find();
            if(strpos($hook['plugins'],$this->code) === false){
                $data['plugins'] = $this->code;
                if ($hook['plugins'] != ''){
                    $data['plugins'] = $hook['plugins'] .','. $this->code;
                }
                M('hook')->where(['name'=>$name])->save($data);
            }
        }
    }
}