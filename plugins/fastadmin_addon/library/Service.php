<?php

namespace addons\clogin\library;

use addons\clogin\model\CloginUser;
use app\common\model\User;
use fast\Random;
use think\Db;
use think\Exception;

/**
 * 第三方登录服务类
 *
 */
class Service
{

    /**
     * 第三方登录
     * @param string $type 平台
     * @param array  $params   参数
     * @param array  $extend   会员扩展信息
     * @param int    $keeptime 有效时长
     * @return boolean
     */
    public static function connect($type, $params = [], $extend = [], $keeptime = 0)
    {

        $nickname = $params['nickname'] ?? '';
        $avatar = $params['avatar'] ?? '';
        $values = [
            'type'        => $type,
            'openid'      => $params['openid'],
            'openname'    => $nickname,
        ];
        $values = array_merge($values, $params);

        $auth = \app\common\library\Auth::instance();

        $auth->keeptime($keeptime);
        //是否有自己的
        $cloginUser = CloginUser::get(['type' => $type, 'openid' => $params['openid']], 'user');
        if ($cloginUser) {
            if (!$cloginUser->user) {
                $cloginUser->delete();
            } else {
                $cloginUser->allowField(true)->save($values);
                // 写入登录Cookies和Token
                return $auth->direct($cloginUser->user_id);
            }
        }

        if ($auth->id) {
            if (!$cloginUser) {
                $values['user_id'] = $auth->id;
                CloginUser::create($values, true);
            }
            $user = $auth->getUser();
        } else {
            // 先随机一个用户名,随后再变更为u+数字id
            $username = Random::alnum(20);
            $password = Random::alnum(6);
            $domain = request()->host();

            Db::startTrans();
            try {
                // 默认注册一个会员
                $result = $auth->register($username, $password, $username . '@' . $domain, '', $extend);
                if (!$result) {
                    throw new Exception($auth->getError());
                }
                $user = $auth->getUser();
                $fields = ['username' => 'u' . $user->id, 'email' => 'u' . $user->id . '@' . $domain];
                if ($nickname) {
                    $fields['nickname'] = $nickname;
                }
                if ($avatar) {
                    $fields['avatar'] = function_exists("xss_clean") ? xss_clean(strip_tags($avatar)) : strip_tags($avatar);
                }

                // 更新会员资料
                $user = User::get($user->id);
                $user->save($fields);

                // 保存第三方信息
                $values['user_id'] = $user->id;
                CloginUser::create($values, true);
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                $auth->logout();
                return false;
            }
        }
        // 写入登录Cookies和Token
        return $auth->direct($user->id);
    }


    public static function isBindThird($type, $openid)
    {
        $conddtions = [
            'type'     => $type,
            'openid'   => $openid
        ];
        $cloginUser = CloginUser::get($conddtions, 'user');
        //第三方存在
        if ($cloginUser) {
            //用户失效
            if (!$cloginUser->user) {
                $cloginUser->delete();
                return false;
            }
            return true;
        }

        return false;
    }
}
