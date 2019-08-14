<?php
class Native
{
    public function wechat($pro_id,$order_num,$subject,$title,$totle)
    {
        ini_set('date.timezone', 'Asia/Shanghai');
        require_once "lib/WxPay.Api.php";
        require_once "example/WxPay.NativePay.php";
        require_once 'example/log.php';
        $notify = new NativePay();
        $input = new WxPayUnifiedOrder();
        $input->SetBody($title);
        $input->SetAttach($subject);
        $input->SetOut_trade_no($order_num);
        $input->SetTotal_fee($totle);
        $input->SetNotify_url(config('site.url')."/index.php/api/payment/WxNotify");   //回调地址
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($pro_id);
        $result = $notify->GetPayUrl($input);
        $url2 = $result["code_url"];
        return urlencode($url2);
    }

    public function getCode($url)
    {
         error_reporting(E_ERROR);
        require_once EXTEND_PATH.'WxPay/example/phpqrcode/phpqrcode.php';
        $path=date('Ymd',time());
        //生成二维码图片
        $filename = 'images/'.$path.'/'.md5(uniqid()).'.png';
        if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/images/'.$path)){
            mkdir($_SERVER['DOCUMENT_ROOT'].'/images/'.$path,0777,true);
        }
        \QRcode::png(urldecode($url),$_SERVER['DOCUMENT_ROOT'].'/'.$filename);
        $QR = $_SERVER['DOCUMENT_ROOT'].'/'.$filename;
        $QR = imagecreatefromstring(file_get_contents($QR));
        return config('site.url').$filename;    //返回路径
    }
}