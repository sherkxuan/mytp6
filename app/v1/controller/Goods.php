<?php
/*
 *
 *           ┌─┐       ┌─┐
 *        ┌──┘ ┴───────┘ ┴──┐
 *        │                 │
 *        │       ───       │
 *        │  ─┬┘       └┬─  │
 *        │                 │
 *        │       ─┴─       │
 *        │                 │
 *        └───┐         ┌───┘
 *            │         │
 *            │         │
 *            │         │
 *            │         └──────────────┐
 *            │                        │
 *            │                        ├─┐
 *            │                        ┌─┘
 *            │                        │
 *            └─┐  ┐  ┌───────┬──┐  ┌──┘
 *              │ ─┤ ─┤       │ ─┤ ─┤
 *              └──┴──┘       └──┴──┘
 *                  神兽保佑
 *                  代码无BUG!
 *
 */

declare (strict_types = 1);

namespace app\v1\controller;

use app\middleware\IsAdminLogin;
use app\v1\model\GoodsClass;
use app\v1\model\GoodsDetails;
use app\v1\model\GoodsModel;
use app\v1\model\Goods as GoodsM;
use app\v1\model\GoodsModelProp;
use app\v1\model\GoodsModelSpec;
use app\v1\model\GoodsProp;
use app\v1\model\GoodsServe;
use app\v1\model\GoodsSku;
use app\v1\model\SpecValues;
use think\facade\Db;

/**
 * 处理商品模块的控制器
 *
 * addGoodsModel:添加商品模型
 * getGoodsModelList:输出商品模型列表
 * delGoodsModel:商品模型删除或移至回收站
 * getGoodsModelById:根据id获取对应商品模型
 * editGoodsModel:根据id修改相应商品模型
 * addGoodsClass:增加商品类别(三级分类)
 * getGoodsClassOne:查询商品一级分类(已筛选,适用于添加分类)
 * getGoodsClassTwo:根据一级id查询商品二级分类
 * getGoodsClassList:查询商品分类数据列表
 * editGoodsClassSort:根据id重新设置商品的排序值
 * delGoodsClass:根据id将商品类别删除或移入回收站(批量)
 * getGoodsClassById:根据id查询分类详情
 * getGoodsClassWhere:根据请求查询对应的等级分类全部数据(level=1:查询所有一级分类，level=2:查询所有二级分类，level=3:查询所有三级分类)
 * editGoodsClass:更新商品分类数据
 * addGoodsServe:新增商品服务
 * getGoodsServeList:输出商品服务列表
 * getGoodsServeById:根据id查询对应的商品服务
 * editGoodsServe:修改商品服务
 * delGoodsServe:删除商品模型(彻底删除)
 * addGoods:添加商品
 * getGoodsList:输出商品列表
 * setGoodsStatus:通过此接口可以让商品上架销售或下架到仓库
 * getGoodsByIdJson:查询商品详情(json输出)
 * editGoods:修改商品详情
 * editGoodsSku:修改商品库存
 * delGoods:删除商品or加入回收站,通过type判断
 * searchGoods:搜索商品
 * getGoodsRecycle:查询软删除的商品&&商品模型&&商品分类
 * restoreGoods:调用此接口可以恢复商品，品模型，商品分类 type[goods:商品;goodsModel:商品模型;goodsClass:商品分类]
 * Class Goods
 * @package app\v1\controller
 */
