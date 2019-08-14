<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/5/24
 * Time: 10:03
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Cardinfo as CardinfoModel;
use app\api\model\Product as ProductModel;

class Cardinfo extends Api
{
    protected $NeedLogin = ['addCard,getList,listCardInfo,delAll'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**加卡**/
    public function addCard()
    {
        $user = $this->auth->getUser();
        $categorys_id = $this->request->request('categorys_id');    //商品ID
        $product_id = $this->request->request('product_id');        //商品ID
        $repeatOut = $this->request->request('is_repeat');          //是否去重0=不去，1=去重
        $data = $_POST['textarea']; //卡密信息
      	$pro_name = ProductModel::getField($product_id,'product_name');
        if(empty($categorys_id)){
            $this->error('请选择分类');
        }elseif(empty($product_id) || $product_id == '0'){
            $this->error('请选择商品...');
          	return false;
        }
        if($repeatOut == '1'){
           $arr_N = explode("\n",$data);//将多个卡密转化成数组
            $ca_arr = [];
	          foreach($arr_N as $key=>$val){
                  if($val == ''){
                      unset($arr_N[$key]);
                  }else{
                      array_unshift($ca_arr,$arr_N[$key]);
                  }
              }
            $arr = array_unique($ca_arr);
            $num = count($arr);     //数组元素的长度
            $res = ProductModel::upStock($product_id,$num);
            for($i=0;$i<$num;$i++){
                $nbspCount = substr_count($arr[$i]," ");
              	$n = $i+1;
                if($nbspCount >3){
                  $this->error('第'.$n.'行卡密输入格式错误添加失败，每行空格最多出现3次');
                }
                $arr1['uid'] = $user['id'];
                $arr1['categorys_id'] = $categorys_id;   //商品ID
                $arr1['product_id'] = $product_id;   //商品ID
              	$arr1['pro_name'] = $pro_name;   //商品名称
                $arr1['create_time'] = time();
              	switch($nbspCount){
                  case '0':
                    $arr1['card_num'] = '';
                    $arr1['card_pwd'] = $arr[$i];
                    $result = db('cardinfo')->where(['product_id'=>$arr1['product_id'],'card_pwd'=>$arr1['card_pwd'],'card_num'=>''])->find();
                    break;
                  case '1':
                    if(strpos($arr[$i]," ")){
                        $array = explode(" ",$arr[$i]);
                        $arr1['card_num'] = $array[0];
                        $arr1['card_pwd'] = $array[1];
                        $result = db('cardinfo')->where(['product_id'=>$arr1['product_id'],'card_num'=>$arr1['card_num'],'card_pwd'=>$arr1['card_pwd']])->find();
                    }
                    break;
                  case '2':
                    if(strpos($arr[$i],"  ")){
                        $array = explode("  ",$arr[$i]);
                        $arr1['card_num'] = $array[0];
                        $arr1['card_pwd'] = $array[1];
                        $result = db('cardinfo')->where(['product_id'=>$arr1['product_id'],'card_num'=>$arr1['card_num'],'card_pwd'=>$arr1['card_pwd']])->find();
                    }
                    break;
                  case '3':
                    if(strpos($arr[$i],"   ")){
                        $array = explode("   ",$arr[$i]);
                        $arr1['card_num'] = $array[0];
                        $arr1['card_pwd'] = $array[1];
                        $result = db('cardinfo')->where(['product_id'=>$arr1['product_id'],'card_num'=>$arr1['card_num'],'card_pwd'=>$arr1['card_pwd']])->find();
                    }
                    break;
                }
              	if($result){
                  $this->error('该卡号已存在，请重新添加...');
                }
                $aa = CardinfoModel::addCard($arr1);
            }
        }else{
        	$arr_N = explode("\n",$data);//将多个卡密转化成数组
            $arr = [];
	          foreach($arr_N as $key=>$val){
                  if($val == ''){
                      unset($arr_N[$key]);
                  }else{
                      array_unshift($arr,$arr_N[$key]);
                  }
              }
            $num = count($arr);     //数组元素的长度
            $res = ProductModel::upStock($product_id,$num);
            for($i=0;$i<$num;$i++){
                $nbspCount = substr_count($arr[$i]," ");
                $n = $i+1;
                if($nbspCount >3){
                    $this->error('第'.$n.'行卡密输入格式错误添加失败，每行空格最多出现3次');
                }
                $arr1['uid'] = $user['id'];
                $arr1['categorys_id'] = $categorys_id;   //商品ID
                $arr1['product_id'] = $product_id;   //商品ID
              	$arr1['pro_name'] = $pro_name;   //商品名称
                $arr1['create_time'] = time();
                switch($nbspCount){
                    case '0':
                    $arr1['card_num'] = '';
                    $arr1['card_pwd'] = $arr[$i];
                    break;
                  case '1':
                    if(strpos($arr[$i]," ")){
                        $array = explode(" ",$arr[$i]);
                        $arr1['card_num'] = $array[0];
                        $arr1['card_pwd'] = $array[1];
                    }
                    break;
                  case '2':
                    if(strpos($arr[$i],"  ")){
                        $array = explode("  ",$arr[$i]);
                        $arr1['card_num'] = $array[0];
                        $arr1['card_pwd'] = $array[1];
                    }
                    break;
                  case '3':
                    if(strpos($arr[$i],"   ")){
                        $array = explode("   ",$arr[$i]);
                        $arr1['card_num'] = $array[0];
                        $arr1['card_pwd'] = $array[1];
                    }
                    break;
                }
             
              $aa = CardinfoModel::addCard($arr1);
            }
        }
        if($aa){
          $this->success('添加成功','','200');
        }else{
          $this->error('添加失败','','-1');
        }
    }

    //获取列表
    public function getList()
    {
        $user = $this->auth->getUser();
        $data = CardinfoModel::getList($user['id']);
        if($data){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }

    /**卡信息列表**/
    public function listCardInfo()
    {
        $user = $this->auth->getUser();
        $condition = [];
        $condition['c.uid'] = $user['id'];
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        if($status){
            $condition['c.status'] = $status;
        }else{
            $condition['c.status'] = ['in',['1','2']];
        }
        $product_name = isset($_POST['product_name']) ? $_POST['product_name'] : '';
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量
        if(!empty($product_name)){
            $condition['product_name'] = ['like','%'.$product_name.'%'];
        }
        $res = CardinfoModel::listCardInfos($page,$size,$condition);
        if($res){
            $this->success('操作成功',$res,'0');
        }else{
            $this->error('暂无数据','-1');
        }
    }

    /**卡信息详情**/
    public function cardInfo()
    {
       $id = $this->request->request('id');
       $info = CardinfoModel::getInfo($id);
       if($info){
           $this->success('操作成功',$info,'200');
       }else{
           $this->error('暂无数据','-1');
       }
    }

    /**卡信息修改**/
    public function updateCard()
    {
        $id = $this->request->request('id');
        $data['card_num'] = $this->request->request('card_num');
        $data['card_pwd'] = $this->request->request('card_pwd');
        $data['update_time'] = time();
        $rees = CardinfoModel::UpdateC($id,$data);
        if($rees){
            $this->success('修改成功',$data,'200');
        }else{
            $this->error('修改失败','','-1');
        }
    }

    /**软删除商品卡**/
    public function delCard()
    {
        $id = $this->request->request('id');
        $pro_id = $this->request->request('pro_id');
        $status = $this->request->request('status');
        $res = CardinfoModel::delC($id);
        if($status == '1'){
            $rrr = ProductModel::UpNUM($pro_id);
        }
        if($res){
            $this->success('删除成功',$res,'200');
        }else{
            $this->error('删除失败','','-1');
        }
    }

    /**批量删除**/
    public function delAll()
    {
        $ids = $this->request->request('ids');
      	$pro_id = $this->request->request('pro_id');
        $ids = rtrim($ids,',');
        $array_id = explode(',',$ids);
      	$num = count($array_id);
        $res = CardinfoModel::delA($array_id);
      	$rrr = ProductModel::UpNUMAll($pro_id,$num);
        if($res){
            $this->success('删除成功',$res,'200');
        }else{
            $this->error('删除失败','','-1');
        }
    }

    /**软删除的数据列表**/
    public function softDelList()
    {
      	$user = $this->auth->getUser();
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量
        $data = CardinfoModel::getSoftList($user['id'],$page,$size);
        if($data){
            $this->success('操作成功',$data,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**恢复软删除数据**/
    public function recoverCard()
    {
        $id = $this->request->request('id');
        $pro_id = $this->request->request('pro_id');
        $data = ProductModel::getProInfo($pro_id);
        if(!$data){
            $this->error('该卡所属的商品不存在，无法恢复');
        }
        $recover = new CardinfoModel();
        $res = $recover->restore(['id'=>$id]);
        if($res){
            CardinfoModel::updateS($id);    //还原后状态改为出售中
          	ProductModel::upNumJia($pro_id,1);
            $this->success('恢复成功',$res,'200');
        }else{
            $this->error('恢复失败','','-1');
        }
    }

    /**批量恢复**/
    public function recoAll()
    {
        $ids = $this->request->request('ids');
        $ids = rtrim($ids,',');
        $array_id = explode(',',$ids);
      	$num = count($array_id);
        $recover = new CardinfoModel();
        $res = $recover->restore(['id'=>['in',$array_id]]);
        if($res){
            CardinfoModel::updateS($array_id);    //还原后状态改为出售中
          	//ProductModel::upNumJia($pro_id,$num);
            $this->success('恢复成功',$res,'200');
        }else{
            $this->error('恢复失败','','-1');
        }
    }

    /**卡密管理  列表**/
    public function cardPwdContro()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $condition['p.uid'] = $user['id'];
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量
        $data = ProductModel::getPro($page,$size,$condition);
        if($data){
            $this->success('操作成功',$data,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }


    /**指定状态的卡密删除**/
    public function cardPwdDelStu()
    {
        $condition = [];
        $user = $this->auth->getUser(); //获取用户信息
        $product_id = $this->request->request('product_id');
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        if(!empty($status)){
            $condition['status'] = $status;
        }else{
            $condition['status'] = ['in',['1','2']];
        }
        $res = CardinfoModel::delCardStu($user['id'],$product_id,$condition);
        if($res){
            $this->success('删除成功',$res,'200');
        }else{
            $this->error('删除失败','','-1');
        }
    }
  
  	/** 彻底删除回收站的数据 **/
  	public function delSoftData()
    {
    	$card_id = $this->request->request('card_id');  //卡密id
        $res = CardinfoModel::realDelData($card_id);
        if($res){
            $this->success('删除成功',$res,'200');
        }else{
            $this->error('删除失败','','-1');
        }
    }
  
  	/** 批量彻底删除回收站的数据 **/
    public function delAllSoftData()
    {
        $card_ids = $this->request->request('card_ids');  //卡密id
        $id_arr = explode(',',$card_ids);
        $res = CardinfoModel::realAllDelData($id_arr);
        if($res){
            $this->success('删除成功',$res,'200');
        }else{
            $this->error('删除失败','','-1');
        }
    }
}