<?php
/**
 * 彩虹聚合登录，免申请支持QQ、微信、微博、支付宝等登录方式
 *
 * @package Clogin
 * @author 消失的彩虹海
 * @version 1.0
 * @link https://u.cccyun.cc/
 */
class Clogin_Plugin implements Typecho_Plugin_Interface
{
    const PLUGIN_NAME  = 'Clogin';
    const PLUGIN_PATH  = __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/Clogin/';
    const LOGIN_TYPE = ['qq'=>'QQ','wx'=>'微信','sina'=>'微博','alipay'=>'支付宝'];

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        $info = self::installDb();

        Typecho_Plugin::factory('admin/profile.php')-> bottom = array(__CLASS__, 'adminProfile');

        Helper::addRoute('connect', '/connect', 'Clogin_Widget', 'connect');
        Helper::addRoute('connect_callback', '/connect_callback', 'Clogin_Widget', 'callback');
        Helper::addRoute('connect_info', '/connect_info', 'Clogin_Widget', 'userinfo');

        return _t($info);
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        Helper::removeRoute('connect');
        Helper::removeRoute('connect_callback');
        //删除数据表
        return self::removeTable();
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $input = new Typecho_Widget_Helper_Form_Element_Text('apiurl',null,'https://u.cccyun.cc/',_t('接口地址：'),_t('接口地址必须以http://或https://开头，以/结尾'));
        $form->addInput($input);

        $input = new Typecho_Widget_Helper_Form_Element_Text('appid',null,'',_t('APPID：'));
        $form->addInput($input);

        $input = new Typecho_Widget_Helper_Form_Element_Text('appkey',null,'',_t('APPKEY：'));
        $form->addInput($input);

        $options = self::LOGIN_TYPE;
        $input = new Typecho_Widget_Helper_Form_Element_Checkbox('opentype',$options,'',_t('开启的登录方式：'));
        $form->addInput($input);

        $input = new Typecho_Widget_Helper_Form_Element_Radio('autoreg', array(1=>_t('是'),0=>'否'), 1, _t('是否自动注册：'), _t('用户使用第三方登录后，如果本站无绑定用户，则自动注册新用户'));
        $form->addInput($input);

        echo '<ul class="typecho-option"><li><label class="typecho-label">使用说明：</label><p><font color=red>开启插件后，在主题的适当位置添加以下代码，才能显示第三方登录按钮：<br/><code>&lt;?php Clogin_Plugin::show();?&gt;</code></font></p></li></ul>';
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /** 获取插件配置 */
    public static function getoptions(){
        return Helper::options()->plugin(self::PLUGIN_NAME);
    }

    /**
     * 安装数据库
     */
    public static function installDb()
    {
        try {
            return self::addTable();
        } catch (Typecho_Db_Exception $e) {
            if ('42S01' == $e->getCode()) {
                $msg = '数据表oauth_user已存在!';
                return $msg;
            }
        }
    }
    //添加数据表
    public static function addTable()
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        if ("Pdo_Mysql" === $db->getAdapterName() || "Mysql" === $db->getAdapterName()) {
            $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}connect_user` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `uid` int(11) unsigned NOT NULL,
                  `type` varchar(20) NOT NULL,
                  `openid` varchar(100) NOT NULL,
                  `addtime` datetime NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `uid` (`uid`),
                  KEY `openid` (`openid`,`type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->query($sql);
        } else {
            throw new Typecho_Plugin_Exception(_t('对不起, 本插件仅支持MySQL数据库。'));
        }
        return "数据表connect_user安装成功！";
    }
    //删除数据表
    public static function removeTable()
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        try {
            $db->query("DROP TABLE `" . $prefix . "connect_user`", Typecho_Db::WRITE);
        } catch (Typecho_Exception $e) {
            return "删除connect_user表失败！";
        }
        return "删除connect_user表成功！";
    }
    //在前端调用显示登录按钮
    public static function show($text = false)
    {
        if ($text) {
            //文本样式
            $format= '<a href="{url}">{title}</a>&nbsp;&nbsp;';
        } else {
            //登录按钮样式
            $format= '<a href="{url}" title="{title}"><img src="/usr/plugins/Clogin/icon/{type}.png" width="32px"></a>&nbsp;&nbsp;';
        }

        $opentype = self::getoptions()->opentype;
        $options = self::LOGIN_TYPE;
        $html = '<div class="text-align:center;margin: 5px 0 5px 0;">';
        foreach ($opentype as $type) {
            $url = Typecho_Common::url('/connect?type='.$type, Typecho_Widget::Widget('Widget_Options')->index);
            $html .= str_replace(
                array('{type}','{title}','{url}'),
                array($type,$options[$type],$url),
                $format
            );
        }
        $html .= '</div>';
        echo $html;
    }

    
    /** 管理后台个人设置 */
    public static function adminProfile(){
        $url = Typecho_Common::url('/connect_info', Typecho_Widget::Widget('Widget_Options')->index);
        include dirname(__FILE__).'/profile.php';
    }
}
