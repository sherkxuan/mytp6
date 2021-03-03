<?php
declare (strict_types = 1);

namespace app\v1\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * @mixin Model
 */
class GoodsModel extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    /**
     * 一对多关联外键方法
     * @return \think\model\relation\HasMany
     */
    public function goodsModelSpec()
    {
        return $this->hasMany(GoodsModelSpec::class,'gm_id','id');
    }

    /**
     * 一对多关联外键方法
     * @return \think\model\relation\HasMany
     */
    public function goodsModelProp()
    {
        return $this->hasMany(GoodsModelProp::class,'gm_id','id');
    }
}
