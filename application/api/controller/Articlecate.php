<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/7/5
 * Time: 17:43
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Articlecate as ArticlecateModel;

class Articlecate extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    //添加文章分类
    public function addArticlaCate()
    {
        $data['cate_name'] = $this->request->request('cate_name');
        $res = ArticlecateModel::addCate($data);
        if($res){
            $this->success('添加成功',$res,'200');
        }else{
            $this->error('添加失败','','-1');
        }
    }

    //文章列表
    public function articlaCateList()
    {
        $res = ArticlecateModel::CateList();
        if($res){
            $this->success('操作成功',$res,'200');
        }else{
            $this->error('暂时无数据','','-1');
        }
    }

    //修改文章分类
    public function upCate()
    {
        $id = $this->request->request('id');
        $cate_name = $this->request->request('cate_name');
        $data['cate_name'] = $cate_name;
        $res = ArticlecateModel::upCate($id,$data);
        if($res){
            $this->success('操作成功',$res,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }

    //删除
    public function delCate()
    {
        $id = $this->request->request('id');
        $res = ArticlecateModel::delCate($id);
        if($res){
            $this->success('操作成功',$res,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }
}