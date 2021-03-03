<?php
declare (strict_types = 1);

namespace app\v1\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class Goods extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    /**
     * 一对多关联sku表
     * @return \think\model\relation\HasMany
     */
    public function goodsSku()
    {
        return $this->hasMany(GoodsSku::class,'goods_id','id');
    }
}
