<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/7/22
 * Time: 15:17
 */

namespace app\api\model;


use think\Model;

class Message extends Model
{
    public static function addData($data)
    {
        return self::insert($data,true);
    }

    public static function getMessList($order_num)
    {


        $mess = self::where(['order_num'=>$order_num])->select();
        $images = db('img')->where(['order_num'=>$order_num,'type'=>'2'])->select();
        $data = array_merge($mess,$images);
        $data = (self::order_date_array($data, 'asc', 'create_time'));
        return $data;
    }

    public static function order_date_array($array, $order, $key)
    {
        if (!$array){
            return [];
        }else{
            $_array = $array;
        }
        if (!$order){
            $_order = 'desc';
        }else{
            $_order = $order;
        }

        if (!$key){             // 二维数据中的时间字段
            $_key = 'create_time';
        }else{
            $_key = $key;
        }
        $new_array = [];
        $array_1 = [];
        $array_2 = [];

        for ($t=0; $t<count($_array); $t++){
            $array_1[] = $_array[$t][$_key];
            $array_2[] = $_array[$t][$_key];
        }
        // 排列方式
        if ($_order === 'desc'){ // 降序
            rsort($array_2);
        }else{                   // 升序
            sort($array_2);
        }
        // 重新排序原始数组
        for ($r=0; $r<count($array_2); $r++){
            $index = array_search($array_2[$r], $array_1); // 元素索引
            $new_array[] = $_array[$index];
        }
        return $new_array;
    }
}