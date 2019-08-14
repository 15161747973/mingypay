<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/6/12
 * Time: 15:44
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Users as UsersModel;
use app\api\model\Categorys;
use app\api\model\Order as OrderModel;
use app\api\model\Product;
use app\api\model\Agentproduct as AgentProModel;
use think\request;

class Agentproduct extends Api
{
    protected $NeedLogin = '*';
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 对接管理 （对接 == 添加 ）
     */
    public function agentJoin()
    {
        $data['supple_id'] = $this->request->request('uid');
        $user = $this->auth->getUser(); //获取用户信息
        $data['uid'] = $user['id'];
        $res = UsersModel::getField($data['supple_id'],'qq');
        $data['supple_qq'] = $res['qq'];
        $data['supple_pro_id'] = $this->request->request('product_id');
        $data['score'] = $this->request->request('score');
        $data['cost_price'] = $this->request->request('cost_price');
        $data['product_name'] = $this->request->request('product_name');
        $data['categorys_id'] = $this->request->request('categorys_id');
        $data['old_price'] = $this->request->request('sell_price');
        $data['now_price'] = $this->request->request('now_price');
        $data['stock'] = $this->request->request('num');
        $data['create_time'] = time();
        $resq = AgentProModel::addSub($data);
        if($resq){
            $this->success('对接成功',$data,'200');
        }else{
            $this->error('对接失败','','-1');
        }
    }

    /**我是代理商**/

