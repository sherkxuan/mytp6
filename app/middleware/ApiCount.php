<?php
/** @noinspection PhpUnreachableStatementInspection */
declare (strict_types = 1);

namespace app\middleware;

use think\db\exception\DbException;
use think\facade\Cache;
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
     * @return \think\response\Json|\think\response\Redirect
     */
    public function handle(\think\Request $request, \Closure $next,$api_route='')
    {
        //判断是否传入第三个参数
        if($api_route!=null){
            try {
                //查询当前接口信息
                $res = Db::name('api_info')->where('api_route', $api_route)->find();
            } catch (DbException $e) {
                return json(['code'=>500,'data'=>'API出现异常!']);exit;
                //return redirect('returnCode/code/500/data/API exception!');
            }
            //判断接口是否存在
            if(isset($res)){
                //判断接口是否已调用完/若为负数表示无限调用
                if($res['call_num']==0) {
                    return json(['code'=>500,'data'=>'API调用次数已用完']);exit;
                    // return redirect('http://api.sherkxuan.ren/v1/returnCode/code/500/data/API calls are zero!');
                }
                //判断接口是否开启
                if($res['status']!=1){
                    return json(['code'=>500,'data'=>'当前API已关闭!']);exit;
                    //halt($request->domain().'/v1/returnCode/code/500/data/API closed!');
                    //return redirect('http://api.sherkxuan.ren/v1/returnCode/code/500/data/API closed!');
                }
                //判断请求者ip是否已被禁用
                if(isset($res['forbid_ip'])){
                    if(in_array($request->ip(),explode('|', $res['forbid_ip']))){
                        Cache::clear();
                        return json(['code'=>500,'data'=>'禁止当前IP访问此API']);exit;
                    }
                }

                //成功调用,api剩余次数减一
                try {
                    Db::name('api_info')
                        ->where('id', $res['id'])
                        ->dec('call_num')
                        ->update(['update_time' => time()]);
                } catch (DbException $e) {
                    return json(['code'=>500,'data'=>'API出现异常!']);exit;
                    //return redirect('http://api.sherkxuan.ren/v1/returnCode/code/500/data/API exception!');
                }

                //判断是否滥用接口
                //判断依据:三秒内调用10次则为脚本调用,不符合常理。图片上传若有多图，则只是调用一次接口.
                $num = Db::name('api_log')
                    ->where('access_ip',$request->ip())
                    ->where('create_time','between',[time()-3,time()])
                    ->count('id');
                if($num>10){
                    if(Cache::get($request->ip())){
                        Cache::inc($request->ip());
                    }else{
                        Cache::set($request->ip(),1,3600);
                    }
                    if(Cache::get($request->ip())==5){
                        try {
                            Db::name('api_info')
                                ->where('id', $res['id'])
                                ->update(['forbid_ip'=>empty($res['forbid_ip'])?$request->ip():$res['forbid_ip'].'|'.$request->ip()]);
                        } catch (DbException $e) {
                            return json(['code'=>500,'data'=>'API出现异常!']);exit;
                        }
                        Cache::delete($request->ip());
                        return json(['code'=>500,'data'=>'你已被禁止访问该API!']);exit;
                    }else{
                        return json(['code'=>500,'data'=>'频繁调用此API,给予警告!']);exit;
                    }

                    //return redirect('http://api.sherkxuan.ren/v1/returnCode/code/500/data/API calls frequently, please try again later!');
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
                return json(['code'=>500,'data'=>'API不存在!']);exit;
                //return redirect('http://api.sherkxuan.ren/v1/returnCode/code/500/data/The API does not exist!');
            }
        }
        //返回
        return $next($request);
    }
}
