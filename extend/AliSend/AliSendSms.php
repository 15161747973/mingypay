<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/23
 * Time: 10:03
 */


namespace AliSend;

//引入sdk的命名空间
use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\SendBatchSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;
use think\Db;

//引入autoload.php，EXTEND_PATH就是指tp5根目录下的extend目录，系统自带常量。alisms为我们复制api_sdk过来后更改的目录名称
require_once EXTEND_PATH . 'AliSend/vendor/autoload.php';
Config::load();             //加载区域结点配置


class AliSendSms
{
    static $acsClient = null;
    public $accessKeyId = '';               //阿里云短信获取的accessKeyId
    public $accessKeySecret = '';           //阿里云短信获取的accessKeySecret
    public $signName = '';
    public $templateCode = '';

    public function __construct()
    {
        $this->accessKeyId = config('site.accessKeyId');
        $this->accessKeySecret = config('site.accessKeySecret');
        $this->signName = config('site.signName');
        $this->templateCode = config('site.templateCode');
    }

    /**
     * 发送短信
     *  $mobile  手机号码
     *  $templateParam  短信模版里的参数  例如 array('code'=>'1234')
     *  $type  对应的短信模版id   可以参考 get_sms_temp_id  进行选择
     * @return stdClass
     */
    public function send($mobile, $templateParam, $type)
    {
        //获取成员属性
        $accessKeyId = $this->accessKeyId;
        $accessKeySecret = $this->accessKeySecret;
        $signName = $this->signName;
        $templateCode = $this->get_sms_temp_id($type);

        //短信API产品名（短信产品名固定，无需修改）
        $product = "Dysmsapi";
        //短信API产品域名（接口地址固定，无需修改）
        $domain = "dysmsapi.aliyuncs.com";
        //暂时不支持多Region（目前仅支持cn-hangzhou请勿修改）
        $region = "cn-hangzhou";

        // 初始化用户Profile实例
        $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
        // 增加服务结点
        DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
        // 初始化AcsClient用于发起请求
        $acsClient = new DefaultAcsClient($profile);

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();
        // 必填，设置雉短信接收号码
        $request->setPhoneNumbers($mobile);

        // 必填，设置签名名称
        $request->setSignName($signName);

        // 必填，设置模板CODE
        $request->setTemplateCode($templateCode);

        // 可选，设置模板参数
        if ($templateParam) {
            $request->setTemplateParam(json_encode($templateParam));
        }

        //发起访问请求
        $acsResponse = $acsClient->getAcsResponse($request);

        //返回请求结果，这里为为数组格式
        $result = json_decode(json_encode($acsResponse), true);
        return $result;
    }

    /**
     * 取得AcsClient
     *
     * @return DefaultAcsClient
     */
    public static function getAcsClient()
    {
        //产品名称:云通信短信服务API产品,开发者无需替换
        $product = "Dysmsapi";

        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";

        // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
        $accessKeyId = "yourAccessKeyId"; // AccessKeyId

        $accessKeySecret = "yourAccessKeySecret"; // AccessKeySecret

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";


        if (static::$acsClient == null) {

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
     * 批量发送短信
     * $phone_arr 手机号码集合  array("1500000000","1500000001",)
     * $param    参数  array(array("name" => "Tom","code" => "123",),array("name" => "Jack","code" => "456",),)
     * $type  模版列表
     * 签名默认
     * @return stdClass
     */
    public function sendBatchSms($phone_arr, $param, $type)
    {

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendBatchSmsRequest();

        //可选-启用https协议
        //$request->setProtocol("https");

        // 必填:待发送手机号。支持JSON格式的批量调用，批量上限为100个手机号码,批量调用相对于单条调用及时性稍有延迟,验证码类型的短信推荐使用单条调用的方式
        $request->setPhoneNumberJson(json_encode($phone_arr, JSON_UNESCAPED_UNICODE));

        // 必填:短信签名-支持不同的号码发送不同的短信签名
        $request->setSignNameJson(json_encode(array("明宇科技"), JSON_UNESCAPED_UNICODE));

        // 必填:短信模板-可在短信控制台中找到
        $request->setTemplateCode($this->get_sms_temp_id($type));

        // 必填:模板中的变量替换JSON串,如模板内容为"亲爱的${name},您的验证码为${code}"时,此处的值为
        // 友情提示:如果JSON中需要带换行符,请参照标准的JSON协议对换行符的要求,比如短信内容中包含\r\n的情况在JSON中需要表示成\\r\\n,否则会导致JSON在服务端解析失败
        $request->setTemplateParamJson(json_encode($param, JSON_UNESCAPED_UNICODE));

        // 可选-上行短信扩展码(扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段)
        // $request->setSmsUpExtendCodeJson("[\"90997\",\"90998\"]");

        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);

        return $acsResponse;
    }

    /**
     * 短信发送记录查询
     * @return stdClass
     */
    public static function querySendDetails()
    {

        // 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
        $request = new QuerySendDetailsRequest();

        //可选-启用https协议
        //$request->setProtocol("https");

        // 必填，短信接收号码
        $request->setPhoneNumber("12345678901");

        // 必填，短信发送日期，格式Ymd，支持近30天记录查询
        $request->setSendDate("20170718");

        // 必填，分页大小
        $request->setPageSize(10);

        // 必填，当前页码
        $request->setCurrentPage(1);

        // 选填，短信发送流水号
        $request->setBizId("yourBizId");

        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);

        return $acsResponse;
    }

    /**
     * 根据类型进行短信魔板判断
     * $type  短信模版ID
     * @return stdClass
     */
    private static function get_sms_temp_id($type)
    {
        $info = Db::name('sms_template')->where('send_scene', '=', $type)->find();
        return $info['sms_tpl_code'];

        switch ($type) {
            case '1':           // 注册
                $temp_id = 'SMS_113456069';
                break;
            case '2':           // 买家购买通知
                $temp_id = 'SMS_127168403';
                break;
            case '3':           // 商户密码找回
                $temp_id = 'SMS_128790371';
                break;
            case '4':           //商户库存不足提示
                $temp_id = 'SMS_135029755';
                break;
            case '5':           //安全模式解除动态密码
                $temp_id = 'SMS_135806956';
                break;
        }
        return $temp_id;
    }

}