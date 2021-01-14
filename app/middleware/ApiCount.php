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
     * @return Response|\think\response\Redirect
     */
    public function handle(\think\Request $request, \Closure $next,$api_route='')
    {
        //判断是否传入第三个参数
        if($api_route!=null){
            try {
                //查询当前接口信息
                $res = Db::name('api_info')->where('api_route', $api_route)->find();
            } catch (DbException $e) {
                return redirect('returnCode/code/500/data/API exception!');
            }
            //判断接口是否存在
            if(isset($res)){
                //判断接口是否已调用完/若为负数表示无限调用
                if($res['call_num']==0) {
                    return redirect('returnCode/code/500/data/API calls are zero!');
                }
                //判断接口是否开启
                if($res['status']!=1){
                    return redirect('returnCode/code/500/data/API closed!');
                }
                //判断请求者ip是否已被禁用
                if(isset($res['forbid_ip'])){
                    if(in_array($request->ip(),explode(',', $res['forbid_ip']))){
                        return redirect('returnCode/code/500/data/The current IP is blocked!');
                    }
                }

                //成功调用,api剩余次数减一
                try {
                    Db::name('api_info')
                        ->where('id', $res['id'])
                        ->dec('call_num')
                        ->update(['update_time' => time()]);
                } catch (DbException $e) {
                    return redirect('returnCode/code/500/data/API exception!');
                }

                //判断是否滥用接口
                //判断依据:三秒内调用10次则为脚本调用,不符合常理。图片上传若有多图，则只是调用一次接口.
                $num = Db::name('api_log')
                    ->where('access_ip',$request->ip())
                    ->where('create_time','between',[time()-3,time()])
                    ->count('id');
                if($num>10){
                    return redirect('returnCode/code/500/data/API calls frequently, please try again later!');
                }
                //组装数据
                $data = [
                    'aid'=>$res['id'],
                    'access_ip'=>$request->ip(),
                    'create_time'=>time()
                ];
                //接口请求成功插入到日志表
                Db::name('api_log')->insert($data);
            }else{
                return redirect('returnCode/code/500/data/The API does not exist!');
            }
        }
        //返回
        return $next($request);
    }
}
