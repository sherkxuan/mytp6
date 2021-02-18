<?php
declare (strict_types = 1);

namespace app\v1\model;

use think\Model;
use think\model\concern\SoftDelete;
/**
 * Class Platform
 * @package app\v1\model
 */
class Platform extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    /**
     * 输出类型
     * @param $value
     * @return string
     */
    /*public function getTypeAttr($value)
    {
        $status = [1=>'微信公众号',2=>'微信小程序'];
        return $status[$value];
    }*/
}
