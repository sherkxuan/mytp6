<?php
declare (strict_types = 1);

namespace app\v1\model;

use think\Model;

/**
 * @mixin Model
 */
class GoodsModelProp extends Model
{
    /**
     * 将属性值转为数组
     * @param $value
     * @return false|string[]
     */
    public function getPropValuesAttr($value)
    {
        return explode('_',$value);
    }
}
