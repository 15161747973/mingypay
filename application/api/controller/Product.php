<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/5/23
 * Time: 15:46
 */
namespace app\api\controller;

use app\common\controller\Api;
use app\api\model\Product as ProductModel;
use app\api\model\Cardinfo as CardinfoModel;
use app\api\model\Categorys;
use think\Request;

class Product extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }
    /**查看禁售商品**/
    public function stopSell()
    {
        $list = ProductModel::getStop();
        if($list){
            $this->success('获取成功');
        }else{
            $this->error('暂无数据');
        }
    }

    /**商品列表**/
    public function listProduct()
    {
        $condition = [];
        $user = $this->auth->getUser(); //获取用户信息
        $condition['p.uid'] = $user['id'];
        $cate_name = isset($_POST['categorys_name']) ? $_POST['categorys_name'] : '';
        $product_name = isset($_POST['product_name']) ? $_POST['product_name'] : '';
        if(!empty($cate_name)){
            $condition['categorys_name'] = ['like','%'.$cate_name.'%'];
        }
        if(!empty($product_name)){
            $condition['product_name'] = ['like','%'.$product_name.'%'];
        }
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量
        $data = ProductModel::getlist($page,$size,$condition);
        if($data){
            $this->success('操作成功！',$data,'0');
        }else{
            $this->error('暂无数据！','','-1');
        }
    }


    /**添加商品**/
    public function addProduct(Request $request)
    {
        $user = $this->auth->getUser(); //获取用户信息
        if(!$user){
            $this->error('未登录...');
        }
        $data = $request->except(['token']);
        $data['uid'] = $user['id'];
      	if(!$data['categorys_id']){
        	$this->error('请选择商品分类或添加商品分类');
        }
      	if($data['sell_price'] <$data['cost_price']){
        	$this->error('成本价格不能大于销售价格');
        }
      	if($data['above_num'] <$data['low_num']){
        	$this->error('起购数量不能大于限购数量');
        }
      	$proInfo = db('product')->where(['product_name'=>$data['product_name']])->find();
        if($proInfo){
            $this->error('该商品已存在请重新添加');
        }
        if($data['is_agent'] == '1'){
            $data['agent_code'] = 'AE'.time().rand(1000,9999);
//            $data['is_agent_product'] = '1';
        }
       	if($data['is_getCard_pass'] == '1'){
            $data['visit_pass'] = $this->request->request('visit_pass');
        }
        $data['create_time'] = time();
        $res = ProductModel::addPro($data);
        if($res){
            $this->success('添加成功',$data,'200');
        }else{
            $this->error('添加失败','','-1');
        }
    }

    /**商品详情**/
    public function productInfo()
    {
        $id = $this->request->request('id');
        if(!$id){
            $this->error('必填参数不能为空');
        }
        $res = ProductModel::getProInfo($id);
        if($res){
            $this->success('操作成功',$res,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }
    /**编辑商品信息**/
    public function upProduct(Request $request)
    {
        $data = $request->except(['token']);
        if($data['is_agent'] == '1'){
            $data['agent_code'] = 'AE'.time().rand(1000,9999);
//            $data['is_agent_product'] = '1';
        }elseif ($data['is_agent'] == '0'){
            $data['agent_code'] = '';
        }
      	if($data['sell_price'] <$data['cost_price']){
        	$this->error('成本价格不能大于销售价格');
        }
      	if($data['above_num'] <$data['low_num']){
        	$this->error('起购数量不能大于限购数量');
        }
      	//if($data['categorys_id']){
          //  $cateArr = Categorys::getCateName($data['categorys_id']);
            //$data['categorys_name'] = $cateArr['categorys_name'];
        //}
        $res = ProductModel::UpdatePro($data['id'],$data);
        
        if($res){
            $this->success('修改成功',$data,'200');
        }else{
            $this->error('未修改数据','','-1');
        }
    }


    /**商品删除**/
    public function delPro()
    {
        header("Access-Control-Allow-Origin: *");
        $id = $this->request->request('id');
        $data = CardinfoModel::cardPros($id);
        if($data){
            $this->error('商品下有未售卡，暂且不能删除');
        } 
      	$ddd = CardinfoModel::deleteCard($id);
        $res = ProductModel::delProduct($id);
        if($res){
            $this->success('删除成功',$data,'200');
        }else{
            $this->error('删除失败','','-1');
        }
    }

    /**商品的上下架**/
    public function shelf()
    {
        $status = $this->request->request('status');
        $id = $this->request->request('id');
        if($status == '2'){
            $mess = '下架成功';
        }elseif ($status == '1'){
            $mess = '上架成功';
        }
        $res = ProductModel::shelfStatus($id,$status);
        if($res){
            $this->success('操作成功',$mess,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }

    /**商品的卡库存**/
    public function cardPro()
    {
        $product_id = $this->request->request('product_id');
        $list = CardinfoModel::cardPros($product_id);
        if($list){
            $this->success('操作成功',$list,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }
  	
  	/** 根据类别id和商品id获取商品的信息 **/
  	public function getProInfoByCateAndId()
    {
      	$cateId = $this->request->request('cate_id');
      	$pro_id = $this->request->request('pro_id');
      	$res = ProductModel::getInfoByUCPid($cateId,$pro_id);
      	if($res){
            $this->success('操作成功',$res,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }
}