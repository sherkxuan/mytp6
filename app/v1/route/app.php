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
//上传文件路由
foreach ($res as $v){
    Route::rule($v['api_route'], $v['api_map'],'post|get')
        ->middleware(\app\middleware\ApiCount::class,$v['api_route']);
}
/*Route::rule('upload', 'Upload/upload02','post|get')
    ->middleware(\app\middleware\ApiCount::class,'upload');*/

Route::miss(function() {
    return '404 Not Found!';
});