<?php
declare (strict_types = 1);

namespace app\v1\controller;
use think\facade\Request;
/**`
 * Class Index
 * @package app\v1\controller
 */
class Index extends Restful
{
    /**
     * @return string
     */
    public function index()
    {
        dd($this->getData('',true));
    }
}
