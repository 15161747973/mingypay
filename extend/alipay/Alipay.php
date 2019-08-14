<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/6/13
 * Time: 16:37
 */

class Alipay
{
    public function newpay($out_trade_no,$subject,$total_amount)
    {
        require_once EXTEND_PATH.'alipay/config.php';
        require_once EXTEND_PATH.'alipay/AopSdk.php';
        require_once EXTEND_PATH.'alipay/aop/AopClient.php';
        require_once EXTEND_PATH.'alipay/pagepay/service/AlipayTradeService.php';
        require_once EXTEND_PATH.'alipay/aop/request/AlipayTradePagePayRequest.php';
        require_once EXTEND_PATH.'alipay/aop/request/AlipayTradePrecreateRequest.php';
        require_once EXTEND_PATH.'alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = trim($out_trade_no);
        //订单名称，必填
        $subject = trim($subject);
        //付款金额，必填
        $total_amount = trim($total_amount);
        //商品描述，可空
        $config = array (
            'app_id' => config('site.appId'),//应用ID,您的APPID
            'merchant_private_key' => config('site.merchant_private_key'),//商户私钥
            'notify_url' => config('site.url')."/index.php/api/payment/AliNotify",//异步通知地址
            'return_url' => config('site.return_url'),    //支付成功后的返回跳转页面
            'charset' => "UTF-8",
            'sign_type'=>"RSA2",//签名方式
            'gatewayUrl' => "https://openapi.alipay.com/gateway.do",//支付宝网关
            'alipay_public_key' => config('site.alipay_public_key'),//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥
        );
        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
//        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $aop = new \AlipayTradeService($config);

        $response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);
        //输出表单
        return $response;
    }
}