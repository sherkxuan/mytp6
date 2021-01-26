<?php
declare (strict_types = 1);

namespace app\v1\controller;

use think\facade\Request;

/**
 * 重定向专用控制器
 * Class Redirect
 * @package app\v1\controller
 */
class Redirect extends Restful
{
    /**
     * @return \think\response\Json
     */
    public function returnCode(){
        $data = Request::param();
        if(!isset($data['code']))return  $this->resCode(404,'禁止访问');
        if(!isset($data['data']))return  $this->resCode(404,'禁止访问');
        return $this->resCode($data['code'],$data['data']);
    }
}
