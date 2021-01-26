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
     * @return Response|\think\response\Redirect
     * @noinspection PhpMissingParamTypeInspection
     */
    public function handle($request, \Closure $next)
    {
        //
        $token = Request::header('token');
        $tokenData = checkToken($token);

        if(isset($tokenData['code'])){
            return redirect('returnCode/code/'.$tokenData['code'].'/data/'.$tokenData['data']);
        }
        $request->aid = $tokenData['uid'];
        return $next($request);
    }
}