class Goods extends Restful
{
    protected $middleware = [IsAdminLogin::class];
    /**
     * 添加商品模型
     * @return \think\response\Json
     * @throws \Exception
     */
    public function addGoodsModel(){
        $name = $this->getData('name');
        $Spec = $this->getData('Spec');
        $Prop = $this->getData('Prop');
        // 启动事务
        Db::startTrans();
        try{
            $goodsModel = new GoodsModel();
            $goodsModelProp = new GoodsModelProp();
            //$goodsModelSpec = new GoodsModelSpec();
            $specValues = new SpecValues();
            //添加商品模型记录
            $goodsModel->save(['model_name'=>$name]);
            foreach ($Spec as $v){
                $tmpSpec = [
                    'gm_id'=>$goodsModel->id,
                    'spec_name'=>$v['SpecName'],
                ];
                $gms = GoodsModelSpec::create($tmpSpec);
                $tmpSpecValue = [];
                foreach ($v['SpecValues'] as $i){
                    $tmpSpecValue[] = [
                        'gms_id'=>$gms->id,
                        'spec_value'=>$i
                    ];
                }
                $specValues->saveAll($tmpSpecValue);
            }
            $tmpProp = [];
            //添加商品属性
            foreach ($Prop as $item){
                $tmpProp[] = [
                    'gm_id'=>$goodsModel->id,
                    'prop_name'=>$item['PropName'],
                    'prop_values'=>implode('_',$item['PropValues'])
                ];
            }
            $goodsModelProp->saveAll($tmpProp);
            // 提交事务
            Db::commit();
            return $this->resCode(200);
        }catch (\Exception $e){
            // 回滚事务
            Db::rollback();
            return $this->resCode(500);
            //return $this->resCode(500,$e->getMessage());
        }
    }

    /**
     * 输出商品模型列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGoodsModelList(){
        $model=new GoodsModel();
        $data= $model->with('goodsModelSpec')->field('id,model_name,create_time')->order('create_time','desc')->select();
        foreach($data as $k=>$v){
            foreach($v['goodsModelSpec'] as $k2=>$i){
                $data[$k]['goodsModelSpec'][$k2]['value']=SpecValues::where('gms_id',$i['id'])->field('spec_value,id')->select();
                unset($data[$k]['goodsModelSpec'][$k2]['create_time']);
                unset($data[$k]['goodsModelSpec'][$k2]['update_time']);
                unset($data[$k]['goodsModelSpec'][$k2]['gm_id']);
            }
            $data[$k]['goodsModelProp'] =
                GoodsModelProp::where('gm_id',$v['id'])
                    ->field('id,prop_name,prop_values')
                    ->select();
        }
        return $this->resCode(200,$data);
    }

    /**
     * 商品模型删除或移至回收站
     * type=0移入回收站;type=1彻底删除
     * @return \think\response\Json
     */
    public function delGoodsModel(){
        $type = $this->getData('type');
        $id = $this->getData('id');
        if($type==1){
            GoodsModel::destroy($id,true);
        }else{
            GoodsModel::destroy($id);
        }
        return $this->resCode(200);
    }

    /**
     * 根据id获取对应商品模型
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGoodsModelById(){
        $id = $this->getData('id');
        $model=new GoodsModel();
        $data= $model->with('goodsModelSpec')->field('id,model_name,create_time')->order('create_time','desc')->find($id);
        foreach($data['goodsModelSpec'] as $k=>$v){
            $data['goodsModelSpec'][$k]['value']=SpecValues::where('gms_id',$v['id'])->field('spec_value')->column('spec_value');
            unset($data['goodsModelSpec'][$k]['create_time']);
            unset($data['goodsModelSpec'][$k]['update_time']);
            unset($data['goodsModelSpec'][$k]['gm_id']);
        }
        $data['goodsModelProp'] = GoodsModelProp::where('gm_id',$data['id'])
                ->field('id,prop_name,prop_values')
                ->select();
        return $this->resCode(200,$data);
    }

    /**
     * 根据id修改相应商品模型
     * @return \think\response\Json
     */
    public function editGoodsModel(){
        $name = $this->getData('name');
        $Spec = $this->getData('Spec');
        $Prop = $this->getData('Prop');
        $id = $this->getData('id');
        //修改商品模型表
        Db::startTrans();
        try {
            $specValues = new SpecValues();
            $goodsModelProp = new GoodsModelProp();
            GoodsModelSpec::where('gm_id',$id)->delete();
            GoodsModelProp::where('gm_id',$id)->delete();
            $goodsModel = GoodsModel::find($id);
            $goodsModel->model_name = $name;
            $goodsModel->save();
            foreach ($Spec as $v){
                $tmpSpec = [
                    'gm_id'=>$goodsModel->id,
                    'spec_name'=>$v['SpecName'],
                ];
                $gms = GoodsModelSpec::create($tmpSpec);
                $tmpSpecValue = [];
                foreach ($v['SpecValues'] as $i){
                    $tmpSpecValue[] = [
                        'gms_id'=>$gms->id,
                        'spec_value'=>$i
                    ];
                }
                $specValues->saveAll($tmpSpecValue);
            }
            $tmpProp = [];
            //添加商品属性
            foreach ($Prop as $item){
                $tmpProp[] = [
                    'gm_id'=>$goodsModel->id,
                    'prop_name'=>$item['PropName'],
                    'prop_values'=>implode('_',$item['PropValues'])
                ];
            }
            $goodsModelProp->saveAll($tmpProp);
            Db::commit();
            return $this->resCode(200);
        }catch (\Exception $e){
            // 回滚事务
            Db::rollback();
            //return $this->resCode(500);
            return $this->resCode(500,$e->getMessage());
        }
    }

