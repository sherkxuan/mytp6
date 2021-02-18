<?php
/*
 *
 *           ┌─┐       ┌─┐
 *        ┌──┘ ┴───────┘ ┴──┐
 *        │                 │
 *        │       ───       │
 *        │  ─┬┘       └┬─  │
 *        │                 │
 *        │       ─┴─       │
 *        │                 │
 *        └───┐         ┌───┘
 *            │         │
 *            │         │
 *            │         │
 *            │         └──────────────┐
 *            │                        │
 *            │                        ├─┐
 *            │                        ┌─┘
 *            │                        │
 *            └─┐  ┐  ┌───────┬──┐  ┌──┘
 *              │ ─┤ ─┤       │ ─┤ ─┤
 *              └──┴──┘       └──┴──┘
 *                  神兽保佑
 *                  代码无BUG!
 *
 */

declare (strict_types = 1);

namespace app\v2\controller;

use app\BaseController;
use EasyWeChat\Factory;


/**
 * 处理微信公众号的事件
 *
 * 查询用户列表:getUserList()
 * 根据openid查询单个用户信息:getUserByid()
 * 根据openid修改用户备注:editUserRemark()
 * 用户加入黑名单:editUserBlock()
 * 根据openid移除黑名单用户:editUserUnblock()
 * 获取黑名单用户列表:getUserBlock()
 * 获取标签列表:getUserTagList()
 * 创建一个新的标签:createTag()
 * 根据标签ID修改标签:editUserTag()
 * 根据ID删除标签:delUserTag()
 * 根据用户openid获取用户的所有标签:getUserByIdTag()
 * 根据标签ID获取用户openid列表:getTagByIdUserList()
 * 将用户添加到标签:createUserTag()
 * 根据openid将用户移除指定的标签:unTagUsers()
 * 获取当前自定义菜单列表:getMenuList()
 *
 * @package app\v2\controller
 */
