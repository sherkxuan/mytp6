<?php /** @noinspection PhpFunctionNamingConventionInspection */

// 应用公共文件
use Firebase\JWT\JWT;
use think\facade\Cache;
use think\facade\Request;
//生成token
/**
 * @param $uid
 * @return string
 */
function createToken($uid)
{
    $secret = 'sherk668';      //密匙
    $payload = [
        'iss'=>'zx',                //签发人(官方字段:非必需)
        'exp'=>time()+3600*24*1,     //过期时间(官方字段:非必需)
        'aud'=>'user',              //受众(官方字段:非必需)
        'nbf'=>time(),               //生效时间(官方字段:非必需)
        'iat'=>time(),               //签发时间(官方字段:非必需)
        'uid'=>$uid,        //自定义字段
    ];
    /** @noinspection PhpRedundantOptionalArgumentInspection */
    $token = JWT::encode($payload,$secret,'HS256');
    Cache::set('token',$token.Request::ip(),3600*24*1);
    return $token;
}
//验证token
/**
 * @param $token
 * @return array|mixed
 */
function checkToken($token)
{
    $res = Cache::get('token');
    if($res!=$token.Request::ip()){
        return ['code'=>201,'data'=>'Token已过期!'];
    }
    try{
        $Result = JWT::decode($token,'sherk668',['HS256']);

        $Result =json_encode($Result);
        return json_decode($Result, true);
    }
    catch (Exception $e)
    {
        return ['code'=>201,'data'=>'Token已过期!'];
    }
}
