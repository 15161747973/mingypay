<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/6/12
 * Time: 16:09
 */

namespace app\api\model;


use think\Model;
use app\api\model\Users as UsersModel;
use think\Db;

class Agentproduct extends Model
{
    public static function addSub($data)
    {
        return db('agentProduct')->insert($data,true);
    }

    public static function getList($uid,$page,$size,$condition)
    {
        $res['data'] = Db('agentProduct a')
            ->join('categorys c','a.categorys_id=c.id','left')
            ->where(['a.uid'=>$uid])
            ->where($condition)
            ->page($page,$size)
            ->field('a.*,c.categorys_name')
            ->select();

        if($res['data']){
            $res['count'] = Db('agentProduct a')
                ->join('categorys c','a.categorys_id=c.id','left')
                ->where(['a.uid'=>$uid])
                ->where($condition)
                ->count();
        }

        return $res;
    }

    //商品详情
    public static function getInfo($id)
    {
        $res = Db('agentProduct a')
            ->join('categorys c','a.categorys_id=c.id','left')
            ->where(['a.id'=>$id,'a.status'=>'1'])
            ->field('a.*,c.categorys_name')
            ->select();
        return $res;
    }

    //修改
    public static function upAgent($id,$data)
    {
       return db('agentProduct')->where(['id'=>$id])->update($data);
    }
    //删除
    public static function delAgent($id)
    {
        return db('agentProduct')->where(['id'=>$id])->delete();
    }

    //批量删除
    public static function delAll($ids_arr)
    {
        return db('agentProduct')->where(['id'=>['in',$ids_arr]])->delete();
    }

    public static function getSupper($uid,$page,$size)
    {
        $userInfo = db('user')->where(['id'=>$uid])->find();
        $agent_uid_arr = explode(',',$userInfo['stop_agent_uid']);
        $res = db("agentProduct a")
            ->join('user u','a.uid=u.id','left')
            ->where(['a.supple_id'=>$uid])
            ->page($page,$size)
            ->field('a.uid as agent_id,u.shop_name,u.qq,u.status')
            ->select();
        foreach($res as $k=>$v){
            if(in_array($v['agent_id'],$agent_uid_arr)){
                $res[$k]['show_status'] = '0';
            }else{
                $res[$k]['show_status'] = '1';
            }
        }
        $data['arr'] = [];
        foreach ($res as $key=>$value) {

            if (!in_array($value, $data['arr'])){
                $data['arr'][$key] = $value;
            }

        }
        if($data['arr']){
            $data['count'] = count($data['arr']);
        }
        return $data;
    }

    public static function getAhentList($supple_id,$condition,$page,$size)
    {
        /** 已有商户代理的供货产品 **/
//        $res['data'] = db("agentProduct a")
//            ->join('product p','a.supple_pro_id=p.id','left')
//            ->join('categorys c','p.categorys_id=c.id','left')
//            ->where(['a.supple_id'=>$supple_id])
//            ->where($condition)
//            ->field('p.id,p.uid,p.product_name,c.categorys_name,a.product_name as agentProName,a.old_price,a.now_price,a.create_time,a.stock,a.status,a.sale_num')
//            ->select();
//        if($res['data']){
////            foreach ($res as $k=>$v){
////                $res[$k]['uid'] = $uid;
////            }
//            $res['count'] = db("agentProduct a")
//                ->join('product p','a.supple_pro_id=p.id','left')
//                ->join('categorys c','p.categorys_id=c.id','left')
//                ->where(['a.supple_id'=>$supple_id])
//                ->where($condition)
//                ->count();
//        }
//
//        return $res;

        /** 所有的可代理产品 **/
        $res['info'] = db('product')->where(['uid'=>$supple_id,'is_agent'=>'1'])->where($condition)->page($page,$size)->select();
        if($res['info']){
            $res['count'] = db('product')->where(['uid'=>$supple_id,'is_agent'=>'1'])->where($condition)->count();
        }
        return $res;
    }

    public static function upAgentSta($uid,$agent_id,$status)
    {
        return db('agentProduct')->where(['supple_id'=>$uid,'uid'=>$agent_id])->update(['status'=>'2']);
    }

    public static function upStatus($uid,$agent_id)
    {
        return db('agentProduct')->where(['uid'=>$agent_id,'supple_id'=>$uid])->update(['status'=>'2']);
    }
}