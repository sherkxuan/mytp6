<?php
declare (strict_types = 1);

namespace app\v1\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * @mixin \think\Model
 */
class GoodsClass extends Model
{
    //
    use SoftDelete;
    protected $deleteTime = 'delete_time';
}
