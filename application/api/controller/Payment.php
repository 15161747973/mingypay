<?php

/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/5/31
 * Time: 14:13
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Order as OrderModel;
use app\api\model\Product;
use app\api\model\Cardinfo;
use app\api\model\Users as UsersModel;
use app\api\controller\Sms;
use app\api\controller\Users;
use app\api\model\Coupon;
use think\Db;

class Payment extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    public function pay()
    {
        require_once EXTEND_PATH.'alipay/Alipay.php';
        require_once EXTEND_PATH.'WxPay/Native.php';
        $data['supple_id'] = isset($_POST['supple_id']) ? $_POST['supple_id'] : '';         	//供货商id
        $data['order_type'] = isset($_POST['order_type']) ? $_POST['order_type'] : '0';     	//订单类型 1 = 代理订单
        $data['agent_pro_id'] = isset($_POST['agent_pro_id']) ? $_POST['agent_pro_id'] : '';	//对应的代理商品表ID
        $data['pro_id']         = $this->request->request('pro_id');                   			//商品名称
        $couponCode = isset($_POST['couponCode']) ? $_POST['couponCode'] : '';                  //优惠券码
        if(!empty($couponCode)){
            $couponInfo = Coupon::getInfoByCode($couponCode,$data['pro_id']);
            if(!$couponInfo){
                $this->success('优惠券不存在或已使用','','-1');
            }
            $data['coupon_id'] = $couponInfo['id'];
            $coupon_money = $couponInfo['price'];
        }else{
            $coupon_money = '0';
        }
        $user = $this->auth->getUser(); //获取用户信息
        $data['uid']            = $user['id'];
        $data['pay_type']       = $this->request->request('pay_type');                			//支付方式 1=支付宝 2=微信 3=QQ钱包
        $product_name           = Product::getField($data['pro_id'],'product_name');
        $sequence               = Product::getField($data['pro_id'],'sequence');        		//发卡顺序 0,：顺序  1=随机
        $data['pro_price']      = $this->request->request('pro_price');               			//商品单价
        $data['pro_num']        = $this->request->request('pro_num');                 			//商品数量
        $card_ids               = Cardinfo::getRandId($data['pro_id'],$data['pro_num'],$sequence);
        $data['card_id']        = rtrim($card_ids,',');     									//卡的数量
      	$data['pro_name']       = $product_name;
        $data['mobile']         = $this->request->request('mobile');                  			//手机号
        $data['is_SendSms']     = isset($_POST['is_SendSms']) ? $_POST['is_SendSms'] : '0'; 	//是否短信提醒
        $data['is_SendEmail']   = isset($_POST['is_SendEmail']) ? $_POST['is_SendEmail'] : '0';     	//是否邮箱提醒
        if($data['is_SendEmail']== '1'){
            $data['email']      = $this->request->request('email');                   			//邮箱
        }
        if($data['is_SendSms'] == '1'){
            $data['sms_price']  = '0.1';
            $data['price']      = $data['pro_price'] * $data['pro_num'] + 0.1 - $coupon_money;              //支付金额（短信提醒  +0.1元）
        }else{
            $data['price']      = $data['pro_price'] * $data['pro_num'] - $coupon_money;                    //支付金额
        }
        $data['create_time']    = time();     //下单时间
        $data['order_num']      = date('md',time()).substr(time(),2,1).rand(10000,99999).$this->getrandstr();       //订单号
        $order_res          = OrderModel::addOrderInfo($data);
        if (!$order_res) {
            $this->error('订单插入失败', '',-1);
        }
        if ($data['pay_type'] == 1){    //支付宝支付
            // 是否使用优惠券 TODO
            //调用支付接口 TODO
            $alipay = new \Alipay();
            $res = $alipay->newpay($data['order_num'],$product_name,$data['price']);

        }elseif ($data['pay_type'] == 2){   //微信支付
            $wxpay = new \Native();
            $res = $wxpay->wechat($data['pro_id'],$data['order_num'],$product_name,'最新支付',$data['price']*100);
            $ARR['order_num'] = $data['order_num'];
            $ARR['price'] = $data['price'];
            $ARR['img_url'] = $wxpay->getCode($res);
            //生成二维码
            $this->success('success',$ARR,'200');
        }
    }

    //生成随机数
    public function getrandstr()
    {
        $str='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $randStr = str_shuffle($str);   //打乱字符串
        $rands= substr($randStr,0,6);   //substr(string,start,length); 返回字符串的一部分
        return strtoupper($rands);
    }

    /**
     * @throws \Exception
     * 支付宝回调
     */
    public function AliNotify()
    {
        require_once EXTEND_PATH . 'alipay/config.php';
        require_once EXTEND_PATH . 'alipay/pagepay/service/AlipayTradeService.php';

        $postData = $_POST;
        $config = array(
            'app_id' => config('site.appId'),
            'merchant_private_key' => config('site.merchant_private_key'),
            'notify_url' => config('site.url') . "/index.php/api/payment/AliNotify",
            'return_url' => config('site.return_url'),    //支付成功后的返回跳转页面
            'charset' => "UTF-8",
            'sign_type' => "RSA2",
            'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
            'alipay_public_key' => config('site.alipay_public_key'),
        );
        $orderInfo = OrderModel::getOrderInfo($postData['out_trade_no']); //订单数据详情
        $productInfo = Product::getProInfo($orderInfo['pro_id']);
        $userInfo = db('user')->where(['id' => $orderInfo['uid']])->find();
        $alipaySevice = new \AlipayTradeService($config);
        $alipaySevice->writeLog(var_export($_POST, true));
        $result = $alipaySevice->check($postData);
        if ($orderInfo['status'] == '1') {
            if ($result) {
                $up_order = [
                    'pay_type' => '1',                           // 支付宝
                    'status' => '2',                           // 支付成功 代发货
                    'pay_time' => time(),                       // 支付时间
                    'pay_price' => $postData['total_amount'],      // 实际支付金额
                    'user_price' => $postData['total_amount'] * 0.97, //商户分成
                    'trade_no' => $postData['trade_no'],          // 支付成功后返回的订单号
                ];
                $new_money = $userInfo['all_money'] + ($postData['total_amount'] * 0.97);
                $new_use_money = $userInfo['can_use_money'] + $postData['total_amount'] * 0.97;
                $new_num = $productInfo['num'] - $orderInfo['pro_num'];
                $new_sell_num = $productInfo['sale_num'] + $orderInfo['pro_num'];
                UsersModel::upApprove($userInfo['id'], ['all_money' => $new_money, 'can_use_money' => $new_use_money]);    //修改用户余额
                $res = OrderModel::updateOrder($orderInfo['id'], $up_order);            //修改订单表里面的状态等信息
                $res1 = Product::UpdatePro($orderInfo['pro_id'], ['num' => $new_num, 'sale_num' => $new_sell_num]);        //修改商品表改商品的数量
                $arr_card_id = explode(',', $orderInfo['card_id']);
                Cardinfo::upDatas($arr_card_id, ['status' => '2']);        //修改售出的卡密状态未  已售出

                if ($orderInfo['is_SendSms'] == '1') {
                    $sms = new Sms();
                    $sms->SendSms('buyNotice', $orderInfo['mobile'], '2', $orderInfo['order_num']);
                }
                if ($orderInfo['is_SendEmail'] == '1') {
                    $users = new Users();
                    $users->sendEmail('1', $orderInfo['email'], '购买通知', $orderInfo['order_num']);
                }
            }
        }
    }


        /**
         * 微信回调
         */
        public function WxNotify()
    {
        $postXml = file_get_contents('php://input');
        if (empty($postXml)) {
            echo "数据不能为空";
            die;
        }
        libxml_disable_entity_loader(true); //禁止引用外部xml实体
        $xml = simplexml_load_string($postXml, 'SimpleXMLElement', LIBXML_NOCDATA);//XML转数组
        $returnData = (array)$xml;

        $out_trade_no = isset($returnData['out_trade_no']) && !empty($returnData['out_trade_no']) ? $returnData['out_trade_no'] : 0;
        $orderInfo = OrderModel::getOrderInfo($out_trade_no); //订单数据详情
        $productInfo = Product::getProInfo($orderInfo['pro_id']);
        $userInfo = UsersModel::getUserInfo($orderInfo['uid']);
        if($orderInfo['status'] == '1'){
            if ($returnData['return_code'] == "SUCCESS") {
                //根据自己业务需要去执行操作
                $up_order = [
                    'pay_type' => '2',                         		// 支付宝
                    'status' => '2',                         		// 支付成功 代发货
                    'pay_time' => time(),                      		// 支付时间
                    'pay_price' => $returnData['total_fee']/100,  	// 实际支付金额
                    'user_price'=>$returnData['total_fee']*0.97/100,//商户分成
                ];
                $new_money = $userInfo['all_money'] + $returnData['total_fee']*0.97/100;
                $can_use_money = $userInfo['can_use_money'] + $returnData['total_fee']*0.97/100;
                $new_num = $productInfo['num'] - $orderInfo['pro_num'];
                $new_sale_num = $productInfo['sale_num']+$orderInfo['pro_num'];
                UsersModel::upApprove($orderInfo['uid'],['all_money'=>$new_money,'can_use_money'=>$can_use_money]); //修改用户余额
                $res = OrderModel::updateOrder($orderInfo['id'], $up_order); 			//修改订单表里面的状态等信息
                $res1 = Product::UpdatePro($orderInfo['pro_id'], ['num' => $new_num, 'sale_num' => $new_sale_num]);  //修改商品表改商品的数量
                $ids = explode(',', $orderInfo['card_id']);
                Cardinfo::upStatus($ids);    				//修改售出的卡密状态未  已售出
              
              	if ($orderInfo['is_SendSms'] == '1') {
                    $sms = new Sms();
                    $sms->SendSms('buyNotice', $orderInfo['mobile'], '2', $out_trade_no);
                }
                if ($orderInfo['is_SendEmail'] == '1') {
                    $users = new Users();
                    $users->sendEmail('1', $orderInfo['email'], '购买通知', $orderInfo['order_num']);
                }
              
            }
        }
    }

        /** 微信跳转 **/
        public function WXJump()
    {
        $order_num = $this->request->request('order_num');
        $status = OrderModel::getField($order_num,'status');
        if($status == '2'){
            $this->success('SUCCESS','SUCCESS','200');
        }else{
            $this->error('ERROR','ERROR','-1');
        }
    }

        /** 支付宝退款 **/
        public function aliRefund()
    {
        require_once EXTEND_PATH.'alipay/aop/AopClient.php';
        require_once EXTEND_PATH.'alipay/aop/request/AlipayTradeRefundRequest.php';
        $out_trade_no = $this->request->request('order_num');
        $orderInfo = OrderModel::getOrderInfoByNum($out_trade_no);	//返回一维数组
        if($orderInfo['status'] == '1'){
            $this->error('未支付订单不能发起退款');
        }
        if($orderInfo['status'] == '5'){
            $this->error('已退款订单不能重复退款');
        }
        $refund_fee = $this->request->request('money');
        $aop = new \AopClient ();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = config('site.appId');
        $aop->rsaPrivateKey = config('site.merchant_private_key');
        $aop->alipayrsaPublicKey = config('site.alipay_public_key');

        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'utf-8';
        $aop->format = 'json';
        $request = new \AlipayTradeRefundRequest ();
        //TODO 方便多次退款的设置
        $out_request_no = $out_trade_no.rand(1000,9999);
        $request->setBizContent("{" .
            //订单支付时传入的商户订单号,不能和 trade_no同时为空。
            "\"out_trade_no\":\"$out_trade_no\"," .
            //支付宝交易号，和商户订单号不能同时为空
            //"\"trade_no\":\"2019060622001445431042039169\"," .
            //需要退款的金额，该金额不能大于订单金额,单位为元，支持两位小数 c
            "\"refund_amount\":$refund_fee," .
            //标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传
            "\"out_request_no\":\"$out_request_no\"" .
            "  }");
        $result = $aop->execute($request);
        //var_dump($result);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;

        if (!empty($resultCode) && $resultCode == 10000) {
            //UsersModel::upDataCanUseMoney($out_trade_no,$refund_fee);
          	$userIn = db('user')->where(['id'=>$orderInfo['uid']])->field('id as uid,can_use_money')->find();
            $newMon = $userIn['can_use_money'] - $refund_fee;
            $aa = db('user')->where(['id'=>$orderInfo['uid']])->update(['can_use_money'=>$newMon]);
            OrderModel::upDataByOrderNum($out_trade_no,['status'=>'5']);
            $status = 1;
            $this->success('退款成功','','200');
        } else {
            $status = 0;
            $message = $result->alipay_trade_refund_response->sub_msg;
        }
        echo $message;
    }

        /**
         * 微信退款
         * @param float $totalFee 订单金额 单位元
         * @param float $refundFee 退款金额 单位元
         * @param string $wxOrderNo 微信订单号
         * @return string
         */
        public function wxRefund()
    {
        require_once EXTEND_PATH.'WxPay/DoRefund.php';
        $request = new \DoRefund ();
        $orderNo = $this->request->request('order_num');
        $orderInfo = OrderModel::getOrderInfoByNum($orderNo);
        if($orderInfo['status'] == '1'){
            $this->error('未支付订单不能发起退款');
        }
        if($orderInfo['status'] == '5'){
            $this->error('已退款订单不能重复退款');
        }
        $totalFee = $this->request->request('price');
        $refundFee = $this->request->request('money');
        $parma = array(
            'appid' => config('site.WXAPPID'),
            'mch_id' => config('site.MCHID'),
            'nonce_str' => rand(100000000,999999999),
            'out_refund_no' =>$request::createNonceStr(),	//退款单号
            'out_trade_no' => $orderNo,		//订单号
            'total_fee' => intval($totalFee * 100),
            'refund_fee' => intval($refundFee *100),
        );
        $parma['sign'] = $request->getSign($parma, config('site.KEY'));
        $xmldata = $request->arrayToXml($parma);
        $xmlresult = $request->postXmlSSLCurl($xmldata, 'https://api.mch.weixin.qq.com/secapi/pay/refund');
        if($xmlresult){
          	$userIn = db('user')->where(['id'=>$orderInfo['uid']])->field('id as uid,can_use_money')->find();
            $newMon = $userIn['can_use_money'] - $refundFee;
            $aa = db('user')->where(['id'=>$orderInfo['uid']])->update(['can_use_money'=>$newMon]);
            $rrr = OrderModel::UpStatus($orderNo);
            $this->success('退款成功','','200');
        }else{
            $this->error('退款失败','','-1');
        }
//        halt($xmlresult);
//        $result = $request->arrayToXml($xmlresult);
//        return $result;
    }
    }