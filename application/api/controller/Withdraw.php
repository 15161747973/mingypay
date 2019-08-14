<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/7/4
 * Time: 15:30
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Users as UsersModel;
use app\api\model\Order as OrderModel;
use app\api\model\Withdraw as WithdrawModel;
use think\db;

class Withdraw extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    //商户申请结算
    public function applyWithdraw()
    {
        $user = $this->auth->getUser();
        $data['uid'] = $user['id'];
        $today_money = OrderModel::getTodayMoney($user['id']);  	//今日可提现金额
        $data['money'] = $this->request->request('money');   		//提现金额
        $low_money = db('setting')->value('withdraw_low_manual');   //最低提现金额
      	if($data['money'] <= 0){
        	$this->error('提现金额必须大于零');
        }
        if($data['money'] < '10'){
            $this->error('最低提现金额不能小于10元');
        }
        if($today_money < $data['money']){
            $this->error('提现金额不得大于商户可提现余额');
        }
        $data['charge_money'] = 1;
        $data['create_time'] = time();
        $res = WithdrawModel::addApply($data);
        if($res){
            //修改商户可提现余额
            UsersModel::upMoneyInfo($user['id'],['all_money'=>$user['all_money']-$data['money'],'can_use_money'=>$user['can_use_money']-$data['money']]);
            $this->success('申请成功',$data,'200');
        }else{
            $this->error('申请失败','','-1');
        }
    }

    //平台 生成结算申请列表
    public function applyList()
    {
        $data = WithdrawModel::getList();
        if($data){
            $this->success('success',$data,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    //平台 结算审核
    public function applyCheck()
    {
        $data['is_pass'] = $this->request->request('is_pass');
        $id = $this->request->request('id');
        if($data['is_pass'] == '0'){
            $data['noPassReason'] = $this->request->request('reason');
        }
        $res = WithdrawModel::changeStat($id,$data);
        if($res){
            $this->success('审核成功',$res,'200');
        }else{
            $this->error('审核失败','','-1');
        }
    }

    //平台打款列表
    public function payApply()
    {
        $data = WithdrawModel::getPayList();
        if($data){
            $this->success('success',$data,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    //点击打款后修改结算列表处理状态
    public function payStatus()
    {
        $id = $this->request->request('id');
        $data['status'] ='2';
        $data['deal_time'] = time();
        $res = WithdrawModel::upData($id,$data);
        if($res){
            $this->success('操作成功',$res,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }

    //商户结算列表展示、
    public function UserApplyList()
    {
        $data = WithdrawModel::getApplyList();
        if($data){
            $this->success('success',$data,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    //商户结算记录
    public function withdrawList()
    {
        $user = $this->auth->getUser();
        $uid = $user['id'];
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 8); //每页显示数量
        $data = WithdrawModel::getDataByUid($uid,$page,$size);
        if($data){
            $this->success('success',$data,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }
}