<?php
declare (strict_types = 1);

namespace app\v1\controller;

use app\BaseController;

/**
 * Class Restful
 * @package app\v1\controller
 */
class Restful extends BaseController
{
    /**
     * @param $code
     * @param string $data
     * @return \think\response\Json
     */
    public function resCode($code, $data=null){
        switch ($code){
            case 200:
                return json(['code'=>200,'data'=>$data ?? 'SUCCESS']);
            case 201:
                return json(['code'=>201,'data'=>$data ?? 'Token过期或不存在']);
            case 202:
                return json(['code'=>202,'data'=>$data ?? '请求数据不存在']);
            case 203:
                return json(['code'=>203,'data'=>$data ?? '请求参数错误']);
            case 204:
                return json(['code'=>204,'data'=>$data ?? '请求数据不符合要求']);
            case 404:
                return json(['code'=>404,'data'=>$data ?? '请求地址不存在']);
            default:
                return json(['code'=>500,'data'=>$data ?? '系统错误，稍后再试']);
        }
    }
}