    /**
     * 增加商品类别(三级分类)
     * @return \think\response\Json
     */
    public function addGoodsClass(){
        $data = $this->getData('',false);
        //组装数据
        //if($data['checkId']==0)
        $res = [
            'two_id'=>$data['checkId'],
            'three_id'=>$data['checkId2'],
            'class_name'=>$data['class_name'],
            'class_desc'=>$data['class_desc'],
            'class_icon'=>$data['class_icon'],
            'sort'=>$data['sort']
        ];
        $goodsClass = new GoodsClass();
        $rs = $goodsClass->save($res);
        if($rs==1){
            return $this->resCode(200);
        }else{
            return $this->resCode(500);
        }
    }

    /**
     * 查询商品一级分类
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGoodsClassOne(){
        $goodsClass = new GoodsClass();
        $data = $goodsClass->where('two_id',0)->where('three_id',0)->field('id,class_name')->order('sort','desc')->select();
        return $this->resCode(200,$data);
    }

    /**
     * 根据一级id查询商品二级分类
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGoodsClassTwo(){
        $fid = $this->getData('id');
        $goodsClass = new GoodsClass();
        $data = $goodsClass->where('two_id',$fid)->where('three_id',0)->field('id,class_name')->select();
        return $this->resCode(200,$data);
    }

    /**
     * 查询商品分类数据列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGoodsClassList(){
        $goodsClassModel = new GoodsClass();
        //先查询顶级级分类
        $oneClass = $goodsClassModel->where('two_id',0)->order('sort','desc')->select();
        //查询顶级分类下的二级分类
        foreach ($oneClass as $v){
            $v['children'] =$goodsClassModel->where('two_id',$v['id'])->where('three_id',0)->order('sort','desc')->select();
            foreach ($v['children'] as $i){
                $i['children'] =$goodsClassModel->where('three_id',$i['id'])->order('sort','desc')->select();
            }
        }
        foreach ($oneClass as $k1=>$v1){
            if(count($v1['children'])==0){
                unset($oneClass[$k1]['children']);
                $oneClass[$k1]['disabled']=true;
            }else{
                foreach ($v1['children'] as $k2=>$v2){
                    if(count($v2['children'])==0){
                        unset($oneClass[$k1]['children'][$k2]['children']);
                        $oneClass[$k1]['children'][$k2]['disabled']=true;
                    }
                }
            }
        }
        return $this->resCode(200,$oneClass);
    }

    /**
     * 根据id重新设置商品的排序值
     * @return \think\response\Json
     */
    public function editGoodsClassSort(){
        $data = $this->getData(['sort','id']);
        GoodsClass::where('id',$data['id'])->update(['sort'=>$data['sort']]);
        return $this->resCode(200);
    }

