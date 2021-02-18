<?php
declare (strict_types = 1);

namespace app\v2\controller;

use EasyWeChat\Factory;
class Index
{
    public function index()
    {
        $config = [
            'app_id' => 'wx5a954fbcc9b55f79',
            'secret' => '133021129ccdf46538ccbeab24746518',
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
            'token' => 'Sherk'
        ];
        $app = Factory::officialAccount($config);
        $response = $app->user->get('opAc9533_xtw8gal58kLTX0tNHKs');
        return json($response);
    }
}
