<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/6/28
 * Time: 17:26
 */

namespace app\api\controller;

use app\common\controller\Api;
use app\api\model\Loginlog as LoginlogModel;

class Loginlog extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    //添加登录日志
    public function addLoginLog()
    {
        $user = $this->auth->getUser();
        $data['uid'] = $user['id'];
        $data['type'] = isset($_POST['type']) ? $_POST['type'] : '1';
        $data['ip'] = $this->request->request('ip');
        $region = $this->request->request('region');
        $city = $this->request->request('city');
        $data['address'] = $region.$city;
        $data['time'] = time();
        $res = LoginlogModel::addData($data);
        if($res){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }
    // 最近登录记录
    public function loginLog()
    {
        $user = $this->auth->getUser();
        $page = input('post.page', 1); //页数
        $size = input('post.size', 5); //每页显示数量
        $data = LoginlogModel::loginLog($user['id'],$page,$size);
        if($data){
            $this->success('操作成功',$data,'0');
        }else{
            $this->error('操作失败','','-1');
        }
    }
}