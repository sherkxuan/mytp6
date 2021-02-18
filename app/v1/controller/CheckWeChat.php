<?php
declare (strict_types = 1);

namespace app\v1\controller;


use think\facade\Cache;
use EasyWeChat\Factory;
/**
 * 校验控制器，用来验证微信端是否接入成功
 * Class CheckWeChat
 * @package app\v1\controller
 */
class CheckWeChat extends Restful
{
    /**
     * 校验微信端是否接入成功
     * @return \think\response\Json
     */
    public function verifyWechat(){
        //$type = Request::param('type');
        //$code = Request::param('code');
        $type = $this->getData('type');
        $code = $this->getData('code');
        //if(!($code || $type))return  $this->resCode(202);
        if($type==1){
            return $this->tokenCheck($code);
        }
        return $this->resCode(500);
    }

    /**
     * 缓存临时的微信验证文件
     * 只缓存一个小时，之后重新调用该接口产生新的缓存
     * @return \think\response\Json
     */
    public function getCheckUrlCode(){
        //$data = Request::param();
        $data = $this->getData('',false);
        //if(!isset($data['original_id']))return $this->resCode(202);
        $original_id = $data['original_id'];
        $code = md5($original_id);
        Cache::set($code,$data,3600);
        return $this->resCode(200,$code);
    }

    /**
     * 微信token专用验证
     * @param $code
     * @return false|mixed
     */
    public function tokenCheck($code){
        $signature = $this->getData('signature');
        $timestamp = $this->getData('timestamp');
        $nonce = $this->getData('nonce');
        $echostr = $this->getData('echostr');
        //if(!($signature || $timestamp || $nonce || $echostr))return $this->resCode(202);
        $data = Cache::get($code);
        if(!$data){
            return $this->resCode(202);
        }
        $token = $data['Token'];
        $tmpArr = [$token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            Cache::set($data['Token'],$data['Token'],3600);
            return $echostr;
        }else{
            return $this->resCode(205);
        }
    }

    /**
     * 判断微信是否验证成功
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function isTokenOk(){
        $type = $this->getData('type');
        $token = $this->getData('Token');
        //if(!($type || $token))return $this->resCode(202);
        if($type==1) {
            if ($token == Cache::get($token)) {
                //Cache::delete($token);
                return $this->resCode(200);
            } else {
                //new P
                return $this->resCode(200, 'ERROR');
            }
        }else{
            return $this->isMiniApp($token);
        }
    }

    /**
     * 验证小程序是否成功接入
     * @param $token
     * @return \think\response\Json
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function isMiniApp($token){
        $data = Cache::get(md5($token));
        if(!$data)return $this->resCode(200, '请手动验证');
        $config = [
            'app_id' => $data['app_id'],
            'secret' => $data['app_secret'],

            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
        ];
        $app = Factory::miniProgram($config);

        $access = $app->access_token;
        try {
            $accessToken = $access->getToken();
        }catch (\Exception $e){
            return $this->resCode(200, $e->getMessage());
        }
        if(isset($accessToken['access_token'])){
            Cache::delete(md5($token));
            return $this->resCode(200);
        }else{
            return $this->resCode(200, 'ERROR');
        }
    }
}
