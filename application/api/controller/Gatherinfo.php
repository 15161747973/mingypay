<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/5/27
 * Time: 16:33
 */

namespace app\api\controller;

use app\api\controller\Upload;
use app\common\controller\Api;
use app\api\model\Gatherinfo as GatherModel;
use app\api\model\Img;
use think\db;
use app\api\controller\Sms as SmsContro;
use think\Request;

class Gatherinfo extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * @throws \think\exception\DbException
     * @throws db\exception\DataNotFoundException
     * @throws db\exception\ModelNotFoundException
     * 添加商户收款信息
     */
    public function addInfo()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $data['uid'] = $user['id'];
        $resa = db('gatherinfo')->where('uid','=',$data['uid'])->find();
        if($resa){
            $this->error('请先删除旧数据');
        }
        $data['gath_type'] =  isset($_POST['gath_type']) ? $_POST['gath_type'] : '1';
        $data['account']        = $this->request->request('account');       //收款账户
        $data['account_name']   = $this->request->request('account_name');  //收款人姓名
        $data['idCard']         = $this->request->request('idCard');        //收款人身份证号码
        $data['create_time'] = time();
//        $resq = GatherModel::checkIdCard($data['idCard'],$data['account_name']);
//        if($resq == '-1'){
//            $this->error('身份证验证失败','','-1');
//        }
        $res = GatherModel::addGather($data);
        if($res){
            $this->success('添加成功',$res,'200');
        }else{
            $this->error('添加失败','','-1');
        }
    }

    /**
     * 商户收款信息
     */
    public function getInfo()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $uid = $user['id'];
        $data = GatherModel::getInfo($uid);
        if($data['gath_type']){
            $data['image'] = Img::getField($data['uid']);
        }
        if($data){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**
     * /删除收款信息
     */
    public function delMess()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $res = GatherModel::delPayee($user['id']);
        Img::delImg($user['id']);
        if($res){
            $this->success('删除成功','','200');
        }else{
            $this->error('删除失败','','-1');
        }
    }

    /**
     * @param Request $request
     * 上传收款二维码图片
     */
    public function upImgUrl(Request $request)
    {
        $user = $this->auth->getUser(); //获取用户信息
        $file = request()->file('file');
        $info = $file->move( '../public/uploads');
        $returnUrl='';
        if($info){
            $returnUrl='/uploads/'.$info->getSaveName();
        }
        $data['type'] = $this->request->request('type');    // 1=收款二维码
        $res = Img::getInfo($user['id'],$data['type']);
        if($res){
            $this->error('该类型图片已存在，不得重复添加');
        }
      	$url = rtrim(config('site.url'),'/');
        $data['image'] = $url.$returnUrl;
        $data['uid'] = $user['id'];
        $data['create_time'] = time();
        $res = Img::addImg($data);
        if($res){
            $this->success('操作成功',$data['image'],'200');
        }else{
            $this->error('失败','','-1');
        }
    }
}