<?php

namespace app\api\controller;

use AliSend\AliSendSms;
use app\common\controller\Api;
use app\common\library\Sms as Smslib;
use app\common\model\User;
use app\api\model\Users as UsersModel;
use app\api\model\Sms as SmsModel;
use app\common\library\REST;

require_once EXTEND_PATH . 'Aliyun/api_demo/SmsDemo.php';

/**
 * 手机短信接口
 */
class Sms extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    /**
     * 发送验证码
     *
     * @param string $mobile 手机号
     * @param string $event 事件名称
     */
    public function send()
    {
        $mobile = $this->request->request("mobile");
        $event = $this->request->request("event");
        $event = $event ? $event : 'register';

        if (!$mobile || !\think\Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号不正确'));
        }
        $last = Smslib::get($mobile, $event);
        if ($last && time() - $last['createtime'] < 60) {
            $this->error(__('发送频繁'));
        }
        $ipSendTotal = \app\common\model\Sms::where(['ip' => $this->request->ip()])->whereTime('createtime', '-1 hours')->count();
        if ($ipSendTotal >= 5) {
            $this->error(__('发送频繁'));
        }
        if ($event) {
            $userinfo = User::getByMobile($mobile);
            if ($event == 'register' && $userinfo) {
                //已被注册
                $this->error(__('已被注册'));
            } elseif (in_array($event, ['changemobile']) && $userinfo) {
                //被占用
                $this->error(__('已被占用'));
            } elseif (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }
        }
        $ret = Smslib::send($mobile, null, $event);
        if ($ret) {
            $this->success(__('发送成功'));
        } else {
            $this->error(__('发送失败'));
        }
    }

    /**
     * 检测验证码
     *
     * @param string $mobile 手机号
     * @param string $event 事件名称
     * @param string $captcha 验证码
     */
    public function check()
    {
        $mobile = $this->request->request("mobile");
        $event = $this->request->request("event");
        $event = $event ? $event : 'register';
        $captcha = $this->request->request("captcha");

        if (!$mobile || !\think\Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号不正确'));
        }
        if ($event) {
            $userinfo = User::getByMobile($mobile);
            if ($event == 'register' && $userinfo) {
                //已被注册
                $this->error(__('已被注册'));
            } elseif (in_array($event, ['changemobile']) && $userinfo) {
                //被占用
                $this->error(__('已被占用'));
            } elseif (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }
        }
        $ret = Smslib::check($mobile, $captcha, $event);
        if ($ret) {
            $this->success(__('成功'));
        } else {
            $this->error(__('验证码不正确'));
        }
    }


    //获取验证码
    public function SendSms($event="register",$mobile,$type,$order_num='')
    {
        header("Access-Control-Allow-Origin: *");
        //$event = $this->request->request("event");
        //$event = $event ? $event : 'register';
        //$mobile = $this->request->request('mobile');
        //$type = $this->request->request('type');
        $ip = request()->ip();
        $time = time();
        if(in_array($event, ['register', 'resetPwd','removeSafe','delPayMess','resetMobile'])){
            $rande = rand(1000,9999);
            //register:1=注册；resetPwd:3=重置密码；buyNotice:2=买家购买通知；stockFew:4=库存不足；removeSafe:5=解除安全模式 delPayMess:6=删除收款信息 resetMobile:7=修改绑定手机号
            if($event){
                $userinfo = UsersModel::getByMobile($mobile);
                if ($event == 'register' && $userinfo) {
                    //已被注册
                    $this->error(__('已被注册'));
                } elseif (in_array($event, ['buyNotice', 'resetPwd','stockFew','removeSafe','delPayMess']) && !$userinfo) {
                    //未注册
                    $this->error(__('未注册'));
                }
            }
            $data['mobile'] = $mobile;
            $data['code'] = $rande;
            $data['ip'] = $ip;
            $data['event'] = $event;
            $data['createtime'] = $time;
            SmsModel::addSend($data);
            set_time_limit(0);
            header('Content-Type: text/plain; charset=utf-8');
            $SmsDemo = new \SmsDemo();
            $response = $SmsDemo->sendSms($mobile, ['code'=>$rande], $type);
        }elseif ($event == 'buyNotice'){
            //$order_num = $this->request->request('order_num');
            $rande = $order_num;
//            $orderInfo = Order::getInfo($order_num);
            set_time_limit(0);
            header('Content-Type: text/plain; charset=utf-8');
            $SmsDemo = new \SmsDemo();
            $response = $SmsDemo->sendSms($mobile, ['code'=>$order_num], $type);
        }elseif ($event == 'stockFew'){
//            TODO商品库存不足（使用场景不确定）
        }
        if($response){
            $this->success('发送成功',$rande,'200');
        }else{
            $this->error('发送失败','','-1');
        }
    }

    /**验证码验证**/
    public function Sms_check($mobile,$captcha,$event)
    {
        $event = $event ? $event : 'register';
        if (!$mobile || !\think\Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号不正确'));
        }
        if($event){
            $userinfo = UsersModel::getByMobile($mobile);
            if ($event == 'register' && $userinfo) {
                //已被注册
                $this->error(__('已被注册'));                                     //收款信息
            } elseif (in_array($event, ['buyNotice', 'resetPwd','stockFew','removeSafe','delPayMess','resetMobile']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }
        }
        $ret = Smslib::check($mobile, $captcha, $event);
        if ($ret === true) {
            return 1;
        } else {
            $this->error(__('验证码不正确','','-1'));
        }
    }
}
