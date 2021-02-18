<?php
declare (strict_types = 1);

namespace app\v1\controller;

use think\Request;

/**
 * 处理商品模块的控制器
 *
 * addGoodsModel:添加商品模型
 * Class Goods
 * @package app\v1\controller
 */
class Goods extends Restful
{
    public function addGoodsModel(Request $request){
        $name = $this->getData('name');
        $Spec = $this->getData('Spec');
        $Prop = $this->getData('Prop');

    }
}
