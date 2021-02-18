<?php
/** @noinspection PhpClassNamingConventionInspection */
declare (strict_types = 1);

namespace app\v1\controller;

use app\middleware\IsAdminLogin;
use app\v1\model\ApiInfo;
use think\facade\Db;
use think\db\exception\DbException;
use think\facade\Request;

/**
 * 接口管理模块
 * Class Api
 * @package app\v1\controller
 */
class Api extends Restful
{
    protected $middleware = [IsAdminLogin::class];

    /**
     * 获取接口列表
     * @return \think\response\Json
     */
    public function getApiList(){
        //$model = new ApiInfo();
        try {
            $res = ApiInfo::order('create_time','desc')->select();
        } catch (DbException $e) {
            return $this->resCode(500);
        }
        return $this->resCode(200,$res);
    }

    /**
     * 新增API
     * @return \think\response\Json
     */
    public function addApi(){
        //$data=Request::param();
        $data = $this->getData('',false);
        $model = new ApiInfo();
        $res = $model->save($data);
        if($res == 1){
            return $this->resCode(200);
        }else{
            return $this->resCode(500);
        }

    }

    /**
     * 根据ID删除API
     *
     * 后期有时间优化:
     * 根据传进来的值判断是否是数组
     * 数组?循环删除:单独删除
     * @return \think\response\Json
     */
    public function delApi(){
        //$id = Request::param('id');
        $id = $this->getData('id');
        //if(!$id)return  $this->resCode(202);
        $res = ApiInfo::destroy($id);
        if($res){
            return $this->resCode(200);
        }else{
            return $this->resCode(500);
        }
    }

    /**
     * 验证请求路由或映射是否存在
     * @return \think\response\Json
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function isRouteMap(){
        //$name = Request::param('name');
        $name = $this->getData('name');
        //if(!$name)return  $this->resCode(202);
        $res = ApiInfo::where('api_route',$name)->whereOr('api_map',$name)->find();
        if($res){
            return $this->resCode(200,true);
        }else{
            return $this->resCode(200,false);
        }
    }

    /**
     * 更改API的状态
     * @return \think\response\Json
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setApiStatus(){
        //$id = Request::param('id');
        //if(!$id)return  $this->resCode(202);
        $id = $this->getData('id');
        $data = ApiInfo::find($id);
        if($data['status']){
            $data->status = 0;
        }else{
            $data->status = 1;
        }
        $res = $data->save();
        if($res){
            return $this->resCode(200,true);
        }else{
            return $this->resCode(500);
        }
    }

    /**
     * 根据ID查询相应API数据
     * @return \think\response\Json
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getApiById(){
       // $id = Request::param('id');
        //if(!$id)return  $this->resCode(202);
        $id = $this->getData('id');
        $data = ApiInfo::field('api_route,api_map,name,exp,forbid_ip,method,call_num,status')->find($id);
        if($data){
            return $this->resCode(200,$data);
        }else{
            return $this->resCode(500);
        }
    }

    /**
     * 根据id修改相应API
     * @return \think\response\Json
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editApi(){
        //$id = Request::param('id');
        //if(!$id)return  $this->resCode(202);
        $id = $this->getData('id');
        $data = Request::except(['id'], 'post');
        $edit = ApiInfo::find($id);
        $res = $edit->save($data);
        if($res){
            return $this->resCode(200);
        }else{
            return $this->resCode(500);
        }

    }

    /**
     * 删除选中的id集合
     * @return \think\response\Json
     */
    public function delAll(){
        //$ids = Request::param();
        //if(!$ids)return  $this->resCode(202);
        $ids = $this->getData();
        foreach ($ids as $v){
            ApiInfo::destroy($v);
        }
        return $this->resCode(200);
    }

    /**
     * 查询最新的前50条记录
     * @return \think\response\Json
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getApiLog(){
        $res = Db::name('api_log')
                ->alias('a')
                ->join('zy_api_info b','a.aid = b.id')
                ->limit(50)
                ->field('b.name,a.access_ip,b.method,a.create_time')
                ->order('a.create_time','desc')
                ->select();
        return $this->resCode(200,$res);
    }

    /**
     * API调用排行
     * @return \think\response\Json
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCountApi(){
        $res = Db::name('api_log')
                ->alias('a')
                ->field('b.name,count(a.aid) as num')
                ->join('zy_api_info b','a.aid = b.id')
                ->limit(20)
                ->group('a.aid')
                ->order('num','desc')
                ->select();
        return $this->resCode(200,$res);
    }

    /**
     * 查询接口调用次数小于1000的api
     * @return \think\response\Json
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getApiWarning(){
        $res = ApiInfo::where('call_num','<=',1000)
                ->where('call_num','>=',0)
                ->field('name,call_num')
                ->select();
        return $this->resCode(200,$res);
    }
}
