<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/6/29
 * Time: 17:31
 */

namespace app\api\model;


use think\Model;

class Withdraw extends Model
{
    public static function addApply($data)
    {
        return self::insert($data);
    }

    public static function withdrawMon($type,$time)
    {
        if($type == ''){
            $type = ['in',['1','2']];
        }
        return self::where(['type'=>$type])->whereTime('create_time',$time)->field('case when SUM(money) is null then 0 else SUM(money) end as withdrawMoney')->find();
    }

    public static function getList()
    {
//        $data = self::where(['status'=>'1','is_pass'=>'0'])->select();
        $data = db('withdraw w')
            ->join('user u','w.uid=u.id','left')
            ->join('gatherinfo g','w.uid=g.uid','left')
            ->where(['w.is_pass'=>'0'])
            ->field('w.id as apply_id,u.shop_name,u.id,u.all_money,u.can_use_money,g.account_name,g.account,g.gath_type,w.money,w.charge_money,(w.money-w.charge_money) as payMoney,w.create_time,w.status')
            ->select();
        return $data;
    }

    public static function changeStat($apply_id,$data)
    {
        return self::where(['id'=>$apply_id])->update($data);
    }

    public static function getPayList()
    {
        $data = db('withdraw w')
            ->join('user u','w.uid=u.id','left')
            ->join('gatherinfo g','w.uid=g.uid','left')
            ->where(['w.is_pass'=>'1'])
            ->field('u.shop_name,u.id,g.account_name,g.gath_type,g.account,w.money,w.charge_money,(w.money-w.charge_money) as payMoney,w.create_time')
            ->select();
        return $data;
    }

    public static function upData($id,$data)
    {
        return self::where(['id'=>$id])->update($data);
    }

    public static function getApplyList()
    {
        return db('withdraw w')
            ->join('user u','w.uid=u.id','left')
            ->join('gatherinfo g','w.uid=g.uid','left')
            ->field('u.id,u.shop_name,g.gath_type,g.account,g.account_name,w.type,w.money,w.charge_money,w.create_time')
            ->select();
    }

    public static function getDataByUid($uid,$page,$size)
    {
        $data['res'] = db('withdraw w')
            ->join('gatherinfo g','w.uid=g.uid','left')
            ->where(['w.uid'=>$uid])
            ->field('w.*,(w.money-w.charge_money) as getMoney,g.gath_type,FROM_UNIXTIME(w.create_time) as create_time,FROM_UNIXTIME(w.deal_time) as deal_time')
            ->page($page,$size)
            ->select();
        if($data['res']){
            $data['count'] = db('withdraw w')
                ->join('gatherinfo g','w.uid=g.uid','left')
                ->where(['w.uid'=>$uid])
                ->count();
        }
        return $data;
    }
  
  	public static function ToDayWith($uid)
    {
        $data = self::where(['uid'=>$uid])->where(['is_pass'=>['<>','2']])->whereTime('create_time', 'today')->field('money')->select();
        $money = '0';
        if($data){
            foreach($data as $key=>$val){
                $money += $val['money'];
            }
        }else{
            $money = '0';
        }
        return $money;
    }
}