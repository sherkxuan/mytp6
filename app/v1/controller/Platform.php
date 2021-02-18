<?php
declare (strict_types = 1);

namespace app\v1\controller;

use app\middleware\IsAdminLogin;
use app\v1\model\Platform as P;
use think\facade\Cache;

/**
 * 平台管理专用控制器
 * Class Platform
 * @package app\v1\controller
 */
class Platform extends Restful
{
    protected $middleware = [IsAdminLogin::class];
    /**
     * 输出平台列表
     * @return mixed
     */
    public function getPlatformList(){
        $model = new P();
        $res = $model
                ->alias('a')
                ->field('a.id,a.admin_id,a.original_id,b.real_name,a.name,a.describe,a.type,a.qr_code,a.status,a.create_time,a.is_connect')
                ->join('zy_system_admin b','a.admin_id = b.id')
                ->order('a.create_time','desc')
                ->select();
        return $this->resCode(200,$res);
    }

    /**
     * 删除平台
     * $ids可以是数组
     * @return \think\response\Json
     * @noinspection PhpSeparateElseIfInspection
     */
    public function delPlatform(){
        $ids = $this->getData('ids');
        $type = $this->getData('type');
        //if(!($ids || $type))return  $this->resCode(202);
        if(is_array($ids)){
            if($type == 0){
                foreach ($ids as $v){
                    P::destroy($v);
                }
            }else if($type == 1){
                foreach ($ids as $v){
                    P::destroy($v,true);
                }
            }else{
                return $this->resCode(204);
            }
        }else{
            if($type == 0){
                P::destroy($ids);
            }else if($type == 1){
                P::destroy($ids,true);
            }else{
                return $this->resCode(204);
            }
            return $this->resCode(200);
        }
        return $this->resCode(500);
    }

    /**
     * 添加新的平台
     * @return \think\response\Json
     */
    public function addPlatform(){
        $data = $this->getData('',false);
        //if(!$data)return  $this->resCode(202);
        $model = new P();
        $res = $model->save($data);
        if($res){
            Cache::delete(md5($data['original_id']));
            return $this->resCode(200);
        }else{
            return $this->resCode(500);
        }
    }

    /**
     * 修改平台的状态，关闭后，将无法调用相关接口
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setPlatformStatus(){
        $id = $this->getData('id');
        //if(!$id)return  $this->resCode(202);
        $data = P::find($id);
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
     * 查询重复的平台，根据original_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function isPlatformRepeat(){
        $oid = $this->getData('original_id');
        //if(!$oid)return  $this->resCode(202);
        $res = P::where('original_id',$oid)->find();
        if($res){
            return  $this->resCode(200,true);
        }else{
            return  $this->resCode(200,false);
        }
    }

    /**
     * 根据ID查询相应的数据
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPlatformById(){
        $id = $this->getData('id');
        //if(!$id)return  $this->resCode(202);
        return $this->resCode(200,P::find($id));
    }

    /**
     * 将平台状态改完已接入
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function serPlatformConnect(){
        $id = $this->getData('id');
        //if(!$id)return  $this->resCode(202);
        $model = P::find($id);
        if(empty($model))return $this->resCode(500);
        $model->is_connect = 1;
        $res = $model->save();
        if($res){
            return $this->resCode(200);
        }else{
            return $this->resCode(500);
        }
    }
}