    /**
     *根据id将商品类别删除或移入回收站(批量)
     * @return \think\response\Json
     */
    public function delGoodsClass(){
        $ids = $this->getData('id');
        $type = $this->getData('type');
        if($type==1){
            try{
                GoodsClass::destroy($ids,true);
            }catch (\Exception $e){
                return $this->resCode(204,'该分类下有商品,请将该分类商品移至其它分类');
            }
            return $this->resCode(200);
        }else{
            GoodsClass::destroy($ids);
            return $this->resCode(200);
        }
    }

    /**
     * 根据id查询分类详情
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGoodsClassById(){
        $id = $this->getData('id');
        $data = GoodsClass::find($id);
        return $this->resCode(200,$data);
    }

    /**
     * 根据请求查询对应的等级分类全部数据(level=1:查询所有一级分类，level=2:查询所有二级分类，level=3:查询所有三级分类)
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGoodsClassWhere(){
        $level=$this->getData('level');
        switch ($level){
            case 1:
                $data = GoodsClass::where('two_id',0)->select();
                break;
            case 2:
                $data = GoodsClass::where('three_id',0)->where('two_id','<>',0)->select();
                break;
            case 3:
                $data = GoodsClass::where('three_id','<>',0)->select();
                break;
            default:
                return $this->resCode(202,'level参数错误');
        }
        return $this->resCode(200,$data);
    }

    /**
     * 更新商品分类数据
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editGoodsClass(){
        //return $this->resCode(200);
        $data = $this->getData('',false);
        $goodsClassData = GoodsClass::find($data['id']);
        $res = [
            'id'=>$data['id'],
            'two_id'=>$data['two_id'],
            'three_id'=>$data['three_id'],
            'class_name'=>$data['class_name'],
            'class_icon'=>$data['class_icon'],
            'class_desc'=>$data['class_desc'],
            'sort'=>$data['sort']
        ];
        $goodsClassData->save($res);
        return $this->resCode(200);
    }

    /**
     * 添加商品服务
     * @return \think\response\Json
     */
    public function addGoodsServe(){
        $data = $this->getData(['serve_name','serve_desc']);
        $goodsServe = new GoodsServe();
        $goodsServe->save($data);
        return $this->resCode(200);
    }

    /**
     * 输出商品服务列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGoodsServeList(){
        $data = GoodsServe::order('create_time','desc')->select();
        return $this->resCode(200,$data);
    }

    /**
     * 根据id查询对应的商品服务
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGoodsServeById(){
        $id = $this->getData('id');
        $data = GoodsServe::find($id);
        return $this->resCode(200,$data);
    }

    /**
     * 修改商品服务
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editGoodsServe(){
        $data = $this->getData();
        $res = [
            'serve_name'=>$data['serve_name'],
            'serve_desc'=>$data['serve_desc']
        ];
        $goodsServe = GoodsServe::find($data['id']);
        $goodsServe->save($res);
        return $this->resCode(200);
    }

    /**
     * 删除商品服务(彻底删除)
     * @return \think\response\Json
     */
    public function delGoodsServe(){
        $id = $this->getData('id');
        $res = GoodsServe::destroy($id);
        if($res){
            return $this->resCode(200);
        }else{
            return $this->resCode(500);
        }
    }

