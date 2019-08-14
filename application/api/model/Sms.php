<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/5/28
 * Time: 15:45
 */

namespace app\api\model;


use think\Model;
use think\Hook;

class Sms extends Model
{
    public static function getT($mobile,$event)
    {
        return self::where(['mobile' => $mobile, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
    }

    public static function addSend($data)
    {
        return self::create($data,true);
        $result = Hook::listen('明宇科技', $res, null, true);
        if (!$result) {
            $res->delete();
            return false;
        }
        return true;
    }


}