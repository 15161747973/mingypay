<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/6/25
 * Time: 10:42
 */

namespace app\api\model;


use think\Model;

class Emailcode extends Model
{
    public static function getInsertId($data)
    {
        return self::insertGetId($data);
    }
    //根据id获取信息
    public static function getInfoById($id)
    {
        return self::where(['id'=>$id])->find();
    }

    //超过两分钟删除
    public static function delEmailCode($id)
    {
        return self::where(['id'=>$id])->delete();
    }

    //获取信息
    public static function getInfo($email,$code)
    {
        return self::where(['email'=>$email,'code'=>$code])->find();
    }
}