    /**
     * 添加商品
     * @return \think\response\Json
     */
    public function addGoods(){
        $data = $this->getData('',false);
        // 启动事务
        Db::startTrans();
        try{
            //添加商品表
            $goodsData = [
                'goods_name'=>$data[0]['goods_name'],
                'promotion'=>$data[0]['promotion'],
                'goods_class_id'=>$data[0]['goods_class_id'],
                'goods_class'=>implode(',',$data[0]['goods_class']),
                'goods_serve'=>implode(',',$data[0]['goods_serve']),
                'inventory'=>$data[1]['inventory'],
                'warning_inventory'=>$data[1]['warning_inventory'],
                'goods_price'=>$data[1]['goods_price'],
                'goods_line_price'=>$data[1]['goods_line_price'],
                'sold'=>$data[1]['sold'],
                'limitation'=>$data[1]['limitation'],
                'satr_num'=>$data[1]['satr_num'],
                'is_mail'=>$data[5]['is_mail'],
                'mail_price'=>$data[5]['mail_price'],
                'is_spec'=>$data[2][1]==-1?0:1,
                'goods_sort'=>$data[5]['goods_sort'],
                'status'=>$data[5]['status'],
                'goods_json_data'=>json_encode($data),
            ];
            $goodsModel = new GoodsM();
            $goodsModel->save($goodsData);
            //添加商品sku表
            $modelGoodsSku = new GoodsSku();
            if($data[2][1]==-1){
                $skuData = [
                'goods_id'=>$goodsModel->id,
                'price'=>$data[2][0]['price'],
                'cost_price'=>$data[2][0]['cost_price'],
                'repertory'=>$data[2][0]['repertory'],
                'sku_img'=>$data[2][0]['sku_img'],
                ];
                $modelGoodsSku->save($skuData);
            }else{
                $skuAllData = [];
                foreach ($data[2][0] as $skuItem){
                    $skuAllData[]=[
                        'goods_id'=>$goodsModel->id,
                        'price'=>$skuItem['price'],
                        'cost_price'=>$skuItem['cost_price'],
                        'repertory'=>$skuItem['repertory'],
                        'sku_img'=>$skuItem['sku_img'],
                        'sku_values'=>$skuItem['sku_value']
                    ];
                }
                $modelGoodsSku->saveAll($skuAllData);
            }
            //添加商品属性表
            $modelGoodsProp = new GoodsProp();
            $propData = [];
            foreach ($data[3][0] as $propItem){
                if($propItem['value']!=null){
                    $propData[] = [
                        'goods_id'=>$goodsModel->id,
                        'prop_name'=>$propItem['prop_name'],
                        'prop_value'=>$propItem['value']
                    ];
                }
            }
            $modelGoodsProp->saveAll($propData);

            //添加商品细节
            $modelGoodsDetails = new GoodsDetails();
            $details = [
                'goods_id'=>$goodsModel->id,
                'goods_icon'=>$data[4][0]['goods_icon'],
                'goods_photolist'=>implode(',',$data[4][0]['goods_photoList']),
                'goods_details'=>$data[4][0]['goods_details'],
                'goods_label'=>$data[5]['goods_label']
            ];
            $modelGoodsDetails->save($details);
            // 提交事务
            Db::commit();
            return $this->resCode(200);
        }catch (\Exception $e){
            // 回滚事务
            Db::rollback();
            return $this->resCode(500);
            //return $this->resCode(500,$e->getMessage());
        }
    }

