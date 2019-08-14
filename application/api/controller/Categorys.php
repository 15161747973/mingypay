<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/5/23
 * Time: 11:47
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Categorys as CategorysModel;
use app\api\model\Product as ProductModel;
use app\common\model\Category;

class Categorys extends Api
{
    protected $NeedLogin = '*';
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /**分类列表**/
    public function listCate()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量
        $uid =$user['id'];
        $data = CategorysModel::AllCate($page,$size,$uid);  //后台按score展示（值越大越靠前）
        if($data){
            $this->success('操作成功！',$data,'0');
        }else{
            $this->error('暂无数据！');
        }
    }

    /**添加分类**/
    public function addCate()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $data['categorys_name']   = $this->request->request('categorys_name');
        $data['score']  = $this->request->request('score');
        $data['uid'] = $user['id'];
      	$cateI = CategorysModel::getCateInfoByName($data['categorys_name'],$data['uid']);
      	if($cateI){
        	$this->error('分类已存在，请重新添加');
        }
        if(!$data['categorys_name']){
            $this->error('参数类型填写有误！！');
        }
        $data['create_time'] = time();
        $res = CategorysModel::addC($data);
        if($res){
            $this->success('添加成功！',$res,'200');
        }else{
            $this->error('添加失败！');
        }
    }

    /**类别详情**/
    public function cateInfoId()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $id = $this->request->request('id');
        if (!$id) {
            $this->error('必填参数不能为空');
        }
        $data = CategorysModel::getInfoId($user['id'],$id);
        if($data){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('操作失败！','','-1');
        }
    }
    /**编辑分类**/
    public function upCate()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $data['categorys_name']   = $this->request->request('categorys_name');
        $data['score']  = $this->request->request('score');
        $id  = $this->request->request('id');
        $info = CategorysModel::getInfoId($user['id'],$id);
        if($info['categorys_name']==$data['categorys_name'] && $info['score']==$data['score']){
            $this->error('你未做任何修改');
        }
        if (!$data['categorys_name'] || !$data['score']) {
            $this->error("必填参数不能为空");
        }
        $res = CategorysModel::UpCategory($data,$id);
        if($res){
            $this->success('修改成功',$data,'200');
        }else{
            $this->error('修改失败','','-1');
        }
    }

    /**删除分类**/
    public function delCate()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $id = $this->request->request('id');
        $data = ProductModel::getProByCateId($user['id'],$id);
        if($data){
            $this->error('该分类下有产品不能删除');
        }
        $res = CategorysModel::delCategory($user['id'],$id);
        if($res){
            $this->success('删除成功',$res,'200');
        }else{
            $this->error('删除失败','','-1');
        }
    }

    /**类别所属的商品**/
    public function cateIdPro()
    {
        header("Access-Control-Allow-Origin: *");
        $user = $this->auth->getUser(); //获取用户信息
        $id = $this->request->request('id');//类别的id
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量
        $data = CategorysModel::getInfoByCateId($user['id'],$id,$page,$size);
        if($data){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**
     * 用户下所属分类
     */
    public function userCate()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $data = CategorysModel::cates($user['id']);
        if($data){
            $this->success('success',$data,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    //类别商品信息
    public function CCCCC()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $data = CategorysModel::getGG($user['id']);
        if($data){
            $this->success('success',$data,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }


	//获取分类及分类下商品
    public function CatePro()
    {   header("Access-Control-Allow-Origin: *");
        $user = $this->auth->getUser(); //获取用户信息
        $uid = $user['id'];
        $cate_id = $this->request->request('id');   //类别的id
        $data = CategorysModel::havePro($uid,$cate_id);
        if($data){
            $this->success('success',$data,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }
}