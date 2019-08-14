<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/22
 * Time: 11:57
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Notice as NoticeModel;

class Notice extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 结算公告数据和系统公告数据
     */
    public function accountNotice()
    {
        $status = $this->request->request('status');
        $page = input('post.page', 1); //页数
        $size = input('post.size', 5); //每页显示数量
        if($status == ''){
            $condition = [];
        }else{
            $condition = ['status'=>$status];
        }
        $list = NoticeModel::getList($condition,$page,$size);
        if($list){
            $this->success('操作成功！',$list,'0');
        }else{
            $this->success('操作失败！','','-1');
        }

    }

    /**添加公告**/
    public function addNotice()
    {
        $data['title'] = $this->request->request('title');
        $data['content'] = $this->request->request('content');
        $data['status'] = $this->request->request('status');    //1=结算公告 2=系统公告
        $data['create_time'] = time();
        $res = NoticeModel::addNotice($data);
        if($res){
            $this->success('添加成功',$data,'200');
        }else{
            $this->error('添加失败','','-1');
        }
    }

    /** 新闻动态 **/
    public function articleList()
    {
        $list = db('article')->field('title,content,FROM_UNIXTIME(create_time) as create_time')->select();
        if($list){
            $this->success('操作成功！',$list,'0');
        }else{
            $this->success('操作失败！','','-1');
        }
    }
}