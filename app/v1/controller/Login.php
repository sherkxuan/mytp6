<?php
/*
 *
 *           ┌─┐       ┌─┐
 *        ┌──┘ ┴───────┘ ┴──┐
 *        │                 │
 *        │       ───       │
 *        │  ─┬┘       └┬─  │
 *        │                 │
 *        │       ─┴─       │
 *        │                 │
 *        └───┐         ┌───┘
 *            │         │
 *            │         │
 *            │         │
 *            │         └──────────────┐
 *            │                        │
 *            │                        ├─┐
 *            │                        ┌─┘
 *            │                        │
 *            └─┐  ┐  ┌───────┬──┐  ┌──┘
 *              │ ─┤ ─┤       │ ─┤ ─┤
 *              └──┴──┘       └──┴──┘
 *                  神兽保佑
 *                  代码无BUG!
 *
 */

namespace app\v1\controller;

use think\db\exception\DbException;
use think\facade\Db;

/**
 * Class Login
 * @package app\v1\controller
 */
class Login extends Restful
{
    /**
     * 验证登录的控制器，用于管理员登陆或者用户登录
     * @return \think\response\Json
     */
    public function adminLogin(){
        //获取请求参数
        /*$username = Request::param('username');
        if(!$username)return  $this->resCode(202,'Username parameter error');
        $password = Request::param('password');
        if(!$password)return $this->resCode(202,'Password parameter error');*/
        $username = $this->getData('username');
        $password = $this->getData('password');
        try {
            $admin = Db::name('system_admin')->where('account', $username)->find();
        } catch (DbException $e) {
            return $this->resCode(500,'系统错误，稍后再试');
        }
        if(!isset($admin))return $this->resCode(200,['msg'=>'用户不存在','token'=>'','status'=>0]);
        //设置密码
        //echo password_hash("123456", PASSWORD_DEFAULT);
        if (!password_verify($password, $admin['pwd'])) {
            return $this->resCode(200,['msg'=>'密码错误','token'=>'','status'=>0]);
        }else{
            $token = createToken($admin['id']);
            return $this->resCode(200,['msg'=>'登录成功','token'=>$token,'status'=>1]);
        }

    }

    /**
     * 验证token是否过期或者是否存在
     * @return \think\response\Json
     */
    public function isLogin(){
        //$token = Request::param('token');
        $token = $this->getData('token');
        $res = checkToken($token);
        if(isset($res['code'])){
            return $this->resCode(201,$res['data']);
        }else{
            return $this->resCode(200,true);
        }

    }
}