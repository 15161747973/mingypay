<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/8/8
 * Time: 15:26
 */

namespace app\api\model;


use think\Model;
use think\db;

class Coupon extends Model
{
    public static function addCoupon($data)
    {
        return self::insert($data,true);
    }

    public static function getCouponList($uid,$page,$size,$condition)
    {
        $data['res'] = db('coupon c')
            ->join('product p','c.pro_id=p.id','left')
            ->where(['c.uid'=>$uid])
          	->where($condition)
            ->page($page,$size)
            ->field('c.id,c.code,p.product_name,c.price,FROM_UNIXTIME(c.create_time) as create_time,FROM_UNIXTIME(c.end_time) as end_time,c.status')
            ->select();
        if($data['res']){
            $data['count'] = db('coupon c')
                ->join('product p','c.pro_id=p.id','left')
                ->where(['c.uid'=>$uid])
              	->where($condition)
                ->count();
        }
        return $data;
    }
  
  	public static function getInfoByCode($code,$pro_id)
    {
        return self::where(['code'=>$code,'pro_id'=>$pro_id,'status'=>'1'])->find();
    }
  
  	public static function getInfoById($id)
    {
        return db('coupon c')
            ->join('product p','c.pro_id=p.id','left')
          	->join('categorys ca','p.categorys_id=ca.id','left')
            ->where(['c.id'=>$id])
            ->field('c.*,p.product_name,ca.categorys_name,FROM_UNIXTIME(c.create_time) as create_time,FROM_UNIXTIME(c.end_time) as end_time')
            ->find();
    }
  
  	public static function UpdateCou($id,$data)
    {
        return self::where(['id'=>$id])->update($data);
    }
  
  	public static function delCou($id)
    {
        return self::where(['id'=>$id])->delete();
    }
  	
  	public static function delAllCoupon($id_arr)
    {
      	$j = 0;
      	for($i=0;$i<count($id_arr);$i++){
        	self::where(['id'=>$id_arr[$i]])->delete();
          	$j++;
        }
        return $j;
    }
  
}