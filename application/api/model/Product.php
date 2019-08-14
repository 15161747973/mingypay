<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/5/23
 * Time: 15:46
 */

namespace app\api\model;

use app\api\model\Categorys as CaregoryModel;
use think\Model;
use think\db;

class Product extends Model
{
    public static function getStop()
    {
        //获取禁售商品
        return self::where(['status'=>'3'])->select();
    }
    //获取商品列表
    public static function getlist($page,$size,$condition)
    {
        $res['data'] = db('product p')
            ->join('categorys c','p.categorys_id=c.id','left')
            ->where($condition)
            ->where(['p.status'=>['in',['1','2']]])
            ->order('product_score DESC')
            ->page($page,$size)
            ->field('p.id as pro_id,p.agent_code,p.product_name,p.sell_price,p.cost_price,p.product_score,p.num,p.sale_num,p.status,c.id as cate_id,c.categorys_name,p.is_getCard_pass,p.visit_pass,p.above_num')
            ->select();
        if($res['data']){
            $res['count'] = db('product p')
                ->join('categorys c','p.categorys_id=c.id','left')
                ->where($condition)
                ->where(['p.status'=>['in',['1','2']]])
                ->count();
        }
        return $res;
    }

    public static function addPro($data)
    {
        return self::insert($data);
    }

    /**根据类别id获取商品**/
    public static function getProByCateId($uid,$id)
    {
        return self::where(['categorys_id'=>$id,'status'=>'1','uid'=>$uid])->order('product_score DESC')->select();
    }
    //获取商品详情
    public static function getProInfo($id)
    {
        return db('product p')
            ->join('categorys c','p.categorys_id=c.id','left')
            ->where(['p.id'=>$id])
            ->field('p.*,c.categorys_name')
            ->find();
//        return self::where(['id'=>$id])->find();
    }

    //编辑商品
    public static function UpdatePro($id,$data)
    {
        return self::where(['id'=>$id])->update($data);
    }

    //删除商品
    public static function delProduct($id)
    {
      	return self::where(['id'=>$id])->delete();
		//return self::where(['id'=>$id])->update(['status'=>'0']);
    }

    //商品的上下架
    public static function shelfStatus($id,$status)
    {
        return self::where(['id'=>$id])->update(['status'=>$status]);
    }

    //修改卡库存
    public static function upStock($id,$count)
    {
        $num = self::where(['id'=>$id])->value('num');
        return self::where(['id'=>$id])->update(['num'=>$count+$num]);
    }

    //获取商品名
    public static function getProName($id)
    {
        return self::where(['id'=>$id])->field('product_name')->find();
    }

    //获取商品的名称和分类
    public static function getPro($page,$size,$condition)
    {
        $data['res'] = db('cardinfo ca')
            ->join('product p','ca.product_id=p.id','left')
            ->join('categorys cate','p.categorys_id=cate.id','left')
            ->where($condition)
            ->page($page,$size)
            ->field('ca.id,ca.pro_name,p.id as product_id,p.product_name,cate.categorys_name')
            ->select();
        if($data['res']){
            $data['count'] = db('cardinfo ca')
                ->join('product p','ca.product_id=p.id','left')
                ->join('categorys cate','p.categorys_id=cate.id','left')
                ->where($condition)
                ->count();
        }
        return $data;
    }

    public static function getField($pro_id,$field = '')
    {
        return self::where(['id'=>$pro_id])->value($field);
    }

    //平台 获取商品列表
    public static function Pgetlist($condition,$page,$size)
    {
        $res['res'] = db('product p')
            ->join('user u','p.uid=u.id','left')
            ->join('categorys c','p.categorys_id=c.id','left')
            ->field('u.id,u.username,p.id as pro_id,p.product_name,c.categorys_name,FROM_UNIXTIME(p.create_time) as time,p.status')
            ->where($condition)
            ->page($page,$size)
            ->select();
        if($res['res']){
            $res['count'] = db('product p')
                ->join('user u','p.uid=u.id','left')
                ->join('categorys c','p.categorys_id=c.id','left')
                ->where($condition)
                ->count();
        }
        return $res;
    }

    //根据商品的授权码获取可以被代理的商品
    public static function getProByPro($code,$page,$size)
    {
        $data['info'] = self::where(['agent_code'=>$code])->page($page,$size)->field('id,uid,product_name,num,sell_price,create_time')->select();
        if($data['info']){
            $data['count'] = self::where(['agent_code'=>$code])->count();
        }
        return $data;
    }

    public static function infoByAuthcode($code)
    {
        return db('product p')
            ->join('user u','p.uid=u.id','left')
            ->where(['p.agent_code'=>$code])
            ->field('u.id as uid,u.stop_agent_uid,p.num')
            ->find();
    }

  	public static function getInfoByCateId($uid,$cate_id)
    {
    	return self::where(['uid'=>$uid,'categorys_id'=>$cate_id])->select();
    }
  	public static function getInfoByUCPid($cid,$pid)
    {
        return self::where(['categorys_id'=>$cid,'id'=>$pid])->field('is_getCard_pass,visit_pass,stock_status,num')->find();
    }
  	public static function UpNUM($pro_id)
    {
        $num = self::where(['id'=>$pro_id])->value('num');
        return self::where(['id'=>$pro_id])->update(['num'=>$num-1]);
    }
  
  	public static function UpNUMAll($pro_id,$num)
    {
    	$pro_num = self::where(['id'=>$pro_id])->value('num');
      	return self::where(['id'=>$pro_id])->update(['num'=>$pro_num-$num]);
    }
  
  	public static function upNumJia($pro_id,$num)
    {
    	$pro_num = self::where(['id'=>$pro_id])->value('num');
      	return self::where(['id'=>$pro_id])->update(['num'=>$pro_num+$num]);
    }
}