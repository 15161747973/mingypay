<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/22
 * Time: 11:59
 */

namespace app\api\model;


use think\Model;

class Notice extends Model
{
    public static function getList($condition,$page,$size)
    {
        if($condition['status'] == '1'){
            $list['res'] = self::where($condition)->order('id DESC')->limit('10')->field('id,status,title,content,FROM_UNIXTIME(create_time) as time')->select();
            $list['count'] = self::where($condition)->count();
        }elseif ($condition['status'] == '2'){
            $list['res'] = self::where($condition)->order('id DESC')->field('title,content,FROM_UNIXTIME(create_time) as time')->page($page,$size)->select();
            $list['count'] = self::where($condition)->count();
        }

        return $list;
    }

    public static function addNotice($data)
    {
        return self::create($data,true);
    }
}