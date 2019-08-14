<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/5/23
 * Time: 12:01
 */

namespace app\api\model;


use app\api\model\Product;
use think\Model;

class Categorys extends Model
{
    public static function addC($data)
    {
        return self::insert($data,true);
    }

    public static function AllCate($page, $size,$uid)
    {
        $res['data'] = self::where(['status'=>1,'uid'=>$uid])->page($page, $size)->order('score DESC')->select();
        $res['count'] = self::where(['status'=>1,'uid'=>$uid])->count();
        return $res;
    }

    public static function getInfoId($uid,$id)
    {
        return self::where(['uid'=>$uid,'id'=>$id])->field('categorys_name,score')->find();
    }
    public static function UpCategory($data,$id)
    {
        return self::where('id','=',$id)->update($data);
    }
    public static function delCategory($uid,$id)
    {
        return self::where(['id'=>$id,'uid'=>$uid])->delete();
    }

    public static function getCateName($care_id)
    {
        return self::where(['id'=>$care_id])->field('categorys_name')->find();
    }

    public static function getInfoByCateId($uid,$id,$page,$size)
    {
        $res['data'] = db('categorys c')
            ->join('product p','c.id=p.categorys_id','left')
            ->join('cardinfo ca','p.id=ca.product_id','left')
            ->where(['p.uid'=>$uid,'c.id'=>$id])
            ->page($page,$size)
            ->field('p.id,c.categorys_name,p.product_name,p.product_score,p.cost_price,p.num,p.sale_num,ca.status')
            ->select();
        if($res['data']){
            $res['count'] = db('categorys c')
                ->join('product p','c.id=p.categorys_id','left')
                ->join('cardinfo ca','p.id=ca.product_id','left')
                ->where(['p.uid'=>$uid,'c.id'=>$id])
                ->page($page,$size)
                ->field('p.id,c.categorys_name,p.product_name,p.product_score,p.cost_price,p.num,p.sale_num,ca.status')
                ->count();
        }
        return $res;
    }

    public static function cates($uid)
    {
        return self::where(['uid'=>$uid,'status'=>'1'])->select();
    }

    public static function getGG($uid)
    {
        $res = self::where(['uid'=>$uid])->field('id,categorys_name')->select();
        if($res){
            foreach ($res as $key=>$val) {
                $res[$key]['product'] = Product::getProByCateId($uid,$val['id']);
            }
        }
        return $res;
    }

    public static function havePro($uid,$cate_id)
    {
      $data = db('categorys c')
            ->join('product p','c.id=p.categorys_id','left')
            ->where(['c.uid'=>$uid,'c.id'=>$cate_id,'p.status'=>'1'])
            ->field('c.id,c.categorys_name,p.id as pro_id,p.product_name,p.sell_price,p.stock_status,p.num,p.status')
            ->select();
        foreach($data as $k=>$v){
            if($v['pro_id'] == null){
                $data[$k]['pro_id'] = '';
                $data[$k]['product_name'] = '';
                $data[$k]['sell_price'] = 0;
                $data[$k]['num'] = 0;
            }
        }
        return $data;
    }
  	public static function getCateInfoByName($name,$uid)
    {
    	return self::where(['categorys_name'=>$name,'uid'=>$uid])->find();
    }
}