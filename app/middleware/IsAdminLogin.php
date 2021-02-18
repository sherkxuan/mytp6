<?php
declare (strict_types = 1);

namespace app\middleware;

use think\facade\Request;

/**
 * 验证token的中间件
 * Class IsAdminLogin
 * @package app\middleware
 */
class IsAdminLogin
{
    /**
     * 判断是否登录
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return \think\response\Json
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection PhpUnreachableStatementInspection
     */
    public function handle($request, \Closure $next)
    {
        //
        $token = Request::header('token');
        if(!$token){
            return json(['code'=>201,'data'=>'Token不存在!']);exit;
        }
        $tokenData = checkToken($token);

        if(isset($tokenData['code'])){
            return json(['code'=>$tokenData['code'],'data'=>$tokenData['data']]);exit;
        }
        $request->aid = $tokenData['uid'];
        return $next($request);
    }
}
