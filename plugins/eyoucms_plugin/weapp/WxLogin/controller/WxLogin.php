<?php
/**
 * 易优CMS
 * ============================================================================
 * 版权所有 2016-2028 海南赞赞网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.eyoucms.com
 * ----------------------------------------------------------------------------
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * ============================================================================
 * Author: 小虎哥 <1105415366@qq.com>
 * Date: 2018-06-28
 */

namespace weapp\WxLogin\controller;

use think\Db;
use app\common\controller\Weapp;
use weapp\WxLogin\model\WxLoginModel;

/**
 * 插件的控制器
 */
class WxLogin extends Weapp
{
    /**
     * 实例化模型
     */
    private $model;
    /**
     * 插件基本信息
     */
    private $weappInfo;

    /**
     * 构造方法
     */
    public function __construct(){
        parent::__construct();
        $this->model = new WxLoginModel;

        /*插件基本信息*/
        $this->weappInfo = $this->getWeappInfo();
        $this->assign('weappInfo', $this->weappInfo);
        /*--end*/
    }

    /**
     * 插件安装后置操作
     */
    public function afterInstall()
    {
        // 系统默认是自动调用，这里在安装完插件之后，更改为手工调用
        $savedata = [
            'tag_weapp' => 2,
            'update_time'   => getTime(),
        ];
        Db::name('weapp')->where(['code'=>'WxLogin'])->update($savedata);
    }

    /**
     * 插件后台管理 - 列表
     */
    public function index()
    {
        $info = $this->model->getWeappData();
        $this->assign('info', $info['data']);

        $qqlogin_url = $this->root_dir.'/index.php?m=plugins&c=WxLogin&a=login';
        $this->assign('qqlogin_url', $qqlogin_url);

        return $this->fetch('index');
    }
    
    /**
     * 插件后台管理 - 编辑
     */
    public function save()
    {
        if (IS_POST) {
            $data = input('post.');

            $data['appurl']  = trim($data['appurl']);
            if (empty($data['appurl'])) $this->error('接口地址不能为空！');

            $data['appid']  = trim($data['appid']);
            if (empty($data['appid'])) $this->error('应用APPID不能为空！');

            $data['appkey'] = trim($data['appkey']);
            if (empty($data['appkey'])) $this->error('应用APPKEY不能为空！');
            
            $saveData = array(
                'data'        => serialize($data),
                'update_time' => getTime(),
            );

            $r = Db::name('weapp')->where(array('code' => 'WxLogin'))->update($saveData);
            if ($r) {
                adminLog('编辑' . $this->weappInfo['name'] . '成功'); // 写入操作日志
                $this->success("操作成功!", weapp_url('WxLogin/WxLogin/index'));
            }
        }
        $this->error("操作失败");
    }
}