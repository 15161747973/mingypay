<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/7/18
 * Time: 15:01
 */

namespace app\api\model;


use think\Model;

class Complainorder extends Model
{
    public static function addOrder($data)
    {
        return self::insert($data,true);
    }

    public static function complainOrderList($uid,$order_type,$page,$size,$condition)
    {
      if($order_type == '1'){
        $data['res'] = db('complainorder c')
            ->join('order o','c.order_num=o.order_num','left')
            ->join('agent_product ap','o.pro_id=ap.id','left')
            ->where(['c.uid'=>$uid,'c.order_type'=>'1'])
            ->where($condition)
            ->field('ap.product_name,o.pro_name,c.*,o.pay_price,FROM_UNIXTIME(c.create_time) as create_time')
            ->page($page,$size)
            ->select();
        if($data['res']){
            $order_money = '';
            foreach($data['res'] as $key=>$val){
                $order_money += $val['pay_price'];
            }
            $data['count'] = db('complainorder c')
                ->join('order o','c.order_num=o.order_num','left')
                ->join('agent_product ap','o.pro_id=ap.id','left')
                ->where(['c.uid'=>$uid,'c.order_type'=>'1'])
                ->where($condition)
                ->count();
            $data['freeze_money'] = $order_money;
        }
      }elseif($order_type == '0'){
      	$data['res'] = db('complainorder c')
            ->join('order o','c.order_num=o.order_num','left')
            ->join('product p','o.pro_id=p.id','left')
            ->where(['c.uid'=>$uid,'c.order_type'=>'0'])
            ->where($condition)
            ->field('p.product_name,c.*,o.pay_price,FROM_UNIXTIME(c.create_time) as create_time')
            ->page($page,$size)
            ->select();
        if($data['res']){
            $order_money = '';
            foreach($data['res'] as $key=>$val){
                $order_money += $val['pay_price'];
            }
            $data['count'] = db('complainorder c')
            ->join('order o','c.order_num=o.order_num','left')
            ->join('product p','o.pro_id=p.id','left')
            ->where(['c.uid'=>$uid,'c.order_type'=>'0'])
            ->where($condition)
            ->count();
            $data['freeze_money'] = $order_money;
        }
      }
        return $data;
    }

    public static function getSuppleOrder($uid,$page,$size,$condition)
    {
        $data['res'] = db('complainorder c')
            ->join('order o','c.order_num=o.order_num','left')
            ->join('product p','o.pro_id=p.id','left')
            ->where(['c.supple_id'=>$uid,'c.order_type'=>'1'])
            ->where($condition)
            ->page($page,$size)
            ->field('p.id as pro_id,p.product_name,c.*,FROM_UNIXTIME(c.create_time) as create_time')
            ->select();
        if($data['res']){
            $data['count'] = db('complainorder c')
                ->join('order o','c.order_num=o.order_num','left')
                ->join('product p','o.pro_id=p.id','left')
                ->where(['c.supple_id'=>$uid,'c.order_type'=>'1'])
                ->where($condition)
                ->count();
        }
        return $data;
    }

}