class OfficialAccount extends BaseController
{
    /**
     * 查询用户列表
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserList(){
        /*
         * 这里的config到时候用一个钩子函数勾起
         * 首先根据数据库id查询相应的app_id和secre，然后根据查询的再查询相应公众号的相关信息
         * */
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $userOpenidList = $app->user->list($nextOpenId = null);
        if(isset($userOpenidList['data'])){
            $response = $app->user->select($userOpenidList['data']['openid']);
        }else{
            $response['user_info_list']=null;
        }
        return json(['code'=>200,'data'=>$response['user_info_list']]);
    }

    /**
     * 根据openid查询单个用户信息
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function getUserByid(){
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $openid = 'opAc9533_xtw8gal58kLTX0tNHKs';
        $response = $app->user->get($openid);
        return json(['code'=>200,'data'=>$response]);
    }

    /**
     * 根据openid修改用户备注
     * 备注最多30字
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function editUserRemark(){
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $openid = 'opAc9533_xtw8gal58kLTX0tNHKs';
        $remark = '这里是修改了备注';
        $res = $app->user->remark($openid, $remark);
        if($res['errmsg']==='ok'){
            return json(['code'=>200,'data'=>'SUCCESS']);
        }else{
            return json(['code'=>500,'data'=>'该用户没有关注公众号']);
        }
    }

    /**
     * 用户加入黑名单
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function editUserBlock(){
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $openid = ['opAc9533_xtw8gal58kLTX0tNHKs'];
        $res = $app->user->block($openid);//这里可以传入数组
        if($res['errmsg']==='ok'){
            return json(['code'=>200,'data'=>$res]);
        }else{
            return json(['code'=>500,'data'=>'错误']);
        }
    }

    /**
     * 根据openid移除黑名单用户
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function editUserUnblock(){
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $openid = ['opAc9533_xtw8gal58kLTX0tNHKs'];
        $res = $app->user->unblock($openid);//这里可以是数组
        if($res['errmsg']==='ok'){
            return json(['code'=>200,'data'=>$res]);
        }else{
            return json(['code'=>500,'data'=>'错误']);
        }
    }

    /**
     * 获取黑名单用户列表
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserBlock(){
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $userOpenidList = $app->user->blacklist($beginOpenid = null);
        if(isset($userOpenidList['data'])){
            $response = $app->user->select($userOpenidList['data']['openid']);
        }else{
            $response['user_info_list']=null;
        }

        return json(['code'=>200,'data'=>$response['user_info_list']]);
    }

    /**
     * 获取标签列表
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function getUserTagList(){
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $res = $app->user_tag->list();
        return json(['code'=>200,'data'=>$res['tags']]);
    }

    /**
     * 创建一个新的标签
     * 不得超过6个汉字或12个字母
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createTag(){
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $name= '这里新标签7';//不得超过6个汉字或12个字母
        $res = $app->user_tag->create($name);
        if(isset($res['tag'])){
            return json(['code'=>200,'data'=>'SUCCESS']);
        }else{
            return json(['code'=>500,'data'=> '标签重复']);
        }
    }

    /**
     * 根据标签ID修改标签
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function editUserTag(){
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $name= '这里新签22';//不得超过6个汉字或12个字母
        $tagId = 1;
        $res = $app->user_tag->update($tagId, $name);
        echo $res['errcode'];
        switch ($res['errcode']){
            case 0:
                $msg = 'SUCCESS';
                break;
            case 45058:
                $msg = '不允许修改星标标签';
                break;
            case 45159:
                $msg = '修改标签不存在';
                break;
            case 45157:
                $msg = '修改的标签重复';
                break;
            default:
                $msg = '修改失败';
                break;
        }
        return json(['code'=>200,'data'=>$msg]);
    }

    /**
     * 根据ID删除标签
     * 只能单个删除
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delUserTag(){
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $tagId = 100;
        $res = $app->user_tag->delete($tagId);
        if($res['errcode']===0){
            return json(['code'=>200,'data'=>'SUCCESS']);
        }else{
            return json(['code'=>200,'data'=>'不允许删除该标签']);
        }

    }

    /**
     * 根据用户openid获取用户的所有标签
     * 返回的是标签ID
     * 根据ID遍历出对应的标签信息
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUserByIdTag(){
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $openid = 'opAc9533_xtw8gal58kLTX0tNHKs';
        $userTags = $app->user_tag->userTags($openid);

        $res = $app->user_tag->list();
        $arr = [];
        foreach ($res['tags'] as $v){
            foreach ($userTags['tagid_list'] as $s){
                if($v['id'] === $s){
                    $arr[] = $v;
                }
            }
        }

        return json(['code'=>200,'data'=>$arr]);
    }

    /**
     * 根据标签ID获取用户openid列表
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTagByIdUserList(){
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $tagId = 104;
        $res = $app->user_tag->usersOfTag($tagId, $nextOpenId = '');
        if(isset($res['data'])){
            $data = $app->user->select($res['data']['openid']);
        }else{
            $data['user_info_list'] = [];
        }
        return json(['code'=>200,'data'=>['userList'=>$data['user_info_list'],'count'=>$res['count']]]);
    }

    /**
     * 将用户添加到标签
     * openid只能是数组
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createUserTag(){
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $openIds = ['opAc9533_xtw8gal58kLTX0tNHKs','opAc95z1WdNgCMBKomHDnZytnth0'];
        $tagId = 103;
        $res = $app->user_tag->tagUsers($openIds, $tagId);
        if($res['errcode']===0){
            return json(['code'=>200,'data'=>'SUCCESS']);
        }else{
            return json(['code'=>200,'data'=>'添加失败']);
        }
    }

    /**
     * 根据openid将用户移除指定的标签
     * 移除的openid必须是一个数组
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function unTagUsers(){
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $openIds = ['opAc9533_xtw8gal58kLTX0tNHKs','opAc95z1WdNgCMBKomHDnZytnth0'];
        $tagId = 10;
        $res = $app->user_tag->untagUsers($openIds, $tagId);
        if($res['errcode']===0){
            return json(['code'=>200,'data'=>'SUCCESS']);
        }else{
            return json(['code'=>200,'data'=>'移除失败']);
        }
    }

    /**
     * 获取当前自定义菜单列表
     * @return \think\response\Json
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function getMenuList(){
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            'response_type' => 'array',
        ];
        $app = Factory::officialAccount($config);
        $res = $app->menu->current();
        return json(['code'=>200,'data'=>$res]);
    }
}
