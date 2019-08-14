<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/14
 * Time: 11:07
 */
namespace app\api\controller;
use app\common\controller\Api;
use think\Request;
/*文件上传*/
class Upload extends Api
{
    protected $noNeedLogin =['upload'];
    /*post上传*/
    public function upload(Request $request){
        $file = request()->file('file');
//        halt($file);
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move( '../uploads');
        $returnUrl='';
        if($info){
            $returnUrl='/uploads/'.$info->getSaveName();
            $this->success('上传成功',$returnUrl,'200');
        }else{
            $this->error($file->getError());
        }
    }
}