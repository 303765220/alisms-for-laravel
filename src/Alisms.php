<?php
/**
 * Created by PhpStorm.
 * User: hw
 * Date: 2017/12/14
 * Time: 12:53
 */
namespace Xiaoyi\Ali;
use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Illuminate\Support\Facades\Redis;

// 加载区域结点配置
Config::load();

class Alisms{

    static $acsClient = null;

    /**
     * 取得AcsClient
     *
     * @return DefaultAcsClient
     */
    public static function getAcsClient() {
        //产品名称:云通信流量服务API产品,开发者无需替换
        $product = "Dysmsapi";
        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";
        // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
        $accessKeyId = "LTAIrqtsJKSEyD6y"; // AccessKeyId
        $accessKeySecret = "0tN6Io5jn0DjZd7kVBwTkF4EjPX33k"; // AccessKeySecret
        // 暂时不支持多Region
        $region = "cn-hangzhou";
        // 服务结点
        $endPointName = "cn-hangzhou";
        if(static::$acsClient == null) {
            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);
            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }


    /**
     * 发送短信
     * @return stdClass
     */
    public static function send($phone,$template,$data) {
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();
        // 必填，设置短信接收号码
        $request->setPhoneNumbers($phone);
        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName("百米贩科技");
        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($template);
        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode($data));
        $acsResponse = static::getAcsClient()->getAcsResponse($request);
        return $acsResponse;
    }


    /**
     * 验证验证码是否正确
     * @param $phone
     * @param $type 1注册2登录3找回密码
     */
    public static function check($phone,$type,$code){
        switch ($type){
            case 1:
                $redisKey = 'sjs:reg:'.$phone;
                break;
            case 2:
                $redisKey = 'sjs:login:'.$phone;
                break;
            case 3:
                $redisKey = 'sjs:password:'.$phone;
                break;
        }
        if(Redis::exists($redisKey)){//存在
            if(Redis::get($redisKey) == $code){
                return true;
            }
        }
        return false;
    }
}