    /**
     * 输出商品列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGoodsList(){
        $modelGoods = new GoodsM();
        $modelDetails = new GoodsDetails();
        $res = $modelGoods->with('goodsSku')
            ->field('id,goods_name,goods_price,inventory,status,goods_sold,promotion,create_time')
            ->order('create_time','desc')
            ->select();
        foreach ($res as $key=>$goods){
            $res[$key]['goods_icon'] = $modelDetails->where('goods_id',$goods['id'])->value('goods_icon');
        }
        return $this->resCode(200,$res);
    }

    /**
     * 更改商品状态(上架下架)
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function setGoodsStatus(){
        $id = $this->getData('id');
        $data = GoodsM::find($id);
        if($data['status']){
            $data->status = 0;
        }else{
            $data->status = 1;
        }
        $data->save();
        return $this->resCode(200);
    }

    /**
     * 查询商品详情，输出json格式用于商品编辑
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGoodsByIdJson(){
        $id = $this->getData('id');
        $res = GoodsM::field('goods_json_data')->find($id);
        return $this->resCode(200,json_decode($res['goods_json_data']));
    }

    /**
     * 修改商品详情
     * @return \think\response\Json
     */
    public function editGoods(){
        $id = $this->getData('id');
        $data = $this->getData('',false);
        unset($data['id']);

        // 启动事务
        Db::startTrans();
        try {
            //修改商品
            $goodsData = [
                'goods_name'=>$data[0]['goods_name'],
                'promotion'=>$data[0]['promotion'],
                'goods_class_id'=>$data[0]['goods_class_id'],
                'goods_class'=>implode(',',$data[0]['goods_class']),
                'goods_serve'=>implode(',',$data[0]['goods_serve']),
                'inventory'=>$data[1]['inventory'],
                'warning_inventory'=>$data[1]['warning_inventory'],
                'goods_price'=>$data[1]['goods_price'],
                'goods_line_price'=>$data[1]['goods_line_price'],
                'sold'=>$data[1]['sold'],
                'limitation'=>$data[1]['limitation'],
                'satr_num'=>$data[1]['satr_num'],
                'is_mail'=>$data[5]['is_mail'],
                'mail_price'=>$data[5]['mail_price'],
                'is_spec'=>$data[2][1]==-1?0:1,
                'goods_sort'=>$data[5]['goods_sort'],
                'status'=>$data[5]['status'],
                'goods_json_data'=>json_encode($data),
            ];
            GoodsM::update($goodsData,['id'=>$id]);
            GoodsSku::where('goods_id',$id)->delete();
            GoodsProp::where('goods_id',$id)->delete();
            //修改商品sku表
            $modelGoodsSku = new GoodsSku();
            if($data[2][1]==-1){
                $skuData = [
                    'goods_id'=>$id,
                    'price'=>$data[2][0]['price'],
                    'cost_price'=>$data[2][0]['cost_price'],
                    'repertory'=>$data[2][0]['repertory'],
                    'sku_img'=>$data[2][0]['sku_img'],
                ];
                $modelGoodsSku->save($skuData);
            }else{
                $skuAllData = [];
                foreach ($data[2][0] as $skuItem){
                    $skuAllData[]=[
                        'goods_id'=>$id,
                        'price'=>$skuItem['price'],
                        'cost_price'=>$skuItem['cost_price'],
                        'repertory'=>$skuItem['repertory'],
                        'sku_img'=>$skuItem['sku_img'],
                        'sku_values'=>$skuItem['sku_value']
                    ];
                }
                $modelGoodsSku->saveAll($skuAllData);
            }

            //修改商品属性表
            $modelGoodsProp = new GoodsProp();
            $propData = [];
            foreach ($data[3][0] as $propItem){
                if($propItem['value']!=null){
                    $propData[] = [
                        'goods_id'=>$id,
                        'prop_name'=>$propItem['prop_name'],
                        'prop_value'=>$propItem['value']
                    ];
                }
            }
            $modelGoodsProp->saveAll($propData);

            //修改商品细节
            $details = [
                'goods_icon'=>$data[4][0]['goods_icon'],
                'goods_photolist'=>implode(',',$data[4][0]['goods_photoList']),
                'goods_details'=>$data[4][0]['goods_details'],
                'goods_label'=>$data[5]['goods_label']
            ];
            GoodsDetails::update($details,['goods_id'=>$id]);
            // 提交事务
            Db::commit();
            return $this->resCode(200);
        }catch (\Exception $e){
            // 回滚事务
            Db::rollback();
            //return $this->resCode(500);
            return $this->resCode(500,$e->getMessage());
        }
    }

    /**
     * @return \think\response\Json
     * @throws \Exception
     */
    public function editGoodsSku(){
        $id = $this->getData('id');
        $data = $this->getData('',false);
        unset($data['id']);

        $modelGoods = GoodsM::find($id);
        $sku = json_decode($modelGoods['goods_json_data']);
        $sku[2]=$data;
        $modelGoods->goods_json_data = json_encode($sku);
        $modelGoods->save();

        GoodsSku::where('goods_id',$id)->delete();
        GoodsProp::where('goods_id',$id)->delete();
        //修改商品sku表
        $modelGoodsSku = new GoodsSku();
        if($data[1]==-1){
            $skuData = [
                'goods_id'=>$id,
                'price'=>$data[0]['price'],
                'cost_price'=>$data[0]['cost_price'],
                'repertory'=>$data[0]['repertory'],
                'sku_img'=>$data[0]['sku_img'],
            ];
            $modelGoodsSku->save($skuData);
        }else{
            $skuAllData = [];
            foreach ($data[0] as $skuItem){
                $skuAllData[]=[
                    'goods_id'=>$id,
                    'price'=>$skuItem['price'],
                    'cost_price'=>$skuItem['cost_price'],
                    'repertory'=>$skuItem['repertory'],
                    'sku_img'=>$skuItem['sku_img'],
                    'sku_values'=>$skuItem['sku_value']
                ];
            }
            $modelGoodsSku->saveAll($skuAllData);
        }
        return $this->resCode(200);
    }

