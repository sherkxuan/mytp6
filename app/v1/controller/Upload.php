<?php
declare (strict_types = 1);

namespace app\v1\controller;

use think\facade\Request;
use think\facade\Db;

/**
 * 上传文件类
 * Class Upload
 * @package app\v1\controller
 */
class Upload extends Restful
{
    /**
     * 01版本，只允许上传名称为img的单个文件或或文件夹，不允许携带其它参数
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

    /**
     * 02版本，对上传进行异常捕获，不允许上传空文件，目前只支持图片上传
     * @return \think\response\Json
     */
    public function upload02(){
        //获取文件类别
        //$type = request()->param('file_type');
        $type = $this->getData('file_type');
        //if(!isset($type))return $this->resCode(202);

        $type_array = ['image','video','file','audio'];//后期判断文件后缀是否是上传的类型
        if(!in_array($type,$type_array))return $this->resCode(203,'file_type参数错误');
        // 获取表单上传文件
        try {
            $files = request()->file('files');
        }catch (\Exception $e){
            return $this->resCode(202,$e->getMessage());
        }
        if(!isset($files))return $this->resCode(202,'files参数错误');
        if(!is_array($files)){
            //1Mb = 1048576b（限制文件大小(单位b）
            try {
                validate(['file' => ['fileSize:10485760','fileMime:image/jpeg,image/png','fileExt:jpg,png,gif,jpeg']])->check(['file' => $files]);
                $file_info = $this->operation($files,$type);

            } catch (\think\exception\ValidateException $e) {
                //var_dump( $e->getMessage());
                return $this->resCode(204, $e->getMessage());
            }
        }else {
            try {
                validate(['file' => ['fileSize:10485760','fileMime:image/jpeg,image/png','fileExt:jpg,png,gif,jpeg']])->check(['file' => $files]);
                $file_info = [];
                foreach ($files as $file) {
                    $file_info[] = $this->operation($file,$type);
                }
            } catch (\think\exception\ValidateException $e) {
                return $this->resCode(204, $e->getMessage());
            }
        }
        return $this->resCode(200,$file_info);
    }

    /**
     * 处理图片事务操作
     * 更新到数据库
     * @param $files
     * @param $type
     * @return array
     */
    private function operation($files,$type){
        $type_array = ['image','video','file','audio'];
        foreach ($type_array as $k => $v){
            if($v===$type) $type_key = $k+1;
        }
        $ym = Request::domain();
        $tmp_path = 'uploads/'. \think\facade\Filesystem::disk('public')->putFile('', $files);
        //文件根路径
        $file_path=root_path().'public/'.$tmp_path;
        //替换路径分隔符
        $file_path = str_replace('\\','/',$file_path);
        //文件网络路径
        $savename =$ym.'/uploads/'. \think\facade\Filesystem::disk('public')->putFile('', $files);
        $savename = str_replace('\\','/',$savename);
        //获取文件信息
        $file_info=[
            'name'=>$files->getOriginalName(),
            'extension'=>substr($files->getOriginalName(),strripos($files->getOriginalName(),'.')),
            'size'=>$files->getSize(),
            'upload_ip'=>$this->request->ip(),
            'specific_type'=>$files->getMime(),
            'upload_type'=>$type_key,
            'path'=>$file_path,
            'base_url'=>$savename,
            'create_time'=>time()
        ];

        //数据库操作，将图片路径插入到数据库
        $id = Db::name('file_uploads')->insertGetId($file_info);

        return ['id'=>$id,'name'=>$files->getOriginalName(),'url'=>$savename];
    }
}
