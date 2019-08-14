<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/6/25
 * Time: 10:49
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Emailcode;

class Contro extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    //定时任务，发送邮件验证码后两分钟删除
    public function MinuteDel()
    {
        $id = $this->request->request('id');
        $time = time();
        $info = Emailcode::getInfoById($id);
        $new_time = $info['create_time']+60;
        if($time>$new_time){
            $res = Emailcode::delEmailCode($id);
            if($res){
                $this->success('操作成功','','200');
            }else{
                $this->error('操作失败','','-1');
            }
        }
    }
}