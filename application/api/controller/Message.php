<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/7/22
 * Time: 15:16
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Message as MessageModel;
use app\api\model\Img;
use think\Request;
use function Complex\theta;

class Message extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /** 添加聊天 **/
    public function addMessage()
    {
        $user = $this->auth->getUser();
        $data['order_num'] = $this->request->request('order_num');
        if($user){
            $data['uid'] = $user['id'];
        }else{
            $data['uid'] = '';
        }
        $data['content'] = $this->request->request('content');
        $data['create_time'] = time();
        $res = MessageModel::addData($data);
        if($res){
            $this->success('提交成功',$data,'200');
        }else{
            $this->error('提交失败','','-1');
        }
    }

    /** 聊天记录 **/
    public function messageList()
    {
        $order_num = $this->request->request('order_num');
        $data = MessageModel::getMessList($order_num);
        if($data){
            $this->success('获取成功',$data,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**
     * @param Request $request
     * 上传与卖家(买家)凭证图片
     */
    public function upImgUrl(Request $request)
    {
        $file = request()->file('file');
        $info = $file->move( '../public/uploads');
        $returnUrl='';
        if($info){
            $returnUrl='/uploads/'.$info->getSaveName();
        }
        $user = $this->auth->getUser();
        if($user){
            $data['uid'] = $user['id'];
        }else{
            $data['uid'] = '';
        }
        $data['order_num'] = $this->request->request('order_num');      // 订单号
        $data['type'] = $this->request->request('type');                // 2=图片类型
        $url = rtrim(config('site.url'),'/');
        $data['image'] = $url.$returnUrl;
        $data['create_time'] = time();
        $res = Img::addImg($data);
        if($res){
            $this->success('操作成功',$data['image'],'200');
        }else{
            $this->error('失败','','-1');
        }
    }
}