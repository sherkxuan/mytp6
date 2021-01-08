<?php
/** @noinspection PhpSeparateElseIfInspection */
declare (strict_types = 1);

namespace app\middleware;

use think\db\exception\DbException;
use think\facade\Db;
/**
 * Class ApiCount
 * @package app\middleware
 */
class ApiCount
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure $next
     * @param string $api_route
     * @return Response
     */
    public function handle(\think\Request $request, \Closure $next,$api_route='')
    {
        //判断是否传入第三个参数
        if($api_route!=null){
            try {
                //查询当前接口信息
                $res = Db::name('api_info')->where('api_route', $api_route)->find();
            } catch (DbException $e) {
                $request->Apistatus = ['code'=>'500','data'=>'当前接口异常，请稍后再试'];
                return $next($request);
            }
            //判断接口是否存在
            if(isset($res)){
                //判断接口是否已调用完
                if($res['call_num']==0) {
                    $request->Apistatus = ['code' => '500', 'data' => '当前接口次数已用完'];
                    return $next($request);
                }
                //判断接口是否开启
                if($res['status']!=1){
                    $request->Apistatus = ['code'=>'500','data'=>'当前接口已停用'];
                    return $next($request);
                }
                //判断请求者ip是否已被禁用
                if(in_array($request->ip(),explode(',', $res['forbid_ip']))){
                    $request->Apistatus = ['code'=>'205','data'=>'当前ip已被禁用'];
                    return $next($request);
                }
                //成功调用,api剩余次数减一
                try {
                    Db::name('api_info')
                        ->where('id', $res['id'])
                        ->dec('call_num')
                        ->update(['update_time' => time()]);
                } catch (DbException $e) {
                    $request->Apistatus = ['code'=>'500','data'=>'当前接口异常，请稍后再试'];
                    return $next($request);
                }

                //判断是否滥用接口
                //判断依据:三秒内调用10次则为脚本调用,不符合常理。图片上传若有多图，则只是调用一次接口.
                $num = Db::name('api_log')
                    ->where('access_ip',$request->ip())
                    ->where('create_time','between',[time()-3,time()])
                    ->count('id');
                if($num>10){
                    $request->Apistatus = ['code'=>'500','data'=>'API调用次数过多，请稍后再试'];
                    return $next($request);
                }
                //组装数据
                $data = [
                    'aid'=>$res['id'],
                    'access_ip'=>$request->ip(),
                    'create_time'=>time()
                ];
                //接口请求成功插入到日志表
                Db::name('api_log')->insert($data);
                $request->Apistatus = ['code'=>'200'];
            }else{
                $request->Apistatus = ['code'=>'500','data'=>'当前接口不存在'];
            }
        }
        //返回
        return $next($request);
    }
}
