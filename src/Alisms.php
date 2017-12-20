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
use Illuminate\Config\Repository;
// 加载区域结点配置
Config::load();

class Alisms{

    protected $config;
    static $acsClient = null;

    public function __construct(Repository $config){
        $this->config = $config;
    }
    /**
     * 取得AcsClient
     *
     * @return DefaultAcsClient
     */
    public function getAcsClient() {
        //产品名称:云通信流量服务API产品,开发者无需替换
        $product = "Dysmsapi";
        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";
        $accessKeyId = $this->config->get('ali.accessKeyId'); // AccessKeyId
        $accessKeySecret = $this->config->get('ali.accessKeySecret'); // AccessKeySecret
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
    public function send($phone,$template,$data,$sign = '') {
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();
        // 必填，设置短信接收号码
        $request->setPhoneNumbers($phone);
        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName(
            $sign == '' ? $this->config->get('ali.SignName') : $sign
        );
        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode($template);
        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode($data));
        $acsResponse = $this->getAcsClient()->getAcsResponse($request);

        $result = json_decode(json_encode($acsResponse), true);
        if($result['Code'] == 'OK'){
            return 'OK';
        }else{
            return $result['Message'];
        }
    }


    /**
     * 验证验证码是否正确
     * @param $phone
     * @param $type 1注册2登录3找回密码
     */
    public function validation($phone,$type,$code){
        $rdsPrefix = $this->config->get('ali.rdsPrefix');
        switch ($type){
            case 1:
                $redisKey = $rdsPrefix.':reg:'.$phone;
                break;
            case 2:
                $redisKey = $rdsPrefix.':login:'.$phone;
                break;
            case 3:
                $redisKey = $rdsPrefix.':password:'.$phone;
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