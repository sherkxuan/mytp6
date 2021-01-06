<?php
declare (strict_types = 1);

namespace app\v1\controller;

use think\facade\Request;

/**
 * Class Upload
 * @package app\v1\controller
 */
class Upload extends Restful
{
    /**
     * @return \think\response\Json
     */
    public function upload01(){
        // 获取表单上传文件
        $files = request()->file('img');
        if(!isset($files))return $this->resCode(202);
        if(!is_array($files)){
            //1Mb = 1048576b
            try {
                validate(['file' => ['fileSize:10485760', 'fileExt:jpg,png,gif']])->check(['file' => $files]);
                $savename =Request::domain().'/uploads/'. \think\facade\Filesystem::disk('public')->putFile('', $files);
            } catch (\think\exception\ValidateException $e) {
                //var_dump( $e->getMessage());
                return $this->resCode(204, $e->getMessage());
            }
        }else {
            try {
                validate(['file' => ['fileSize:10485760', 'fileExt:jpg,png,gif']])->check(['file' => $files]);
                $savename = [];
                foreach ($files as $file) {
                    $savename[] = \think\facade\Filesystem::disk('public')->putFile('uploads', $file);
                }
            } catch (\think\exception\ValidateException $e) {
                return $this->resCode(204, $e->getMessage());
            }
        }
        return $this->resCode(200,$savename);
    }
}
