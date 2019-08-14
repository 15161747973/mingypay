<?php
$config = array (
    //应用ID,您的APPID。
    'app_id' => config('site.appId'),
    //商户私钥
    'merchant_private_key' => config('site.merchant_private_key'),
    //异步通知地址
//            'notify_url' => "http://外网可访问网关地址/alipay.trade.page.pay-PHP-UTF-8/notify_url.php",
    'notify_url' => config('site.url')."/index.php/api/payment/AliNotify",
    //同步跳转
    'return_url' => config('site.return_url'),
    //编码格式
    'charset' => "UTF-8",
    //签名方式
    'sign_type'=>"RSA2",
    //支付宝网关
    'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    'alipay_public_key' => config('site.alipay_public_key'),
);