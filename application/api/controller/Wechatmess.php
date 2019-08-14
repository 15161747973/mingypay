<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/7/31
 * Time: 14:30
 */

namespace app\api\controller;


use app\common\controller\Api;

class WechatMess extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }
  
  	public function http_request($url,$data=null){
		//初始化CURL
        $curl = curl_init();
		//设置CURL参数
        curl_setopt($curl,CURLOPT_URL,$url);//访问地址
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);//不检测证书 
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);//不检测证书 
        if(!empty($data)){
             curl_setopt($curl,CURLOPT_POST,1);//发送方式post
             curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//发送数据
        }
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);//不直接输出到浏览器 存入变量中
        $output = curl_exec($curl);//执行curl
        curl_close($curl);
        return $output;

    }
	
  	/** 方式模板消息 **/
	public function to_User_sendMes($openid,$title,$activity,$require,$time,$remark,$url,$access_token){
		$content['first']=array('value'=>$title,"color"=>'#000');
		$content['keyword1']=array('value'=>$time,"color"=>'#000');
		$content['keyword2']=array('value'=>$activity,"color"=>'#000');
		$content['keyword3']=array('value'=>$require,"color"=>'#000');
		$content['remark']=array('value'=>$remark,"color"=>'#000');

		$mesData['touser']=$openid;		//接收消息的微信openID
		$mesData['url']=$url;
		$mesData['template_id']="LFM87MdQMPWhcRzZwfJM4tPNTWPAJPjmWGd4yhvbxQg";
		$mesData['data']=$content;
		$url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$this->getAccess_token;
		$result=$this->http_request($url,json_encode($mesData));
    }
	
  	/** 获取access_token **/
	public function getAccess_token(){
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx85e7145e26f6c261&secret=32f87626d3a76cc9b46f4daa7b7a4c83";
        $data=$this->http_request($url);
        $data=json_decode($data,true);
		return $data['access_token'];
    }
}