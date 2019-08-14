<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/7/18
 * Time: 14:51
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Complainorder as ComplainModel;
use app\api\model\Order as OrderModel;
use app\api\model\Users as UsersModel;
use app\api\model\Img;

class Complainorder extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }
    //添加投诉订单
    public function addComplain()
    {
        $data['order_num'] = $this->request->request('order_num');
        $data['order_type'] = isset($_POST['order_type']) ? $_POST['order_type'] : '0';
      
      	$orderInfo = OrderModel::getOrderInfoByNum($data['order_num']);
        $userInfo = UsersModel::getField($orderInfo['uid'],'can_use_money');
        $freezeHouM = $userInfo['can_use_money'] - $orderInfo['pay_price'];
      	if($orderInfo['status'] == '4'){
          $this->error('该订单不能重复投诉','','-2');
        }
        if($data['order_type'] == '1'){
            $data['supple_id'] = OrderModel::getField($data['order_num'],'supple_id');
            $data['uid'] = OrderModel::getField($data['order_num'],'uid');
        }elseif($data['order_type'] == '0'){
            $data['supple_id'] = '';
            $data['uid'] = OrderModel::getField($data['order_num'],'uid');
          	UsersModel::upApprove($data['uid'],['can_use_money'=>$freezeHouM]);	//将可用金额减去投诉冻结的金额
        }
        $data['cause'] = $this->request->request('cause');
        $data['qq'] = $this->request->request('qq');
        $data['mobile'] = $this->request->request('mobile');
        $data['detail'] = $this->request->request('detail');
        $data['create_time'] = time();
        $res = ComplainModel::addOrder($data);
        if($res){
            OrderModel::upDataByOrderNum($data['order_num'],['status'=>'4']);
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }

    //投诉订单  0= 一般的投诉订单  1=代理商的投诉订单
    public function agentComplainOrder()
    {
        $condition = [];
        $pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : '';
        $deal_status = isset($_POST['deal_status']) ? $_POST['deal_status'] : '';
        if(!empty($pay_type)){
            $condition['o.pay_type'] = $pay_type;
        }
        if(!empty($deal_status)){
            $condition['c.deal_status'] = $deal_status;
        }
        $user = $this->auth->getUser();     //用户信息
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量
        $order_type = isset($_POST['order_type']) ? $_POST['order_type'] : '0';
        $res = ComplainModel::complainOrderList($user['id'],$order_type,$page,$size,$condition);
        if($res){
            $this->success('操作成功',$res,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

  	/** 投诉时上传图片 **/
  	public function complainImg()
    {
      $file = request()->file('file');
        $info = $file->move( '../public/uploads');
        $returnUrl='';
        if($info){
            $returnUrl='/uploads/'.$info->getSaveName();
        }
        $user = $this->auth->getUser();
        if($user){
            $data['uid'] = $user['id'];
        }else{
            $data['uid'] = '';
        }
        $data['order_num'] = $this->request->request('order_num');      // 订单号
        $data['type'] = $this->request->request('type');                // 3=图片类型
        $url = rtrim(config('site.url'),'/');
        $data['image'] = $url.$returnUrl;
        $data['create_time'] = time();
        $res = Img::addImg($data);
        if($res){
            $this->success('操作成功',$data['image'],'200');
        }else{
            $this->error('失败','','-1');
        }
    }
    /**
     * 供货商的投诉订单
     */
    public function ComplainSuppleOrder()
    {
        $condition = [];
        $pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : '';
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        if(!empty($pay_type)){
            $condition['o.pay_type'] = $pay_type;
        }
        if(!empty($status)){
            $condition['c.status'] = $status;
        }
        $user = $this->auth->getUser();     //用户信息
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量\
        $res = ComplainModel::getSuppleOrder($user['id'],$page,$size,$condition);
        if($res){
            $this->success('操作成功',$res,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }
}


























