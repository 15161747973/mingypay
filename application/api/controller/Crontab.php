<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/8/1
 * Time: 14:45
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Withdraw as WithdrawModel;
use app\api\model\Users as UsersModel;

class Crontab extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        parent::_initialize();
    }

    /** 定时任务，每天晚上12点执行 **/
    public function withdrawTask()
    {
        $usersInfo = db('user')->select();
        $res = [];
        foreach($usersInfo as $key=>$val){
            if($val['pay_type'] == '2' && $val['can_use_money'] >= '50'){
                $data['uid'] = $val['id'];
                $data['money'] = $val['can_use_money'];
                $data['type'] = '2';
                $data['charge_money'] = '0';
                $data['create_time'] = time();
                $res = WithdrawModel::addApply($data);
                //修改商户可提现余额
                UsersModel::upMoneyInfo($val['id'],['all_money'=>$val['all_money']-$val['can_use_money'],'can_use_money'=> '0.00']);
            }
        }
        if($res){
            $this->success('自动申请成功',$data,'200');
        }else{
            $this->error('自动申请失败','','-1');
        }
    }
  
  	/** 定时任务  **/
    public function delFile()
    {
        $this->delDirAndFile("http://test.mingypay.com/images");
    }


    /**
     * 删除目录及目录下所有文件或删除指定文件
     * @param str $path   待删除目录路径
     * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
     * @return bool 返回删除状态
     */
    function delDirAndFile($path, $delDir = FALSE) {
        $handle = opendir($path);
        if ($handle) {
            while (false !== ( $item = readdir($handle) )) {
                if ($item != "." && $item != "..")
                    is_dir("$path/$item") ? $this->delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
            }
            closedir($handle);
            if ($delDir)
                return rmdir($path);
        }else {
            if (file_exists($path)) {
                return unlink($path);
            } else {
                return FALSE;
            }
        }
    }
  
}