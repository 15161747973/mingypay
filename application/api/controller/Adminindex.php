<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/7/1
 * Time: 10:01
 */

namespace app\api\controller;


use app\common\controller\Api;
use app\api\model\Users as UsersModel;
use app\api\model\Order as OrderModel;
use app\api\model\Product;
use app\api\model\Loginlog;
use app\api\model\Withdraw;
use app\api\model\Paytype;

class Adminindex extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 平台主页数据信息
     */
    public function indexMessage()
    {
        $data['today_register'] = UsersModel::todayRegister('today');           //今日注册
        $data['yesterday_register'] = UsersModel::todayRegister('yesterday');   //昨日注册
        $data['noChech'] = UsersModel::noCheck();                               //冻结用户
        $data['freeze'] = UsersModel::freezeUser();                             //已冻结
        $data['todayOrder'] = OrderModel::getToday('','today');              	//今日总订单
        $data['todayNoPayOrder'] = OrderModel::getToday('1','today');        	//今日未付款订单
        $data['todayPayOrder'] = OrderModel::getToday('2','today');          	//今日成功订单
        $data['yesterdayPayOrder'] = OrderModel::getToday('2','yesterday');  	//昨日成功订单
        $data['todayPayMoney'] = OrderModel::geAllMoney('today');               //今日付款总额
        $data['yesterdayPayMoney'] = OrderModel::geAllMoney('yesterday');       //昨日付款总额
        $data['todayUserMoney'] = OrderModel::geUserMoney('today');             //今日用户总收入
        $data['yesterdayUserMoney'] = OrderModel::geUserMoney('yesterday');     //昨日用户总收入
        $data['todaywithdraw'] = Withdraw::withdrawMon('1','today');            //今日手动提现总额
        $data['todayAutowithdraw'] = Withdraw::withdrawMon('2','today');        //今日自动提现总额
        $data['yesterdaywithdraw'] = Withdraw::withdrawMon('','yesterday');     //今日自动提现总额
        if($data){
            $this->success('success',$data,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**
     * 获取所以的商户信息
     */
    public function usersList()
    {
        $condition = [];
        $id = isset($_POST['id']) ? $_POST['id'] : '';              		//商户id
        $username = isset($_POST['username']) ? $_POST['username'] : '';  	//商户名称
        $is_freeze = isset($_POST['is_freeze']) ? $_POST['is_freeze'] : '0';
        $is_check = isset($_POST['is_check']) ? $_POST['is_check'] : '';
            $condition['id'] = $id;
        }
        if(!empty($username)){
            $condition['username'] = ['like','%'.$username.'%'];
        }
        if(!empty($is_freeze)){
            $condition['is_freeze'] = $is_freeze;
        }
        if(!empty($is_check)){
            $condition['is_check'] = $is_check;
        }else{
            $condition['is_check'] = ['in',['0','1','2']];
        }
        $page = input('post.page', 1); //页数
        $size = input('post.limit', 5); //每页显示数量
        $res = UsersModel::getUsersList($page,$size,$condition);

        if($res){
            $this->success('获取成功',$res,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**
     * 平台商户审核
     */
    public function checkUsers()
    {
        $id = $this->request->request('id');
        $is_check = $this->request->request('is_check');
        $res = UsersModel::UpData($id,['is_check'=>$is_check]);
        if($res){
            $this->success('操作成功',$res,'200');
        }else{
            $this->error('操作失败','','-1');
        }
    }

    /**
     * 平台后台的收款信息
     */
    public function payeeTypeJoin()
    {
        $condition = [];
        $id = isset($_POST['id']) ? $_POST['id'] : '';                      //商户id
        $username = isset($_POST['username']) ? $_POST['username'] : '';    //商户名称
        $page = input('post.page', 1);                         //页数
        $size = input('post.limit', 5);                        //每页显示数量
        if(!empty($id)){
            $condition['id'] = $id;
        }
        if(!empty($username)){
            $condition['username'] = ['like','%'.$username.'%'];;
        }
        $res = UsersModel::getPayeeInfo($condition,$page,$size);
        if($res){
            $this->success('success',$res,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**
     * 平台的登录日志
     */
    public function loginLog()
    {
        $condition = [];
        $username = isset($_POST['username']) ? $_POST['username'] : '';
        $ip = isset($_POST['ip']) ? $_POST['ip'] : '';
        $time = isset($_POST['time']) ? $_POST['time'] : '';
        $page = input('post.page', 1);      //页数
        $size = input('post.limit', 5);     //每页显示数量
        if(!empty($username)){
            $condition['u.username'] = ['like','%'.$username.'%'];
        }
        if(!empty($ip)){
            $condition['l.ip'] = $ip;
        }
        if(!empty($time)){
            $condition['time'] = ['between',[$time,$time+86400]];
        }
        $loginData = Loginlog::getList($condition,$page,$size);
        if($loginData){
            $this->success('success',$loginData,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**
     * 平台 商品列表
     */
    public function productList()
    {
        $condition = [];
        $userId = isset($_POST['id']) ? $_POST['id'] : '';
        $product_name = isset($_POST['product_name']) ? $_POST['product_name'] : '';  //商品名称
        $cate_name = isset($_POST['categorys_name']) ? $_POST['categorys_name'] : '';
        $beg_time = isset($_POST['beg_time']) ? $_POST['beg_time'] : '';
        $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
        $page = input('post.page', 1);      //页数
        $size = input('post.limit', 5);     //每页显示数量
        if(!empty($userId)){
            $condition['u.id'] = $userId;
        }
        if(!empty($product_name)){
            $condition['p.product_name'] = ['like','%'.$product_name.'%'];
        }
        if(!empty($cate_name)){
            $condition['c.categorys_name'] =['like','%'.$cate_name.'%'];
        }
        if(!empty($beg_time) && empty($end_time)){
            $condition['p.create_time'] = ['>=',$beg_time];
        }else if(empty($beg_time) && !empty($end_time)){
            $condition['p.create_time'] = ['<',$end_time];
        }else if(!empty($beg_time) && !empty($end_time)){
            $condition['p.create_time'] = ['between',[$beg_time,$end_time]];
        }
        $data = Product::Pgetlist($condition,$page,$size);
        if($data){
            $this->success('success',$data,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**
     * 平台 订单管理的列表
     */
    public function orderList()
    {
        $condition = [];
        $userId = isset($_POST['id']) ? $_POST['id'] : '';                            //商户id
        $product_name = isset($_POST['product_name']) ? $_POST['product_name'] : '';  //商品名称
        $order_num = isset($_POST['order_num']) ? $_POST['order_num'] : '';           //订单号
        $username = isset($_POST['username']) ? $_POST['username'] : '';              //商户名称
        $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';                    //商户信息
        $beg_time = isset($_POST['beg_time']) ? $_POST['beg_time'] : '';
        $end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        $page = input('post.page', 1);      //页数
        $size = input('post.limit', 5);     //每页显示数量
        if(!empty($userId)){
            $condition['o.uid'] = $userId;
        }
        if(!empty($product_name)){
            $condition['p.product_name'] = ['like','%'.$product_name.'%'];
        }
        if(!empty($username)){
            $condition['u.username'] =['like','%'.$username.'%'];
        }
        if(!empty($order_num)){
            $condition['o.order_num'] = $order_num;
        }
        if(!empty($mobile)){
            $condition['o.mobile'] = $mobile;
        }
        if(!empty($beg_time) && empty($end_time)){
            $condition['p.create_time'] = ['>=',$beg_time];
        }else if(empty($beg_time) && !empty($end_time)){
            $condition['p.create_time'] = ['<',$end_time];
        }else if(!empty($beg_time) && !empty($end_time)){
            $condition['p.create_time'] = ['between',[$beg_time,$end_time]];
        }
        if(!empty($status)){
            $condition['o.status'] = $status;
        }else{
            $condition['o.status'] = ['in',['1','2','3','4','5']];
        }
        $data = OrderModel::getListInfo($condition,$page,$size);
        if($data){
            $this->success('success',$data,'0');
        }else{
            $this->error('暂无数据','','-1');
        }
    }

    /**
     * 订单管理-》通道分析
     */
    public function channel()
    {
        $data = Paytype::getTypes(['status'=>'1']);
        if($data){
            $this->success('success',$data,'200');
        }else{
            $this->error('暂无数据','','-1');
        }
    }
}