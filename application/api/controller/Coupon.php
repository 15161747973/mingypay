<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/8/8
 * Time: 14:31
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Coupon as CouponModel;

class Coupon extends Api
{
    protected $NeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 添加优惠券
     */
    public function addCoupon()
    {
        $user = $this->auth->getUser();
        $num = $this->request->request('num');
        for($i=1;$i<=$num;$i++){
            $data['code'] = date('md',time()).substr(time(),2,1).rand(10000,99999).$this->getrandstr();
            $data['uid'] = $user['id'];
            $data['price'] = $this->request->request('price');
          	$data['categorys_id'] = $this->request->request('categorys_id');
            $data['pro_id'] = $this->request->request('pro_id');
            $end_time = $this->request->request('end_time');
          	$data['end_time'] = strtotime($end_time);
            $data['create_time'] = time();
            $res = CouponModel::addCoupon($data);
        }
        if($res){
            $this->success('添加成功',$num,'200');
        }else{
            $this->error('添加失败','','-1');
        }
    }

    /**
     * @return string
     * 优惠券列表
     */
    public function couponList()
    {
        $condition = [];
        $user = $this->auth->getUser();
        $product_name = isset($_POST['product_name']) ? $_POST['product_name'] : '';
        $code = isset($_POST['code']) ? $_POST['code'] : '';
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        if(!empty($product_name)){
            $condition['p.product_name'] = ['like','%'.$product_name.'%'];
        }
        if(!empty($code)){
            $condition['c.code'] = $code;
        }
        if(!empty($status)){
            $condition['c.status'] = $status;
        }else{
            $condition['c.status'] = ['in',['1','2','3']];
        }
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 8); //每页显示数量
        $data = CouponModel::getCouponList($user['id'],$page,$size,$condition);
        if($data){
            $this->success('添加成功',$data,'0');
        }else{
            $this->error('添加失败','','-1');
        }
    }
  
  	/** 优惠券详情 **/
    public function couponInfo()
    {
        $couId = $this->request->request('cou_id');
        $info = CouponModel::getInfoById($couId);
        if($info){
            $this->success('获取成功',$info,'200');
        }else{
            $this->error('参数错误','','-1');
        }
    }
  	
  	/**
     * @param Request $request
     * 修改优惠券信息
     */
    public function couponEdit(Request $request)
    {
        $data = $request->except(['token']);
        $res = CouponModel::UpdateCou($data['id'],$data);
        if($res){
            $this->success('修改成功',$data,'200');
        }else{
            $this->error('修改失败','','-1');
        }
    }
  
  	/**
     * 删除优惠券信息
     */
    public function delCoupon()
    {
        $id = $this->request->request('cou_id');
        $res = CouponModel::delCou($id);
        if($res){
            $this->success('删除成功',$res,'200');
        }else{
            $this->error('删除失败','','-1');
        }
    }
  
  	/**
     * 批量删除优惠券信息
     */
    public function delAllCoupon()
    {
        $ids = $this->request->request('ids');
        $id_arr = explode(',',$ids);
        $res = CouponModel::delAllCoupon($id_arr);
        if($res){
            $this->success('删除成功',$res,'200');
        }else{
            $this->error('删除失败','','-1');
        }
    }

    /**
     * @return string
     * 判断优惠券是否存在
     */
//    public function check

    public function getrandstr()
    {
        $str='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $randStr = str_shuffle($str);   //打乱字符串
        $rands= substr($randStr,0,6);   //substr(string,start,length); 返回字符串的一部分
        return strtoupper($rands);
    }
}