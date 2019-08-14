<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/5/24
 * Time: 11:32
 */

namespace app\api\model;


use think\Model;
use app\api\model\Product;
use traits\model\SoftDelete;
use think\db;

class Cardinfo extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

    public static function addCard($data)
    {
        return self::insert($data,true);
    }

    public static function cardPros($id)
    {
        $data = db('cardinfo c')
            ->join('product p','c.product_id=p.id','left')
            ->join('categorys ca','p.categorys_id=ca.id','left')
            ->where(['c.product_id'=>$id,'c.status'=>'1'])
            ->field('p.id as pro_id,p.product_name,c.id,c.card_num,c.card_pwd,c.create_time,c.status,ca.categorys_name')
            ->select();
        return $data;
    }

    public static function listCardInfos($page,$size,$condition)
    {
        $res['res'] = db('cardinfo c')
            ->join('product p','c.product_id=p.id','left')
            ->where($condition)
            ->order('c.create_time DESC')
            ->page($page,$size)
            ->field('c.*,p.product_name,FROM_UNIXTIME(c.create_time) as create_time')
            ->select();
        if($res['res']){
            $res['count'] =db('cardinfo c')
                ->join('product p','c.product_id=p.id','left')
                ->where($condition)
                ->count();
        }
        return $res;
    }

    //获取卡详情
    public static function getInfo($id)
    {
        return self::where(['id'=>$id])->field('card_num,card_pwd')->find();
    }

    //执行修改卡信息
    public static function UpdateC($id,$data)
    {
        return self::where(['id'=>$id])->update($data);
    }

    //商品卡软删除
    public static function delC($id)
    {
        $a = db('cardinfo')->where(['id'=>$id])->update(['status'=>'0']);   //将状态更新为删除
        $res = self::destroy($id);
        return $res;
    }

    //批量删除
    public static function delA($arr)
    {
        for ($i = 0; $i < count($arr); $i++) {
            $cardIn = self::where(['id'=>$arr[$i]])->find();
            if($cardIn['status'] == '1'){
                Product::UpNUM($cardIn['product_id']);
            }
            db('cardinfo')->where(['id'=>$arr[$i]])->update(['status'=>'0']);   //将状态更新为删除
            $res = self::destroy($arr[$i]);
        }
        return $res;
    }

    //获取软删除的数据
    public static function getSoftList($uid,$page,$size)
    {
        $data['res'] = db('cardinfo c')
            ->join('product p','c.product_id=p.id','left')
            ->where(['c.uid'=>$uid,'c.delete_time'=>['<>','NULL']])
            ->page($page,$size)
          	->order('c.delete_time DESC')
            ->field('c.*,p.product_name,FROM_UNIXTIME(c.delete_time) as create_time')
            ->select();
        if($data['res']){
            $data['count'] = db('cardinfo c')
                ->join('product p','c.product_id=p.id','left')
                ->where(['c.uid'=>$uid,'c.delete_time'=>['<>','NULL']])
                ->count();
        }
       return $data;
    }

    //还原数据后修改状态
    public static function updateS($ids)
    {
        return self::where(['id'=>['in',$ids]])->update(['status'=>1]);
    }

    //删除指定状态的卡密
    public static function delCardStu($uid,$product_id,$condition)
    {
        $arr = self::where(['product_id'=>$product_id,'uid'=>$uid])->where($condition)->field('id')->select();
        $i = 0;
        foreach ($arr as $key=>$val){
            db('cardinfo')->where(['id'=>$val['id']])->update(['status'=>'0']);   //将状态更新为删除
            $res = self::destroy($val['id']);
            $i++;
        }
        return $i;
    }

    //
    public static function getMess($id)
    {
        return self::where(['product_id'=>$id])->count();
    }

    public static function getFieldById($id,$field)
    {
        return self::where('product_id','=',$id)->field($field)->find();
    }

    public static function getRandId($pro_id,$num,$sequence)
    {
        $id_arr = self::where(['product_id'=>$pro_id,'status'=>'1'])->field('id')->select();
        $array = [];
        $cards = '';
      	$sss = [];
        if($id_arr){
            foreach ($id_arr as $k=>$v){
                $array[] = $v['id'];
            }
        }
        if($sequence == '0'){
            if($num == '1'){
                $cards = array_shift($array);
            }else{
                for($i=0;$i<$num;$i++){
                    $cards .= (array_shift($array).',');
                }
            }
        }elseif ($sequence == '1'){
          if($num == '1'){
          	$key =  array_rand($array,1);
            $cards = $array[$key];
          }else{
           	$key =  array_rand($array,$num);
            for($i=0;$i<count($key);$i++){
                $cards .= $array[$key[$i]].',' ;
            }
          }
        }
        return $cards;

    }
    public static function getList($uid)
    {
        return self::where(['uid'=>$uid])->select();
    }

    public static function upDatas($ids,$data)
    {
        return self::where(['id'=>['in',$ids]])->update($data);
    }
    public static function getField($arrId)
    {
        $arr = [];
        for($i=0;$i<count($arrId);$i++){
          $cardIn = self::where(['id'=>$arrId[$i]])->find();
          $arr[$i]['card_num'] = $cardIn['card_num'];
          $arr[$i]['card_pwd'] = $cardIn['card_pwd'];
        }
        return $arr;
    }

    public static function upStatus($id_arr)
    {
        return self::where(['id'=>['in',$id_arr]])->update(['status'=>'2']);
    }
  	
  	/** 彻底删除卡密 **/
  	public static function deleteCard($pro_id)
    {
      return db('cardinfo') ->where(['product_id'=>$pro_id,'delete_time'=>['<>','NULL']])->delete();
    }
  
  	/** 彻底删除回收站里的数据 **/
    public static function realDelData($card_id)
    {
        return self::destroy($card_id,true);
    }
  
  	/** 批量彻底删除回收站里的数据 **/
    public static function realAllDelData($id_arr)
    {
        $j = 0;
        for($i=0;$i<count($id_arr);$i++){
           $res = self::destroy($id_arr[$i],true);
           $j++;
        }
        return $j;
    }
  	public static function getField1($id,$field)
    {
    	return db('cardinfo')->where(['id'=>$id])->value($field);
    }
}