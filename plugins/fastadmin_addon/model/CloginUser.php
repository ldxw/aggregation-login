<?php

namespace addons\clogin\model;

use think\Model;

/**
 * 第三方登录模型
 */
class CloginUser extends Model
{

    // 表名
    protected $name = 'clogin_user';
    
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];

    public function user()
    {
        return $this->belongsTo('\app\common\model\User', 'user_id', 'id', [], 'LEFT');
    }
}
