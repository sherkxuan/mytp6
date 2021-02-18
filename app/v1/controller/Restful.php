<?php
/** @noinspection PhpSeparateElseIfInspection */
declare (strict_types = 1);

namespace app\v1\controller;

use app\BaseController;
use think\facade\Request;
/**
 * Class Restful
 * @package app\v1\controller
 */
class Restful extends BaseController
{
    /**
     * 返回状态码
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
                return json(['code'=>202,'data'=>$data ?? '请求参数不存在']);
            case 203:
                return json(['code'=>203,'data'=>$data ?? '请求参数错误']);
            case 204:
                return json(['code'=>204,'data'=>$data ?? '请求数据不符合要求']);
            case 205:
                return json(['code'=>205,'data'=>$data ?? '权限不足']);
            case 206:
                return json(['code'=>206,'data'=>$data ?? '接口调用过于频繁']);
            case 404:
                return json(['code'=>404,'data'=>$data ?? '请求地址不存在']);
            default:
                return json(['code'=>500,'data'=>$data ?? '系统错误，稍后再试']);
        }
    }

    /**
     * @return string[]|\think\response\Json
     */
    public function isToken(){
        $token = request()->header('token');
        if(!$token)return ['code' => '201', 'data' => 'Token不存在'];
        $res = checkToken($token);
        if($res){
            return ['code'=>200,'data'=>$res['uid']];
        }else{
            return ['code'=>201,'data'=>'token已过期'];
        }

        /*
         * //token验证器
         *
        $res = $this->isToken();
        //判断token是否过期
        if($res['code']!=200)return $this->resCode($res['code'],$res['data']);
        //获取token里面的值
        $uid = $res['data'];
        *
        */
    }

    /**
     * 获取数据验证器
     * 参数一:string|array
     * 说明:可以传入一个字符串或者一个数组,用来获取前端的值,默认为空，表示获取全部的值
     *
     * 参数二:boolen
     * 说明:是否开启强制校验，默认为true开启状态,表示传进来的值不能为空,否则允许为空
     * @param string $data
     * @param false $is_null
     * @return array|mixed
     */
    public function getData($data='', $is_null=true){
        if(is_array($data)){
            $res = [];
            foreach ($data as $v){
                $res[$v] = Request::param($v);
            }
        }else{
            $res = Request::param($data);
        }
        if(empty($res)){
            echo json_encode(['code'=>202,'data'=>'请求参数不存在']);
            exit;
        }
        if(is_array($res)){
            $this->arrCheck($res,$is_null);
        }else{
            if(empty($res)){
                echo json_encode(['code'=>202,'data'=>'请求参数不存在']);
                exit;
            }
        }
        return $res;
    }

    /**
     * 供上一个方法调用，用来判断是否开启强制校验
     * @param $res
     * @param $is_null
     */
    public function arrCheck($res, $is_null){
        if($is_null){
            foreach ($res as $i){
                if(empty($i)){
                    echo json_encode(['code'=>202,'data'=>'请求参数不存在']);
                    exit;
                }
            }
        }else{
            if(empty($res)){
                echo json_encode(['code'=>202,'data'=>'请求参数不存在']);
                exit;
            }
        }
    }
}
