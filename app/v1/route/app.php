<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;
use think\facade\Db;
try {
    $res = Db::name('api_info')->select();
} catch (\think\db\exception\DbException $e) {
    return '404 Not Found!';
}
//halt($res);
//调用接口路由，执行中间件路由
foreach ($res as $v){
    Route::rule($v['api_route'], $v['api_map'],$v['method'])
        ->middleware(\app\middleware\ApiCount::class,$v['api_route']);
}

//错误专用回调路由
Route::rule('returnCode', 'UnRedirect/returnCode','get');

Route::miss(function() {
    return json(['code'=>500,'data'=>'非法请求']);
});