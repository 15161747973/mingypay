<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/22
 * Time: 11:43
 */

namespace app\api\controller;

use app\common\controller\Api;
use app\api\model\Order as OrderModel;
use app\api\model\Users as UsersModel;
use app\api\model\Withdraw;

class Order extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     *首页未登录前搜索使用
     */
    public function searchOrder()
    {
        $code = $_POST['keyword'];
        if(!isset($code)){
            $this->error('参数错误');
        }
        if(preg_match("/^1[34578]\d{9}$/", $code)){
            $list = OrderModel::Orders($code);
        }else{
            $list = OrderModel::getOrderByONum($code);
        }
        $this->success('成功',$list,'200');
    }


    /**最近卖出**/
    public function nearSell()
    {
        $condition = [];
        $user = $this->auth->getUser();
//        $pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : '';
        $order_num = isset($_POST['order_num']) ? $_POST['order_num'] : '';
        $pro_name  = isset($_POST['pro_name']) ? $_POST['pro_name'] : '';
        $cate_id   = isset($_POST['cate_id']) ? $_POST['cate_id'] : '';
        $pro_id    = isset($_POST['pro_id']) ? $_POST['pro_id'] : '';
        $mobile    = isset($_POST['mobile']) ? $_POST['mobile'] : '';
//        if(!empty($pay_type)){
//            $condition['o.pay_type'] = $pay_type;
//        }
        if(!empty($pro_name)){
            $condition['o.order_num'] = ['like','%'.$order_num.'%'];
        }
        if(!empty($pro_name)){
            $condition['p.product_name'] = ['like','%'.$pro_name.'%'];
        }
        if(!empty($cate_id)){
            $condition['p.categorys_id'] = $cate_id;
        }
        if(!empty($pro_id)){
            $condition['o.pro_id'] = $pro_id;
        }
        if(!empty($mobile)){
            $condition['o.mobile'] = ['like','%'.$mobile.'%'];
        }
      	if(!empty($order_num)){
        	$condition['o.order_num'] = $order_num;
        }
          
        $data = OrderModel::getNearSell($user['id'],$condition);
        if($data){
            $this->success('操作成功',$data,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**交易记录表**/     //该商户所有的订单数据
    public function orderList()
    {
        $condition = [];
        $user = $this->auth->getUser();
        $condition['o.uid'] = $user['id'];
        $categorys_id = isset($_POST['categorys_id']) ? $_POST['categorys_id'] : '';
        $pro_id = isset($_POST['pro_id']) ? $_POST['pro_id'] : '';
        $pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : '';
        $order_num = isset($_POST['order_num']) ? $_POST['order_num'] : '';
        $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        $beg_time = isset($_POST['beg_time']) ? $_POST['beg_time'] : '';
        $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 8); //每页显示数量
        if(!empty($pay_type)){
            $condition['o.pay_type'] = $pay_type;
        }
        if(!empty($categorys_id)){
            $condition['p.categorys_id'] = $categorys_id;
        }elseif (!empty($pro_id)){
            $condition['o.pro_id'] = $pro_id;
        }
        if(!empty($order_num)){
            $condition['o.order_num'] = $order_num;
        }
        if(!empty($mobile)){
            $condition['o.mobile'] = ['like','%'.$mobile.'%'];
        }
        if(!empty($status)){
            $condition['o.status'] = $status;
        }
        if(empty($beg_time) && !empty($end_time)){
            $this->error('请填写开始时间！');
        }else if(!empty($beg_time) && empty($end_time)){
            $this->error('请填写结束时间！');
        }
        if(!empty($beg_time) && !empty($end_time)){
            $condition['o.create_time'] = ['between',[$beg_time,$end_time]];
        }
        $data = OrderModel::getList($condition,$page,$size);
        if($data){
            $this->success('操作成功',$data,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**下单数据统计**/
    public function orderTongJI()
    {
        $condition = [];
        $user = $this->auth->getUser();
        $cate_id = isset($_POST['categorys_id']) ? $_POST['categorys_id'] : '';
        $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
        $beg_time = isset($_POST['beg_time']) ? $_POST['beg_time'] : '0';
        $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
        //if(!empty($cate_id)){
          //  $condition['p.categorys_id'] = $cate_id;
        //}
        if(!empty($product_id)){
            $condition['o.pro_id'] = $product_id;
        }
        if(empty($beg_time) && !empty($end_time)){
            $this->error('请填写开始时间！');
        }else if(!empty($beg_time) && empty($end_time)){
            $this->error('请填写结束时间！');
        }
        if(!empty($beg_time) && !empty($end_time)){
            $condition['o.create_time'] = ['between',[strtotime($beg_time),strtotime($end_time)]];
        }

//        $data = OrderModel::getList($user['id'],$condition);

        $data['orderCon'] = OrderModel::OrderCount($user['id'],$condition);        //下单总数
        $data['payOrderCon'] = OrderModel::payOrderCon($user['id'],$condition);    //支付订单量
        $data['price'] = OrderModel::getPrice($user['id'],$condition);             //交易总金额
        $data['userPrice'] = OrderModel::UserPrice($user['id'],$condition);        //商户分成总金额
        $data['profit'] = OrderModel::profitPrice($user['id'],$condition);         //利润统计
        if($data){
            $this->success('获取成功',$data,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    //商户申请结算
    public function applyWithdraw()
    {
        $user = $this->auth->getUser();
        $data['uid'] = $user['id'];
        $today_money = OrderModel::getCanMoney($user['id']);  //今日可提现金额
      	$today_money = (string)$today_money;
//		halt($today_money);     0.04
        $data['money'] = $this->request->request('money');   //提现金额		0.01
        $low_money = db('setting')->value('withdraw_low_manual');   //最低提现金额
//        if($data['money']  < $low_money){
//            $this->error('最低提现金额不能小于10元');
//        }
        if($today_money < $data['money']){
            $this->error('提现金额不得大于商户可提现余额');
        }
        $data['charge_money'] = 1;
        $data['create_time'] = time();
        $res = Withdraw::addApply($data);
        if($res){
            //修改商户可提现余额
            UsersModel::upMoneyInfo($user['id'],['all_money'=>$user['all_money']-$data['money'],'can_use_money'=>$user['can_use_money']-$data['money']]);
            $this->success('申请成功',$data,'200');
        }else{
            $this->error('申请失败','','-1');
        }
    }

    /**
     * 订单列表  0=一般的订单  1= 代理订单
     */
    public function agentOrderList()
    {
        $condition = [];
        $pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : '';
        $pro_name = isset($_POST['pro_name']) ? $_POST['pro_name'] : '';
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        $order_num = isset($_POST['order_num']) ? $_POST['order_num'] : '';
        $beg_time = isset($_POST['beg_time']) ? $_POST['beg_time'] : '';
        $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
        if(!empty($pay_type)){
            $condition['o.pay_type'] = $pay_type;
        }
        if(!empty($order_num)){
            $condition['o.order_num'] = ['like','%'.$order_num.'%'];
        }
        if(!empty($pro_name)){
            $condition['p.product_name'] = ['like','%'.$pro_name.'%'];
        }
        if(!empty($status)){
            $condition['o.status'] = $status;
        }
        if(empty($beg_time) && !empty($end_time)){
            $this->error('请填写开始时间！');
        }else if(!empty($beg_time) && empty($end_time)){
            $this->error('请填写结束时间！');
        }
        if(!empty($beg_time) && !empty($end_time)){
            $condition['o.create_time'] = ['between',[$beg_time,$end_time]];
        }
        $order_type = isset($_POST['order_type']) ? $_POST['order_type'] : '0';
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量
        $user = $this->auth->getUser();           //用户信息
        $list = OrderModel::agentList($user['id'],$order_type,$page,$size,$condition);
        if($list){
            $this->success('操作成功',$list,'0');
        }else{
            $this->error('操作失败','','-1');
        }
    }

    /**
     * 供货商订单
     */
    public function auppleOrder()
    {
        $condition = [];
        $pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : '';
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        $order_num = isset($_POST['order_num']) ? $_POST['order_num'] : '';
        $beg_time = isset($_POST['beg_time']) ? $_POST['beg_time'] : '';
        $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
        if(!empty($pay_type)){
            $condition['o.pay_type'] = $pay_type;
        }
        if(!empty($order_num)){
            $condition['o.order_num'] = ['like','%'.$order_num.'%'];
        }
//        if(!empty($mobile)){
//            $condition['o.mobile'] = ['like','%'.$mobile.'%'];
//        }
        if(!empty($status)){
            $condition['o.status'] = $status;
        }
        if(empty($beg_time) && !empty($end_time)){
            $this->error('请填写开始时间！');
        }else if(!empty($beg_time) && empty($end_time)){
            $this->error('请填写结束时间！');
        }
        if(!empty($beg_time) && !empty($end_time)){
            $condition['o.create_time'] = ['between',[$beg_time,$end_time]];
        }
        $user = $this->auth->getUser();
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量
        $data = OrderModel::suppleOrder($user['id'],$page,$size,$condition);
        if($data){
            $this->success('操作成功',$data,'0');
        }else{
            $this->error('操作失败','','-1');
        }
    }


    /** 供货商的收益分析 **/
    public function InComeAnalyze()
    {
        $condition = [];
        $pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : '';
        $pro_name = isset($_POST['pro_name']) ? $_POST['pro_name'] : '';
        $order_num = isset($_POST['order_num']) ? $_POST['order_num'] : '';
        $beg_time = isset($_POST['beg_time']) ? $_POST['beg_time'] : '';
        $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
        if(!empty($pay_type)){
            $condition['o.pay_type'] = $pay_type;
        }
        if(!empty($order_num)){
            $condition['o.order_num'] = ['like','%'.$order_num.'%'];
        }
        if(!empty($pro_name)){
            $condition['p.product_name'] = ['like',"%".$pro_name."%"];
        }
        if(empty($beg_time) && !empty($end_time)){
            $this->error('请填写开始时间！');
        }else if(!empty($beg_time) && empty($end_time)){
            $this->error('请填写结束时间！');
        }
        if(!empty($beg_time) && !empty($end_time)){
            $condition['o.create_time'] = ['between',[$beg_time,$end_time]];
        }
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量
        $user = $this->auth->getUser();
        $data = OrderModel::suppleInCome($user['id'],$page,$size,$condition);
        if($data){
            $this->success('获取成功',$data,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }
    //代理商的收益分析
    public function agentInCome()
    {
        $condition = [];
        $pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : '';
        $pro_name = isset($_POST['pro_name']) ? $_POST['pro_name'] : '';
        $order_num = isset($_POST['order_num']) ? $_POST['order_num'] : '';
        $beg_time = isset($_POST['beg_time']) ? $_POST['beg_time'] : '';
        $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
        if(!empty($pay_type)){
            $condition['o.pay_type'] = $pay_type;
        }
        if(!empty($order_num)){
            $condition['o.order_num'] = ['like','%'.$order_num.'%'];
        }
        if(!empty($pro_name)){
            $condition['ap.product_name'] = ['like','%'.$pro_name.'%'];
        }
        if(empty($beg_time) && !empty($end_time)){
            $this->error('请填写开始时间！');
        }else if(!empty($beg_time) && empty($end_time)){
            $this->error('请填写结束时间！');
        }
        if(!empty($beg_time) && !empty($end_time)){
            $condition['o.create_time'] = ['between',[$beg_time,$end_time]];
        }
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量
        $user = $this->auth->getUser();           //用户信息
        $list = OrderModel::agentShouYi($user['id'],$page,$size,$condition);
        if($list){
            $this->success('操作成功',$list,'0');
        }else{
            $this->error('操作失败','','-1');
        }
    }

}