    /**
     * $agentCode   授权码对接
     */
    public function buttAuth()
    {
        $user = $this->auth->getUser();
        $auth_code_status = $this->request->request('status');  //1=商户代理码 2=商品代理码
        $auth_code = $this->request->request('auth_code');
        if(empty($auth_code) && !$auth_code_status){
            $this->error('暂无数据','','-1');
        }
        $page = input('post.page', 1); //页数
        $size = input('post.size', 5); //每页显示数量
        if($auth_code_status == '1'){
            $userInfo = UsersModel::infoByAuthcode($auth_code);
            if($user['id'] == $userInfo['id']){
                $this->error('不能代理自己的商品...');
            }
            $agent_uid_arr = explode(',',$userInfo['stop_agent_uid']);
            if(!in_array($user['id'],$agent_uid_arr)) {
                $data = UsersModel::getUserProData($auth_code,$page,$size);
            }else{
                $this->error('已被禁，请联系供货商家','','-1');
            }
        }elseif ($auth_code_status == '2'){
            $userInfo = Product::infoByAuthcode($auth_code);
            if($user['id'] == $userInfo['uid']){
                $this->error('不能代理自己的商品...');
            }
            $agent_uid_arr = explode(',',$userInfo['stop_agent_uid']);
            if(!in_array($user['id'],$agent_uid_arr)) {
                $data = Product::getProByPro($auth_code,$page,$size);
            }else{
                $this->error('已被禁，请联系供货商家','','-1');
            }
        }
        if($data){
            $this->success('操作成功',$data,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }


    /**
     * 代理商品列表
     */
    public function agentProList()
    {
        $user = $this->auth->getUser();
        $condition = [];
        $cate_id = isset($_POST['categorys_id']) ? $_POST['categorys_id'] : '';
        $product_name = isset($_POST['product_name']) ? $_POST['product_name'] : '';

        $page = input('post.page', 1); //页数
        $size = input('post.size', 5); //每页显示数量
        if(!empty($cate_id)){
            $condition['categorys_id'] = $cate_id;
        }
        if(!empty($product_name)){
            $condition['product_name'] = ['like','%'.$product_name.'%'];
        }
        $data = AgentProModel::getList($user['id'],$page,$size,$condition);
        if($data){
            $this->success('操作成功',$data,'0');
        }else{
            $this->error('操作失败','','-1');
        }
    }

    /**
     * 代理商品详情
     */
    public function proInfo()
    {
        $pro_id = $this->request->request('pro_id');
        $data = AgentProModel::getInfo($pro_id);
        if($data){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }

    /**
     * 代理商品 编辑
     */
    public function updateAgent(Request $request)
    {
        $data = $request->except(['token']);
        $res = AgentProModel::upAgent($data['id'],$data);
        if($data['categorys_id']){
            $cateArr = Categorys::getCateName($data['categorys_id']);
            $data['categorys_name'] = $cateArr['categorys_name'];
        }
        if($res){
            $this->success('修改成功',$data,'200');
        }else{
            $this->error('修改失败','','-1');
        }
    }

    /**删除 代理商品列表  **/
    public function delAgentPro()
    {
        $id = $this->request->request('id');
        $res = AgentProModel::delAgent($id);
        if($res){
            $this->success('操作成功',$res,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }

    /**
     * 状态的切换（代理商票列表里面商品的上下架状态）
     */
    public function changeStatus()
    {
        // status 1=上架  2=下架
        $id = $this->request->request('id');
        $status = $this->request->request('status');
        $res = AgentProModel::upAgent($id,['status'=>$status]);
        if($res){
            $this->success('操作成功',$res,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }

    /**批量删除 代理商品列表   **/
    public function delAll()
    {
        $ids = $this->request->request('ids');
        $ids = trim($ids,',');
        $ids_arr = explode(',',$ids);
        $res = AgentProModel::delAll($ids_arr);
        if($res){
            $this->success('操作成功',$res,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**
     * 供货商信息
     * 代理管理
     */
    public function getMessage()
    {
        //   status=normal(正常的)
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 8); //每页显示数量
        $user = $this->auth->getUser(); //获取用户信息
        $data = AgentProModel::getSupper($user['id'],$page,$size);  //获取代理商的用户ID
        if($data){
            $this->success('操作成功',$data,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**
     * 供货产品(我是供货商)
     */
    public function AgentPros()
    {
        $condition = [];
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 8); //每页显示数量
        $categorys_name = isset($_POST['categorys_name']) ? $_POST['categorys_name'] : '';
        if(!empty($categorys_name)){
            $condition['c.categorys_name'] = ['like','%'.$categorys_name.'%'];
        }
        $pro_name = isset($_POST['product_name']) ? $_POST['product_name'] : '';
        if(!empty($pro_name)){
            $condition['a.product_name'] = ['like','%'.$pro_name.'%'];
        }
        $user = $this->auth->getUser();           //供货商信息
        $data = AgentProModel::getAhentList($user['id'],$condition,$page,$size);
        if($data){
            $this->success('操作成功',$data,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**
     * 供货商品列表里的该商品是否供货的开关
     */
    public function is_agentPro()
    {
        $pro_id = $this->request->request('pro_id');
        $is_agent = $this->request->request('is_agent');
        $res = Product::UpdatePro($pro_id,['is_agent'=>$is_agent]);
        if($res){
            $this->success('success',$res,'200');
        }else{
            $this->error('error','','-1');
        }
    }

    /**
     * 我是供货商代理管理中商户状态的切换
     */
    public function shopStuChange()
    {
        $user = $this->auth->getUser();           //用户信息
        $uid = $this->request->request('agent_id');
        $status = $this->request->request('status');    //开=1、关=0
        $data = UsersModel::upAgentUid($user['id'],$uid,$status);
        if($data){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }

    /**
     * 我是供货商里的商品列表的商品详情
     */
    public function supplyProInfo()
    {
        $pro_id = $this->request->request('id');
        $data = Product::getProInfo($pro_id);
        if($data){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**
     * 我是供货商里的商品列表的商品修改 编辑
     */
    public function supplyProUpdate()
    {
        $pro_id = $this->request->request('id');
        $data = request()->except(['token','id']);
        $res = Product::UpdatePro($pro_id,$data);
        if(empty($data)){
            $this->error('参数不能为空.....');
        }
        if($res){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('暂未修改数据','','-1');
        }
    }

    /**
     * 我是供货商里的商品列表的商品 删除
     */
    public function delSupplyPro()
    {
        $pro_id = $this->request->request('pro_id');
        $data = Product::delProduct($pro_id);
        if($data){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }
}