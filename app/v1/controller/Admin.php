<?php
declare (strict_types = 1);

namespace app\v1\controller;

use think\db\exception\DbException;
use think\facade\Db;
use app\middleware\IsAdminLogin;

/**
 * 管理员操作类
 * Class Admin
 * @package app\v1\controller
 */
class Admin extends Restful
{
    protected $middleware = [IsAdminLogin::class];
    /**
     * @return \think\response\Json
     */
    public function getMyInfo(){
        $uid = request()->aid;
        try {
            $res = Db::name('system_admin')
                ->field('account,real_name,avatar,last_ip,last_time')
                ->where('id', $uid)
                ->find();
            $res['last_time']=date('Y-m-d H:i',$res['last_time']);
            Db::name('system_admin')
                ->where('id', $uid)
                ->inc('login_count')
                ->update(['last_time' => time(), 'last_ip' => $this->request->ip()]);
        } catch (DbException $e) {
            return $this->resCode(500);
        }
        return $this->resCode(200,$res);
    }
}
