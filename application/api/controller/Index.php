<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\api\model\Users as UsersModel;
use app\api\model\Order as OrderModel;
use app\api\model\Paytype;
use think\DB;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['payType','DaoOut'];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }

    /**
     * 修改首页商户信息
     */
    public function upInfo()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $data['shop_name'] = isset($_POST['shop_name']) ? $_POST['shop_name'] : '';
        $data['qq'] = isset($_POST['user_qq']) ? $_POST['user_qq'] : '';
        $data['shop_biref'] = isset($_POST['shop_natice']) ? $_POST['shop_natice'] : '';
        $data['site_url'] = isset($_POST['my_link']) ? $_POST['my_link'] : '';
        $data['pay_type'] = isset($_POST['pay_type']) ? $_POST['pay_type'] : '1';
        if($data['shop_name'] == $user['shop_name'] && $data['qq']==$user['qq'] && $data['shop_biref']==$user['shop_biref'] && $data['site_url']==$user['site_url'] && $data['pay_type']==$user['pay_type']){
            $this->error('您未修改数据','','-1');
        }
        $res = UsersModel::UpData($user['id'],$data);
        if($res){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }

    /**
     * 首页数据展示
     */
    public function dataInfo()
    {
        //可提现余额、未结金额、今日成交订单、昨日成交订单、今日卖出卡量、今日卖出成本、今日卖出利润
        $user = $this->auth->getUser(); //获取用户信息
        $data['withdraw_money'] = OrderModel::getCanMoney($user['id']);     //提现余额
        $data['today_money'] = OrderModel::getTodayMoney($user['id']);      //未结金额
        $data['yesDay_order'] = OrderModel::yesterday_order($user['id']);   //昨日成交订单
        $data['toDay_order'] = OrderModel::today_order($user['id']);        //今日成交订单
        $data['sellCodeNums'] = OrderModel::getNums($user['id']);           //今日卖出卡量
        $data['todayCost'] = OrderModel::getTodayCost($user['id']);         //今日卖出成本
        $data['profitM'] = $data['today_money'] - $data['todayCost'];       //今日卖出利润
      	$data['freeze'] = OrderModel::getFreezeM($user['id']);
      	if($data['freeze'] == null){
          $data['freeze'] = '0';
        }
        if($data){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * 付款方式管理
     */
    public function payType()
    {
        $condition = [];
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        if(!empty($status)){
            $condition['status'] = $status;
        }else{
            $condition['status'] = ['in',['0','1']];
        }
        $data = Paytype::getType($condition);
        if($data){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }

     /** 卡密导出 **/
    public function DaoOut()
    {
        $text = $this->request->request('info');
        //将数组存到指定的text文件中
        file_put_contents("data.txt",serialize($text));
        //获取数据
        $datas = unserialize(file_get_contents("data.txt"));
        if($datas){
            $this->success('获取成功',$datas,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }
}
