<?php
require_once dirname(__FILE__).'/../../service/Public_service.php';
class Cloginbaidu extends MY_Service
{
    public function __construct(){
        parent::__construct();
        $this->load->model("third_model");
    }
    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        $data = $this->db->get_where($this->third_model->table,["app"=>"baidu"])->row_array();
        if(empty($data)){
            $data = [];
            $data['app'] = "baidu";
            $data['title'] = "百度";
            $data['status'] = 1;
            $data['sort'] = 0;
            $data['thumb'] = '[{"src":"\/addons\/cloginbaidu\/baidu.svg","desc":""}]';
            $data['addon'] = "cloginbaidu";
            $this->db->insert($this->third_model->table,$data);
        }
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        $this->db->delete($this->third_model->table,['app'=>"baidu"]);
        return true;
    }
}