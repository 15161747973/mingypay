<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/6/28
 * Time: 17:37
 */

namespace app\api\model;


use think\Model;

class Loginlog extends Model
{
    public static function addData($data)
    {
        return self::insert($data,true);
    }
    public static function loginLog($uid,$page,$size)
    {
        $res['list'] = self::where(['uid'=>$uid])->order('id DESC')->limit('6')->page($page,$size)->field('FROM_UNIXTIME(time) as time,ip')->select();
        $res['count'] = self::where(['uid'=>$uid])->count();
        return $res;
    }
    //获取所有的登录记录
    public static function getList($condition,$page,$size)
    {
        $res['data'] = db('loginlog l')
            ->join('user u','l.uid=u.id','left')
            ->field('l.id,u.username,l.ip,l.address,FROM_UNIXTIME(l.time) as time')
            ->where($condition)
            ->page($page,$size)
            ->select();

        if($res['data']){
            $res['count'] = db('loginlog l')
                ->join('user u','l.uid=u.id','left')
                ->where($condition)
                ->count();
        }
        return $res;
    }
}