<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/7/5
 * Time: 17:47
 */

namespace app\api\model;


use think\Model;

class Articlecate extends Model
{
    public static function addCate($data)
    {
        return self::insert($data,true);
    }

    public static function CateList()
    {
        return self::where(['status'=>'1'])->select();
    }
    public static function upCate($id,$data)
    {
        return self::where(['id'=>$id])->update($data);
    }
    public static function delCate($id)
    {
        return self::where(['id'=>$id])->delete();
    }
}