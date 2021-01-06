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
     * @return \think\response\Json
     */
    public function index()
    {
        return $this->resCode(200,Request::domain());
    }
}
