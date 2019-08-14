<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/6/28
 * Time: 11:47
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Img as ImgModel;

class Img extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    public function addImg()
    {
//        $user = $this->auth->getUser(); //获取用户信息
//        $data['uid']  = $user['id'];
//        $data['type'] = $this->request->request('type');
//        $res = ImgModel::getInfo($data['uid'],$data['type']);
//        if($res){
//            $this->error('该类型图片已存在，不得重复添加');
//        }
//        $img = $this->request->request('file');
//        $url = $this->upload($img);
//        $data['image'] = config('site.url')."fakawang".$url;
//        halt($data);
    }


//    public function upload($file){
////        $file = request()->file('file');
////        halt($file);
//        // 移动到框架应用根目录/uploads/ 目录下
//        $info = $file->move( '../uploads');
//        $returnUrl='';
//        if($info){
//            $returnUrl='/uploads/'.$info->getSaveName();
//            $this->success('上传成功',$returnUrl,'200');
//        }else{
//            $this->error($file->getError());
//        }
//    }
}