    /**
     * 删除商品或加入回收站
     * @return \think\response\Json
     */
    public function delGoods(){
        $ids = $this->getData('id');
        $type = $this->getData('type');
        if($type==1){
            GoodsM::destroy($ids,true);
            return $this->resCode(200);
        }else{
            GoodsM::destroy($ids);
            return $this->resCode(200);
        }
    }

    /**
     * 搜索商品根据商品名称or商品标签
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function searchGoods(){
        $search = $this->getData('search');
        $modelGoods = new GoodsM();
        $modelDetails = new GoodsDetails();
        $res = $modelGoods->with('goodsSku')
            ->field('id,goods_name,goods_price,inventory,status,goods_sold,promotion,create_time')
            ->where('goods_name','like','%'.$search.'%')
            ->order('create_time','desc')
            ->select();
        if($res->count()==0){
            $details = $modelDetails->where('goods_label','like','%'.$search.'%')->select();
            $res = [];
            foreach ($details as $labelItel){
                $res[] = $modelGoods->with('goodsSku')
                    ->field('id,goods_name,goods_price,inventory,status,goods_sold,promotion,create_time')
                    ->find($labelItel['goods_id']);
            }
        }
        foreach ($res as $key=>$goods){
            $res[$key]['goods_icon'] = $modelDetails->where('goods_id',$goods['id'])->value('goods_icon');
        }

        return $this->resCode(200,$res);
    }

    /**
     * 查询软删除的商品&&商品模型&&商品分类
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getGoodsRecycle(){
        $res['goods'] = GoodsM::onlyTrashed()->select();
        $res['goodsClass'] = GoodsClass::onlyTrashed()->select();
        $res['goodsModel'] = GoodsModel::onlyTrashed()->select();
        return $this->resCode(200,$res);
    }

    /**
     * 调用此接口可以恢复商品，品模型，商品分类 type[goods:商品;goodsModel:商品模型;goodsClass:商品分类]
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @noinspection PhpUndefinedMethodInspection
     */
    public function restoreGoods(){
        $type = $this->getData('type');
        $ids = $this->getData('ids');
        if(is_array($ids)){
            if($type=='goods'){
                foreach ($ids as $goodsItem){
                    $goodslist = GoodsM::onlyTrashed()->find($goodsItem);
                    $goodslist->restore();
                }
                return $this->resCode(200);
            }
            if($type=='goodsModel'){
                foreach ($ids as $goodsModelItem){
                    $goodsModellist = GoodsModel::onlyTrashed()->find($goodsModelItem);
                    $goodsModellist->restore();
                }
                return $this->resCode(200);
            }
            if($type=='goodsClass'){
                foreach ($ids as $goodsClassItem){
                    $goodsClasslist = GoodsClass::onlyTrashed()->find($goodsClassItem);
                    $goodsClasslist->restore();
                }
                return $this->resCode(200);
            }
        }else{
            if($type=='goods'){
                $goodslist = GoodsM::onlyTrashed()->find($ids);
                $goodslist->restore();
                return $this->resCode(200);
            }
            if($type=='goodsModel'){
                $goodsModellist = GoodsModel::onlyTrashed()->find($ids);
                $goodsModellist->restore();
                return $this->resCode(200);
            }
            if($type=='goodsClass'){
                $goodsClasslist = GoodsClass::onlyTrashed()->find($ids);
                $goodsClasslist->restore();
                return $this->resCode(200);
            }
        }
        return $this->resCode(500);
    }
}
