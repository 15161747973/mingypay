<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/5/27
 * Time: 16:55
 */

namespace app\api\model;

use app\api\model\Img;
use think\Model;

class Gatherinfo extends Model
{
    //添加信息数据
    public static function addGather($data)
    {
        return self::create($data,true);
    }

    //获取信息
    public static function getInfo($uid)
    {
//       return db('gatherinfo g')
//            ->join('img i','g.uid=i.uid','left')
//            ->where(['i.type'=>'1'])
//            ->find();

        $data = self::where('uid','=',$uid)->find();
//        $data['image'] = Img::getField($data['uid']);
        return $data;
    }

    public static function idcheck($name,$idCard)
    {
        return self::Authentication($name,$idCard);

    }

    public static function upImage($uid,$data)
    {
        return self::where(['uid'=>$uid])->update($data);
    }

    //身份证验证
//    public static function Authentication($name,$idCard){
//        $url='http://idenauthen.market.alicloudapi.com/idenAuthentication';
////        $url='https://dm-51.data.aliyun.com/rest/160601/ocr/ocr_idcard.json';
//        $appCode = 'e07416db4fd4416f93a72c49a462ca05';//就是图片中的appcode
//        //姓名
//        $params['realName'] = $name;
//        //身份证号码
//        $params['cardNo'] = $idCard;
//        //发送远程请求;
//        $result = self::APISTORE($url, $params, $appCode, "POST");
//        //返回结果
//        return $result;
//    }


    /**
     * APISTORE 获取数据
     * @param $url 请求地址
     * @param array $params 请求的数据
     * @param $appCode 您的APPCODE
     * @param $method
     * @return array|mixed
     */
//    public static function APISTORE($url, $params = array(), $appCode, $method = "GET")
//    {
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, $method == "POST" ? $url : $url . '?' . http_build_query($params));
//        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
//            'Authorization:APPCODE ' . $appCode
//        ));
//        //如果是https协议
//        if (stripos($url, "https://") !== FALSE) {
//            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
//            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
//            //CURL_SSLVERSION_TLSv1
//            curl_setopt($curl, CURLOPT_SSLVERSION, 1);
//        }
//        //超时时间
//        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
//        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        //通过POST方式提交
//        if ($method == "POST") {
//            curl_setopt($curl, CURLOPT_POST, true);
//            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
//        }
//        //返回内容
//        $callbcak = curl_exec($curl);
//        //http status
//        $CURLINFO_HTTP_CODE = curl_getinfo($curl, CURLINFO_HTTP_CODE);
//        //关闭,释放资源
//        curl_close($curl);
//        if ($CURLINFO_HTTP_CODE == 200)
//            return json_decode($callbcak, true);
//        else if ($CURLINFO_HTTP_CODE == 403)
//            return array("error_code" => $CURLINFO_HTTP_CODE, "reason" => "剩余次数不足");
//        else if ($CURLINFO_HTTP_CODE == 400)
//            return array("error_code" => $CURLINFO_HTTP_CODE, "reason" => "APPCODE错误");
//        else
//            return array("error_code" => $CURLINFO_HTTP_CODE, "reason" => "APPCODE错误");
//    }



    public static function checkIdCard($idNo,$name)
    {
        $host = "https://checkid.market.alicloudapi.com";
        $path = "/IDCard";
        $method = "GET";
        $appcode = "e07416db4fd4416f93a72c49a462ca05";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "idCard=$idNo&name=$name";
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        //curl_setopt($curl, CURLOPT_HEADER, true); 如不输出json, 请打开这行代码，打印调试头部状态码。
        //状态码: 200 正常；400 URL无效；401 appCode错误； 403 次数用完； 500 API网管错误
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $out_put = curl_exec($curl);
        echo $out_put;
        if($out_put === true){
            return '200';
        }else{
            return '-1';
        }
    }


    //银行卡实名认证
//    public static function YinCardCheck($cardNo,$realName)
//    {
//        $host = "https://aliyuncardby2.haoservice.com";
//        $path = "/cardquery/VerifyIdcard";
//        $method = "GET";
//        $appcode = "e07416db4fd4416f93a72c49a462ca05";
//        $headers = array();
//        array_push($headers, "Authorization:APPCODE " . $appcode);
//        $querys = "cardNo=$cardNo&realName=$realName";
//        $bodys = "";
//        $url = $host . $path . "?" . $querys;
//
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
//        curl_setopt($curl, CURLOPT_URL, $url);
//        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($curl, CURLOPT_FAILONERROR, false);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_HEADER, true);
//        if (1 == strpos("$".$host, "https://"))
//        {
//            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
//        }
//        $data = curl_exec($curl);
//        return $data;
//    }


    //银行卡三元素验证，卡号，身份证号，姓名
    public static function YinCard($cardNo,$idNo,$name)
    {
        $host = "https://yunyidata3.market.alicloudapi.com";
        $path = "/bankAuthenticate3";
        $method = "POST";
        $appcode = "e07416db4fd4416f93a72c49a462ca05";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");
        $querys = "";
        $bodys = "cardNo=$cardNo&idNo=$idNo&name=$name";
        $url = $host . $path;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
//        var_dump(curl_exec($curl));
        $data = curl_exec($curl);
        curl_close($curl);
//        return $data;
        if($data){
            return '200';
        }else{
            return '-1';
        }
    }

    // 删除收款人信息
    public static function delPayee($uid)
    {
        return self::where(['uid'=>$uid])->delete();
    }


    public static function getInfoById($id)
    {
        return db('gatherinfo g')
            ->join('img i','g.uid=i.uid','left')
            ->where(['i.type'=>'1'])
            ->field('g.*,i.image')
            ->find();
    }

}