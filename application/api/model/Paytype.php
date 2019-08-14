<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/6/29
 * Time: 15:21
 */

namespace app\api\model;


use think\Model;
use app\api\model\Order as OrderModel;

class Paytype extends Model
{
    public static function getType($condition)
    {
        return db('pay_type')->where($condition)->select();
    }

    public static function getTypes($condition)
    {
        $types = db('pay_type')->where($condition)->select();
        if($types){
            $orderData = [];
            foreach($types as $k=>$v){
                $res = db('order')->where(['status'=>'2','pay_type'=>$v['id']])->group('uid')->field('uid,SUM(user_price) as price')->select();
                $orderData[$k]['id'] = $v['id'];
                $orderData[$k]['type'] = $v['name'];
                $orderData[$k]['orderNum'] = OrderModel::orderNums(['pay_type'=>$v['id']]);
                $orderData[$k]['orderPayNum'] = OrderModel::orderNums(['pay_type'=>$v['id'],'status'=>'2']);
                $orderData[$k]['orderNoPayNum'] = OrderModel::orderNums(['pay_type'=>$v['id'],'status'=>'1']);
                $orderData[$k]['orderMoney'] = OrderModel::orderMoney(['pay_type'=>$v['id']],'price');
                $orderData[$k]['orderPayMoney'] = OrderModel::orderMoney(['pay_type'=>$v['id'],'status'=>'2'],'pay_price');
                $orderData[$k]['orderUserMoney'] = OrderModel::getInMon(['pay_type'=>$v['id'],'status'=>'2'],'0');
                $orderData[$k]['orderTabMoney'] = OrderModel::getInMon(['pay_type'=>$v['id'],'status'=>'2'],'1',$v['id']);
            }
        }
        return $orderData;
    }
}