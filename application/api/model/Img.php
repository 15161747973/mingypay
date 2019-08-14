<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/6/29
 * Time: 10:44
 */

namespace app\api\model;


use think\Model;

class Img extends Model
{
    public static function addImg($data)
    {
        return self::insert($data,true);
    }

    public static function getField($uid)
    {
        return self::where(['uid'=>$uid,'type'=>'1','status'=>'1'])->value('image');
    }
    public static function delImg($uid)
    {
        return self::where(['uid'=>$uid,'type'=>'1'])->delete();
    }
    public static function getInfo($uid,$type)
    {
        return self::where(['uid'=>$uid,'type'=>$type])->find();
    }
}