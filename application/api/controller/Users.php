<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/22
 * Time: 8:59
 */

namespace app\api\controller;

use app\api\controller\Sms as SmsContro;
use app\api\validate\UsersValidate;
use app\common\controller\Api;
use app\api\model\Users as UserModel;
use app\api\model\Order as OrderModel;
use app\common\library\Sms;
use app\common\library\Auth;
use think\Validate;
use think\db;
use fast\Random;
use think\Hook;
use think\addons;
use Dm\Request\V20151123 as Dm;
use app\api\model\Emailcode;

class Users Extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = '*';
    protected $_user = null;
    public function _initialize()
    {
        parent::_initialize();
    }


    /**
     * 注册会员
     *
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $email    邮箱
     * @param string $mobile   手机号
     * @param string $qq       QQ号
     */
    public function register()
    {
        header("Access-Control-Allow-Origin: *");
        $res['refer_id'] = isset($_POST['refer_id']) ? $_POST['refer_id'] : '';
        $res['username']   = $this->request->request('username');
        $res['password']   = $this->request->request('password');
        $res['repeatpass'] = $this->request->request('repeatpass');
        $res['email']      = $this->request->request('email');
        $res['mobile']     = $this->request->request('mobile');
        $res['code']       = $this->request->request('code');
        $res['qq']         = $this->request->request('qq');
      	if($res['email']){
          	$res['is_verify_email'] = '1';
        }
        $res['auth_code']  = 'Sq'.(substr(time(),7,4)).rand(1000,9999);
        if (preg_match("/^\d*$/",$res['username'])) {
            $this->error('用户名不能是纯数字');
        }
        $string = strpos($res['username'], '@');
        if($string){
            $this->error('用户名中不能存在@字符');
        }
        $usersValidate = new UsersValidate();
        if(!$usersValidate->check($res)){
            $this->error($usersValidate->getError());
        }
        $smsCheck = new SmsContro();
        $resq = $smsCheck->Sms_check($res['mobile'], $res['code'], 'register');
        if ($resq) {
            $ret = $this->auth->register($res['username'], $res['password'], $res['auth_code'],$res['refer_id'], $res['email'], $res['mobile'], $res['qq'], $res['is_verify_email'], ['avatar'=>'/uploads/20190521/7.jpg']);
            if ($ret) {
                $data = ['userinfo' => $this->auth->getUserinfo()];
                $this->success(__('注册成功'), $data,'200');
            } else {
                $this->error($this->auth->getError());
            }
        }

    }

    /**
     * 会员登录(手机号登录)
     * @param string $password 密码
     * @param string $mobile   手机号
     */
    public function login()
    {
        header("Access-Control-Allow-Origin: *");
        $account = $this->request->request('account');
        $password = $this->request->request('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password);
        $ip = request()->ip();
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            db('user')->where(['id'=>$data['userinfo']['id']])->update(['token'=>$data['userinfo']['token'],'loginip'=>$ip]);   //登录后修改token
          	setcookie("token", $data['userinfo']['token'], time()+10,'http://test.mingypay.com'); /* 有效期0.5 小时 */
            $this->success(__('登录成功'), $data,'200');
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**商户的基本信息**/
    public function shopInfo()
    {
        $user = $this->auth->getUser(); //获取用户信息
        if($user){
            $this->success('获取成功',$user,'200');
        }else{
            $this->error('获取失败','','-1');
        }
    }

    /**修改密码**/
    public function changePwd()
    {
        $oldpass = $this->request->request('oldpass');  //原密码
        $newpass = $this->request->request('newpass');  //新密码
        $repass = $this->request->request('repass');    //重复新密码
        //判断原密码是否正确
        $user = $this->auth->getUser(); //获取用户信息
        if($user['password'] != md5(md5($oldpass).$user['salt'])){
            $this->error('原密码错误，请重新输入');
        }
        if($newpass != $repass){
            $this->error('密码确认错误，请重试');
        }
        $salt = Random::alnum();
        $newpassword = md5(md5($newpass).$salt);
        $data = db('user')->where('id','=',$user['id'])->update(['password'=>$newpassword,'salt'=>$salt]);
        if($data){
            $this->success('密码修改成功','','200');
        }else{
            $this->error('密码修改失败','','-1');
        }
    }

    /**
     * 通过邮箱获取手机号
     */
    public function getPhoByEmail()
    {
        $email = $this->request->request('email');
        $mobile = UserModel::getPhoByE($email,'mobile');
        if($mobile){
            $this->success('操作成功',$mobile,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * 重置密码
     */
    public function resetpwd()
    {
        $email = $this->request->request('email');
        $mobile = UserModel::getPhoByE($email,'mobile');
        $code = $this->request->request('code');
        $newPwd = $this->request->request('newPwd');
        $reNewPwd = $this->request->request('reNewPwd');
        $data = UserModel::getInfoByE($email);
        if($data){
            $smsCheck = new SmsContro();
            $resq = $smsCheck->Sms_check($mobile, $code, 'resetPwd');
            if($resq){
                if($newPwd == $reNewPwd){
                    $salt = Random::alnum();
                    $newpassword = md5(md5($newPwd).$salt);
                    $data = db('user')->where('id','=',$data['id'])->update(['password'=>$newpassword,'salt'=>$salt]);
                    if($data){
                        $this->success('密码重置成功','','200');
                    }
                }else{
                    $this->error('重复密码错误','','-1');
                }
            }else{
                $this->error('验证码不正确','','-1');
            }
        }else{
            $this->error('账号不存在','','-1');
        }
    }



    /*生成推广二维码*/
    public function createErWeiMa()
    {
        require_once EXTEND_PATH.'phpewm/phpqrcode.php';
//        $id = 'http://47.104.81.60/index/regist.html';
        $value = $this->request->request('url');              //二维码内容
        $errorCorrectionLevel = 'L';   //容错级别
        $matrixPointSize = 5;        //生成图片大小
        $path=date('Ymd',time());
        //生成二维码图片
        $filename = 'uploads/'.$path.'/'.md5(uniqid()).'.png';

        if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/uploads/'.$path)){
            mkdir($_SERVER['DOCUMENT_ROOT'].'/uploads/'.$path,0777,true);
        }
        \QRcode::png($value,$_SERVER['DOCUMENT_ROOT'].'/'.$filename , $errorCorrectionLevel, $matrixPointSize, 2);
        $QR = $_SERVER['DOCUMENT_ROOT'].'/'.$filename;          //已经生成的原始二维码图片文件
        $QR = imagecreatefromstring(file_get_contents($QR));
        //输出图片
        /*imagepng($QR, 'qrcode.png');
        imagedestroy($QR);*/
        if($QR){
            $this->success('success',config('site.url').$filename,'200');
        }else{
            $this->error('error','','-1');
        }
    }

    //推广列表
    public function popList()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量
        if(!$user){
            $this->error('请先登录...');
        }
        $data = UserModel::PopList($user['id'],$page,$size);
        if($data){
            $this->success('操作成功',$data,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    //邮箱发送验证码
    public function sendEmail($status='',$email,$substr,$order_num='')
    {
        include_once EXTEND_PATH.'aliEmail/aliyun-php-sdk-core/Config.php';
        //$status = isset($_POST['status']) ? $_POST['status'] : '';
        //$email = $this->request->request('email');
        //$substr = $this->request->request('substr');
        //$order_num = isset($_POST['order_num']) ? $_POST['order_num'] : '';
        if(empty($status)){
            $str = rand(100000,999999);
            $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", "LTAIZMwNbeFIGys2", "BaNiMCcMgR48oaHHynMGTVrozXbKBs");
            $client = new \DefaultAcsClient($iClientProfile);
            $request = new Dm\SingleSendMailRequest();
            $request->setAccountName("mingyu@test.mingypay.com");
            $request->setFromAlias("明宇科技");
            $request->setAddressType(1);
            $request->setTagName("控制台创建的标签");
            $request->setReplyToAddress("true");
            $request->setToAddress($email);
            $request->setSubject($substr);   //邮件主题
            $request->setHtmlBody('您的 '. $substr .' 的验证码：'.$str);    //邮件正文内容
            try {
                $response = $client->getAcsResponse($request);
                if($response->RequestId){
                    $data['email'] = $email;
                    $data['substr'] = $substr;
                    $data['code'] = $str;
                    $data['create_time'] = time();
                    $id = Emailcode::getInsertId($data);
                    sleep(60);
                    Emailcode::delEmailCode($id);
                    $this->success('发送成功',$id,'200');
                }
            }
            catch (\ClientException  $e) {
                print_r($e->getErrorCode());
                print_r($e->getErrorMessage());
            }
            catch (\ServerException  $e) {
                print_r($e->getErrorCode());
                print_r($e->getErrorMessage());
            }
        }else{
            $orderInfo = OrderModel::getOrderByONum1($order_num);
            $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", "LTAIZMwNbeFIGys2", "BaNiMCcMgR48oaHHynMGTVrozXbKBs");
            $client = new \DefaultAcsClient($iClientProfile);
            $request = new Dm\SingleSendMailRequest();
            $request->setAccountName("mingyu@test.mingypay.com");
            $request->setFromAlias("明宇科技");
            $request->setAddressType(1);
            $request->setTagName("控制台创建的标签");
            $request->setReplyToAddress("true");
            $request->setToAddress($email);
            $request->setSubject($substr);   //邮件主题
            $request->setHtmlBody('尊敬的用户，您购买的商品订单号：【'.$order_num.'】<br>请妥善保管您的订单号，如有问题请联系卖家！<br> '.'商品名称：'.$orderInfo['product_name'].';<br>'.'卡号：'.$orderInfo['card_num'].';  卡密：'.$orderInfo['card_pwd']);    //邮件正文内容
            try {
                $response = $client->getAcsResponse($request);
                if($response->RequestId){
                    $data['email'] = $email;
                    $data['substr'] = $substr;
                    $data['code'] = $order_num;
                    $data['create_time'] = time();
                    $id = Emailcode::getInsertId($data);
                    sleep(60);
                    Emailcode::delEmailCode($id);
                    return 1;
                }
            }
            catch (\ClientException  $e) {
                print_r($e->getErrorCode());
                print_r($e->getErrorMessage());
            }
            catch (\ServerException  $e) {
                print_r($e->getErrorCode());
                print_r($e->getErrorMessage());
            }
        }
    }

    /**
     * 邮箱验证验证码
     */
    public function emailCodeCheck()
    {
        $time = time();
        $email = $this->request->request('email');  //邮箱
        $code = $this->request->request('code');    //
        $data = Emailcode::getInfo($email,$code);
        if($time>$data['create_time']+60){
            $this->error('验证码已失效','','-2');
        }
        if($data){
            $this->success('验证成功','','200');
        }else{
            $this->error('验证码错误','','-1');
        }
    }


    //安全码设置
    public function setSafeCode()
    {
        $user = $this->auth->getUser(); //获取用户信息
        if($user['is_open_safe'] == '0'){
            $data['safe_code'] = $this->request->request('safe_code');
            $data['is_open_safe'] = '1';
            $re_safe_code = $this->request->request('re_safe_code');
            if($data['safe_code'] != $re_safe_code){
                $this->error('两次安全码不一致','','-1');
            }
            $res = UserModel::UpData($user['id'],$data);
        }else if($user['is_open_safe'] == '1'){
            $da['is_open_safe'] = isset($_POST['is_open_safe']) ? $_POST['is_open_safe'] : '';
            if($da['is_open_safe'] == '0'){
                $res = UserModel::UpData($user['id'],$da);
            }elseif ($da['is_open_safe'] == '1'){
                $old_safeCode = $this->request->request('old_safeCode');
                $da['safe_code'] = $this->request->request('new_safeCode');
                $repeat_safeCode = $this->request->request('repeat_safeCode');
                if($old_safeCode != $user['safe_code']){
                    $this->error('原安全码错误','','-1');
                }
                if($da['safe_code'] != $repeat_safeCode){
                    $this->error('重复新安全码错误','','-1');
                }
                $res = UserModel::UpData($user['id'],$da);
            }
        }
        if($res){
            $this->success('修改成功','1','200');
        }else{
            $this->error('操作失败','0','-1');
        }
    }

    /**
     * 验证安全码
     */
    public function safeCodeCheck()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $safe_code = $this->request->request('safe_code');
        if($safe_code == $user['safe_code'] && $user['is_open_safe']=='1'){
            $this->success('验证成功','','200');
        }else{
            $this->error('安全码错误','','-1');
        }
    }

    /**
     * 修改绑定手机号
     */
    public function upInfo()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $data['mobile'] = $this->request->request('mobile');
      	$mobileInfo = UserModel::getByMobile($data['mobile']);
        if($mobileInfo){
            $this->error('手机已被使用，请更换');
        }
        $res = UserModel::UpData($user['id'],$data);
        if($res){
            $this->success('操作成功','1','200');
        }else{
            $this->error('操作失败','0','-1');
        }
    }

    //邮箱认证设置 修改认证状态
    public function EmailApprove()
    {
        $user = $this->auth->getUser(); //获取用户信息
        if($user['is_verify_email'] == '0'){
            $data['is_verify_email'] = '1';
            $res = UserModel::upApprove($user['id'],$data);
        }elseif($user['is_verify_email'] == '1'){
            $data['email'] = $this->request->request('email');
          	$emailInfo = UserModel::getInfoByE($data['email']);
            if($emailInfo){
                $this->error('邮箱已被使用，请更换');
            }
            $res = UserModel::upApprove($user['id'],$data);
        }
      	if($res){
                $this->success('修改邮箱成功','','200');
            }else{
                $this->error('操作失败','','-1');
            }

    }


    //获取公网IP地址
    public function getPubIp()
    {
      	$externalIp = $this->PubIp();
        if($externalIp){
            $this->success('获取成功',$externalIp,'200');
        }else{
            $this->error('error','','-1');
        }
    }

    //白名单设置
    public function ipSet()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $data['is_check_ip'] = $this->request->request('status');
        $data['ip_list'] = isset($_POST['ip']) ? $_POST['ip'] : '';
        $res = UserModel::upApprove($user['id'],$data);
        if($res){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('操作无效','','-1');
        }
    }

    //白名单列表 在白名单验证成功之后调用
    public function EditIpL()
    {
        $user = $this->auth->getUser(); //获取用户信息
//        $ip = $this->getPubIp();
        $ip = $this->request->request('ip');
        if(!empty($user['ip_list'])){
            $data['ip_list'] = $user['ip_list'].','.$ip;
        }else{
            $data['ip_list'] = $ip;
        }
//        halt($data);
        $res = UserModel::upApprove($user['id'],$data);
        if($res){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }
    /**
     * @return bool
     * 获取ip所在的城市地址
     */
    public function getCity(){
        $ip=$this->PubIp();
        if($ip == ''){
            $url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json";
            $ip=json_decode(file_get_contents($url),true);
            $data = $ip;
        }else{
            $url="http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
            $ip=json_decode(file_get_contents($url));
            if((string)$ip->code=='1'){
                return false;
            }
            $data = $ip->data;
        }
        if($data){
            $this->success('操作成功',$data,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }


    //极验验证
    public function jyyz()
    {
        require_once EXTEND_PATH.'jyyz/lib/class.geetestlib.php';
        require_once EXTEND_PATH.'jyyz/config/config.php';
        $user = $this->auth->getUser(); //获取用户信息
        $GtSdk = new \GeetestLib(CAPTCHA_ID, PRIVATE_KEY);
        session_start();
        $data = array(
            "user_id" => $user['id'], # 网站用户id
            "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address" => "127.0.0.1" # 请在此处传输用户请求验证时所携带的IP
        );
        $status = $GtSdk->pre_process($data, 1);
        $_SESSION['gtserver'] = $status;
        $_SESSION['user_id'] = $data['user_id'];
        $res = $GtSdk->get_response_str();
        return $res;
    }
    //极验验证（二次验证）
    public function again_jyyz()
    {
        require_once EXTEND_PATH.'jyyz/lib/class.geetestlib.php';
        require_once EXTEND_PATH.'jyyz/config/config.php';
        session_start();
        $GtSdk = new \GeetestLib(CAPTCHA_ID, PRIVATE_KEY);
        $data = array(
            "user_id" => $_SESSION['user_id'], # 网站用户id
            "client_type" => "web", #web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            "ip_address" => "127.0.0.1" # 请在此处传输用户请求验证时所携带的IP
        );
        if ($_SESSION['gtserver'] == 1) {   //服务器正常
            $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
            echo 'qqqqqq';
            if ($result) {
                echo '{"status":"success"}';
            } else {
                echo '{"status":"fail"}';
            }
        } else {  //服务器宕机,走failback模式
            if ($GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])) {
                echo '{"status":"success"}';
            } else {
                echo '{"status":"fail"}';
            }
        }
    }

    //生成短链接存库
    function shorturl1()
    {
        $url = $this->request->request('url');

        $url = "http://api.t.sina.com.cn/short_url/shorten.json?source=202088835&url_long=" . $url;
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $contents = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($contents);
        $array = array();
        $array["url_short"] = $json[0]->url_short;
//        $output = json_encode( $array );  //转为json格式
        if($url){
            $this->success('获取成功',$array,'200');
        }else{
            $this->error('失败','','-1');
        }
    }
  
  	/** 推广佣金 **/
    public function commsion()
    {
        $user = $this->auth->getUser(); //获取用户信息
        $data['res']['uid'] = $user['id'];
        //找到该用户的所有下级id
        $id_arr = UserModel::getIdsByPrefreid($user['id']);
        //待结算佣金的金额(本月)
        $No_Price = OrderModel::NOCommsion_money($id_arr,$user['YJ_rate']);
        $data['res']['No_YJ_price'] = $No_Price*$user['YJ_rate'];
        //已结算的佣金
        $Z_Price = OrderModel::Commsion_money($id_arr,$user['YJ_rate']);
        $data['res']['Yes_YJ_price'] = ($Z_Price-$No_Price)*$user['YJ_rate'];
        $data['res']['rate'] = $user['YJ_rate'];
      	$data['count'] = 1; 
        if($data){javascript:;
            $this->success('操作成功',$data,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    } 
    public function PubIp()
    {
      static $ip = NULL;
      if ( $ip !== NULL )
      return $ip;
      if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
          $arr = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
          $pos = array_search( 'unknown', $arr );
          if ( false !== $pos )
          unset( $arr[$pos] );
          $ip = trim( $arr[0] );
      } elseif ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
          $ip = $_SERVER['HTTP_CLIENT_IP'];
      } elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
          $ip = $_SERVER['REMOTE_ADDR'];
      }
      // IP地址合法验证
      $ip = ( false !== ip2long( $ip ) ) ? $ip : '';
      return $ip;
      }
}