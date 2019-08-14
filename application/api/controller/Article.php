<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/7/6
 * Time: 9:00
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Article as ArticleModel;

class Article extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /** 获取文章列表 **/
    public function ArticleList()
    {
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量
        $data = ArticleModel::getList($page,$size);
        if($data){
            $this->success('获取成功',$data,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /** 添加文章 **/
    public function addArticle()
    {
        $data['cate_id'] = $this->request->request('cate_id');
        $data['title'] = $this->request->request('title');
        $data['score'] = $this->request->request('score');
        $data['issue_time'] = $this->request->request('issue_time');
        $data['content'] = $this->request->request('content');
        $data['is_auto_pop'] = $this->request->request('is_auto_pop');
        $data['create_time'] = time();
        $res = ArticleModel::addArticle($data);
        if($res){
            $this->success('添加成功',$data,'200');
        }else{
            $this->error('添加失败','','-1');
        }
    }

    /** 文章详情 **/
    public function articleInfo()
    {
        $id = $this->request->request('id');
        $res = ArticleModel::getInfo($id);
        if($res){
            $this->success('获取成功',$res,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /** 文章修改 **/
    public function upArticle()
    {
        $id = $this->request->request('id');
        $data = input('post.');
        $res = ArticleModel::upArticle($id,$data);
        if($res){
            $this->success('修改成功',$res,'200');
        }else{
            $this->error('修改失败','','-1');
        }
    }

    /** 文章删除 **/
    public function delArticle()
    {
        $id = $this->request->request('id');
        $res = ArticleModel::delArticle($id);
        if($res){
            $this->success('删除成功',$res,'200');
        }else{
            $this->error('删除失败','','-1');
        }
    }
}