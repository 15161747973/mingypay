<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/22
 * Time: 9:52
 */
namespace app\api\model;

use app\common\controller\Api;
use app\api\model\Cardinfo as CardInfoModel;
use app\api\model\Gatherinfo as GatherModel;
use app\api\model\Agentproduct;
use app\api\model\Order;
use think\Model;

class Users extends Model
{
    public static function UpToken($id,$token)
    {
        return self::where('id','=',$id)->update(['token'=>$token]);
    }
    //获取余额
    public static function getmoney()
    {

    }

    //修改密码
    public static function upPwd($id,$pass,$salt)
    {
        return db('user')->where(['id'=>$id,'is_freeze'=>'0'])->update(['passowrd'=>$pass,'salt'=>$salt]);
    }

    //通过手机号获取用户信息
    public static function getByMobile($mobile)
    {
        return db('user')->where(['mobile'=>$mobile,'is_freeze'=>'0'])->find();
    }

    public static function getField($id,$field)
    {
        return db('user')->where(['id'=>$id,'is_freeze'=>'0'])->field($field)->find();
    }

    public static function getMess($uid)
    {
        return db('user')->where(['id'=>$uid,'is_freeze'=>'0'])->field('id,shop_name,qq,status')->find();
    }

    //获取用户信息
    public static function getUserInfo($uid)
    {
        return db('user')->where(['id'=>$uid,'is_freeze'=>'0'])->find();
    }

    //获取推广列表
    public static function PopList($uid,$page,$size)
    {
        $data['res'] = db('user')->where(['refer_id'=>$uid,'is_freeze'=>'0'])->page($page,$size)->field('id,username,email,mobile,FROM_UNIXTIME(createtime) as createtime,refer_id')->select();
        $data['count'] = db('user')->where(['refer_id'=>$uid,'is_freeze'=>'0'])->count();
        return $data;
    }

    public static function UpData($uid,$data)
    {
        return db('user')->where(['id'=>$uid,'is_freeze'=>'0'])->update($data);
    }
    //修改邮箱认证状态
    public static function upApprove($uid,$data)
    {
        return db('user')->where(['id'=>$uid,'is_freeze'=>'0'])->update($data);
    }

    public static function getPhoByE($email,$field='')
    {
        return db('user')->where(['email'=>$email,'is_freeze'=>'0'])->value($field);
    }

    //根据邮箱获取信息
    public static function getInfoByE($email)
    {
        return db('user')->where(['email'=>$email,'is_freeze'=>'0'])->find();
    }

    //今日注册
    public static function todayRegister($time)
    {
        return db('user')->where(['is_freeze'=>'0'])->whereTime('createtime',$time)->count();
    }
    //冻结账户
    public static function freezeUser()
    {
        return db('user')->where(['is_freeze'=>'1'])->count();
    }
    //未审核用户
    public static function noCheck()
    {
        return db('user')->where(['is_freeze'=>'0','is_check'=>'0'])->count();
    }

    //修改提现后金额
    public static function upMoneyInfo($uid,$data)
    {
        return db('user')->where(['id'=>$uid])->update($data);
    }

    //获取用户信息
    public static function getUsersList($page,$size,$condition)
    {
        $data['res'] = db('user')->where($condition)->page($page,$size)->select();
        if($data['res']){
            $data['count'] = db('user')->where($condition)->count();
        }
        return $data;
    }

    //匹配对应的收款信息
    public static function getPayeeInfo($condition,$page,$size)
    {
        $res['users'] = db('user')->where($condition)->page($page,$size)->field('id')->select();
        if($res['users']){
            foreach ($res['users'] as $key=>$val){
                $res['users'][$key]['payeeInfo'] = GatherModel::getInfo($val['id']);
            }
        }
        $res['count'] = db('user')->where($condition)->count();
        return $res;
    }

    //根据商户授权码获取该商户下可代理额商品
    public static function getUserProData($code,$page,$size)
    {
        $data['info'] = db('user u')
            ->join('product p','u.id=p.uid','left')
            ->where(['u.auth_code'=>$code])
            ->where(['p.is_agent'=>'1'])
            ->page($page,$size)
            ->field('p.id,p.product_name,p.num,p.sell_price,p.create_time')
            ->select();
        if($data['info']){
            $data['count'] = db('user u')
                ->join('product p','u.id=p.uid','left')
                ->where(['u.auth_code'=>$code])
                ->where(['p.is_agent'=>'1'])
                ->count();
        }
        return $data;
    }

    /**
     * @param $uid
     * @param $agent_id
     * @param $status
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 供货商控制代理商代理开关
     */
    public static function upAgentUid($uid,$agent_id,$status)
    {
        $info = db('user')->where(['id'=>$uid])->find();
        if($status == '0'){
            if(!$info['stop_agent_uid']){
                $data['stop_agent_uid'] = $agent_id;
            }else{
                $data['stop_agent_uid'] = $info['stop_agent_uid'].','.$agent_id;
            }
            Agentproduct::upAgentSta($uid,$agent_id,$status);
            $res = self::upApprove($uid,$data);
            Agentproduct::upStatus($uid,$agent_id); //修改该商户下所有代理商品未下架
        }else if($status == '1'){
            $agent_uid_arr = explode(',',$info['stop_agent_uid']);
            if(in_array($agent_id,$agent_uid_arr)){
                foreach($agent_uid_arr as $k=>$v){
                    if($v == $agent_id){
                        unset($agent_uid_arr[$k]);
                    }
                }
                $str_id = implode($agent_uid_arr);
                if(empty($str_id)){
                    $str_id= '';
                }
                $data['stop_agent_uid'] = $str_id;
            }
            $res = self::upApprove($uid,$data);
        }
        return $res;
    }

    public static function infoByAuthcode($code)
    {
        return db('user')->where(['auth_code'=>$code])->find();
    }
  
  	public static function getIdsByPrefreid($uid)
    {
        $data = db('user')->where(['refer_id'=>$uid])->field('id')->select();
        $arr = [];
        foreach($data as $key=>$val){
            $arr[] = $val['id'];
        }
        return $arr;
    }
}