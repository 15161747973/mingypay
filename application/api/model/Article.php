<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/7/6
 * Time: 9:01
 */

namespace app\api\model;


use think\Model;

class Article extends Model
{
    public static function getList($page,$size)
    {
        $data['res'] = db('article a')
            ->join('articlecate ac','a.cate_id=ac.id','left')
            ->page($page,$size)
            ->field('ac.cate_name,a.id,a.title,a.is_auto_pop,a.issue_time')
            ->select();
        if($data['res']){
            $data['count'] = db('article a')
                ->join('articlecate ac','a.cate_id=ac.id','left')
                ->count();
        }
        return $data;
    }

    public static function addArticle($data)
    {
        return self::insert($data,true);
    }

    public static function getInfo($id)
    {
        return db('article a')
            ->join('articlecate ac','a.cate_id=ac.id','left')
            ->where(['a.id'=>$id])
            ->field('ac.cate_name,a.*')
            ->select();
    }

    public static function upArticle($id,$data)
    {
        return self::where(['id'=>$id])->update($data);
    }

    public static function delArticle($id)
    {
        return self::where(['id'=>$id])->delete();
    }
}