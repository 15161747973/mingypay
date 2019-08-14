<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/22
 * Time: 11:09
 */
namespace app\api\model;

use think\Model;
use app\api\model\Product;
use app\api\model\Cardinfo as CardinfoModel;
use app\api\model\Withdraw;
use app\api\model\Users as UsersModel;
use think\db;

class Order extends Model
{
    public static function getOrders($condition)
    {
        $list =  self::where($condition)->where(['status'=>'1'])->order('id DESC')->select();
        $list['count'] = self::where($condition)->where(['status'=>'1'])->count();
        return $list;
    }

    //获取今日收入
    public static function getToday($status='',$date)
    {
        $list = self::where(['status'=>$status])->whereTime('create_time', $date)->count();
        return $list;
    }

    public static function addOrderInfo($orderInfo)
    {
        return self::insertGetId($orderInfo);
    }

    //获取最近卖出列表
    public static function getNearSell($uid,$condition)
    {
        $data['res'] = db('order o')
            ->join('cardinfo c','o.card_id=c.id','left')
            ->join('product p','o.pro_id=p.id','left')
            ->where(['o.uid'=>$uid,'o.status'=>['in',['2','4','5']]])
            ->where($condition)
            ->field('o.id,o.order_num,FROM_UNIXTIME(o.pay_time) as pay_time,o.pro_num,o.price,o.user_price,o.mobile,o.status,o.pay_type,o.pro_name,p.product_name,p.categorys_id,c.card_num,c.card_pwd')
            ->select();
        if($data['res']){
            $data['count'] = db('order o')
                ->join('cardinfo c','o.card_id=c.id','left')
                ->join('product p','o.pro_id=p.id','left')
                ->where(['o.uid'=>$uid,'o.status'=>['in',['2','4','5']]])
                ->where($condition)
                ->count();
        }

        return $data;
    }

    //获取改商户的所有订单数据
    public static function getList($condition,$page,$size)
    {
      $res['data'] = db('order o')
            ->join('product p','o.pro_id=p.id','left')
            ->where(['o.order_type'=>'0'])
            ->where($condition)
            ->page($page,$size)
          	->order('o.create_time DESC')
        	->field('o.uid,o.id,o.order_num,FROM_UNIXTIME(o.create_time) as time,o.pro_num,o.price,o.card_id,o.user_price,o.mobile,o.status,o.pay_type,p.product_name,o.pro_name,p.categorys_id')
            ->select();
        if($res['data']){
            foreach($res['data'] as $key=>$value){
                $card_id_arr = explode(',',$value['card_id']);
                $res['data'][$key]['card_pwd'] = CardinfoModel::getField($card_id_arr,'card_pwd');
            }
          $res['count'] =  db('order o')
              ->join('product p','o.pro_id=p.id','left')
              ->where(['o.order_type'=>'0'])
              ->where($condition)
              ->count();
        }
        return $res;
      
      
      
    }

    //下单总数、
    public static function OrderCount($uid,$condition)
    {
        return  db('order o')
            ->join('cardinfo c','o.card_id=c.id','left')
            ->join('product p','c.product_id=p.id','left')
            ->join('categorys cate','p.categorys_id=cate.id','left')
            ->where(['o.uid'=>$uid])
            ->where($condition)
            ->count();
    }
    //支付订单量
    public static function payOrderCon($uid,$condition)
    {
        return  db('order o')
            ->join('cardinfo c','o.card_id=c.id','left')
            ->join('product p','c.product_id=p.id','left')
            ->join('categorys cate','p.categorys_id=cate.id','left')
            ->where(['o.uid'=>$uid,'o.status'=>'2'])
            ->where($condition)
            ->count();
    }
    //交易总金额
    public static function getPrice($uid,$condition)
    {
        $res = db('order o')
            ->join('cardinfo c','o.card_id=c.id','left')
            ->join('product p','c.product_id=p.id','left')
            ->join('categorys cate','p.categorys_id=cate.id','left')
            ->where(['o.uid'=>$uid,'o.status'=>'2'])
            ->where($condition)
            ->field('o.id,o.price')
            ->select();
        $price = '0';
        if($res){
            foreach($res as $k=>$v){
                $price += $v['price'];
            }
        }
        return $price;
    }
    //商户分成总金额
    public static function UserPrice($uid,$condition)
    {
        $res = db('order o')
            ->join('cardinfo c','o.card_id=c.id','left')
            ->join('product p','c.product_id=p.id','left')
            ->join('categorys cate','p.categorys_id=cate.id','left')
            ->where(['o.uid'=>$uid,'o.status'=>'2'])
            ->where($condition)
            ->field('o.id,o.user_price')
            ->select();
        $userPrice = '0';
        if($res){
            foreach($res as $k=>$v){
                $userPrice += $v['user_price'];
            }
        }
        return $userPrice;
    }
    //订单利润统计
    public static function profitPrice($uid,$condition)
    {
        $res = db('order o')
            ->join('cardinfo c','o.card_id=c.id','left')
            ->join('product p','c.product_id=p.id','left')
            ->join('categorys cate','p.categorys_id=cate.id','left')
            ->where(['o.uid'=>$uid,'o.status'=>'2'])
            ->where($condition)
            ->field('o.id,o.user_price,o.card_id')
            ->select();
        $profit = '0';
        if($res) {
            foreach($res as $k=>$v){
                $arr = self::getCost($v['card_id']);
                $profit += ($v['user_price'] - $arr['cost_price']);
            }
        }
        return $profit;
    }

    //获取成本价
    public static function getCost($card_id)
    {
        $res = db('order o')
            ->join('cardinfo c','o.card_id=c.id','left')
            ->join('product p','c.product_id=p.id','left')
            ->where(['o.card_id'=>$card_id])
            ->field('p.cost_price')
            ->find();
        return $res;
    }

    //根据订单号获取信息
    public static function getOrderInfo($order_num)
    {
        return self::where(['order_num'=>$order_num])->find();
    }

    public function getInfo($order_num)
    {
        return self::where(['order_num'=>$order_num])->find();
    }
    //修改订单状态
    public static function updateOrder($id,$data)
    {
        return self::where(['id'=>$id])->update($data);
    }

    public function updateData($id,$data)
    {
        return self::where(['id'=>$id])->update($data);
    }

    public static function getCanMoney($uid)
    {
        //今日的提现金额
        $today_P = Withdraw::ToDayWith($uid);
        $today_P = (string) $today_P;
        $userM = UsersModel::getField($uid,'can_use_money');
      	$can_use_money = $userM['can_use_money'];
        $data = self::where(['uid'=>$uid,'status'=>'2'])->whereTime('pay_time', 'today')->field('id,order_num,user_price')->select();
        $money = '0';
        if($data){
            foreach($data as $key=>$val){
                $money += $val['user_price'];
            }
        }
        $canWith = $can_use_money-$money;
        return $canWith;
    }
    public static function getTodayMoney($uid)
    {
        $data = self::where(['uid'=>$uid,'status'=>'2'])->whereTime('pay_time', 'today')->field('id,order_num,user_price')->select();
        $money = 0;
        if($data){
            foreach($data as $key=>$val){
                $money += $val['user_price'];
            }
        }
        return $money;
    }

    public static function yesterday_order($uid)
    {
        return self::where(['uid'=>$uid])->whereTime('pay_time', 'yesterday')->count();
    }
    public static function today_order($uid)
    {
        return self::where(['uid'=>$uid])->whereTime('pay_time','today')->count();
    }

    public static function getNums($uid)
    {
        $res = self::where(['uid'=>$uid])->whereTime('pay_time','today')->select();
        $nums = 0;
        if($res){
            foreach($res as $k=>$v){
                $nums += $v['pro_num'];
            }
        }
        return $nums;
    }

    public static function getTodayCost($uid)
    {
        $res = self::where(['uid'=>$uid,'status'=>'2'])->whereTime('pay_time','today')->select();
        $all_cost = 0;
        if($res){
            foreach($res as $k=>$v){
                $cost = Product::getField($v['pro_id'],'cost_price');
                $costs = $cost*$v['pro_num'];
                $all_cost += $costs;
            }
        }
        return $all_cost;
    }

    //今日付款总额
    public static function geAllMoney($time)
    {
        return self::where(['status'=>'2'])->whereTime('pay_time',$time)->field('case when SUM(pay_price) is null then 0 else SUM(pay_price) end as allMoney')->find();
    }

    //今日用户总收入
    public static function geUserMoney($time)
    {
        return self::where(['status'=>'2'])->whereTime('pay_time',$time)->field('case when SUM(user_price) is null then 0 else SUM(user_price) end as userMoney')->find();
    }

    //订单商品用户的信息
    public static function getListInfo($condition,$page,$size)
    {
        $data['res'] = db('order o')
            ->join('product p','o.pro_id=p.id','left')
            ->join('user u','o.uid=u.id','left')
            ->where($condition)
            ->page($page,$size)
            ->field('o.id as order_id,u.username,p.product_name,o.mobile,o.order_num,FROM_UNIXTIME(o.create_time) as time,o.status,o.pay_type,o.price,o.pay_price,o.user_price')
            ->select();
        if($data['res']){
            foreach($data['res'] as $key=>$val){
                $data['res'][$key]['tableInCome'] = $val['pay_price']*0.03*0.8;
                $data['res'][$key]['ip'] = request()->ip();
            }
            $data['count'] = db('order o')
                ->join('product p','o.pro_id=p.id','left')
                ->join('user u','o.uid=u.id','left')
                ->count();
        }
        return $data;
    }

    public static function orderNums($where)
    {
        return self::where($where)->count();
    }
    public static function orderMoney($where,$field)
    {
        $data = self::where($where)->field($field)->select();
        if($data){
            $orderM = '';
            foreach($data as $key=>$val){
                $orderM += $val[$field];
            }
        }
        return $orderM;
    }

    public static function getIdInfo()
    {
        $res = self::where(['status'=>'2'])->group('uid')->field('SUM(user_price) as price')->select();
        if($res){
            $money = 0;
            foreach($res as $k=>$v){
                $money += $v['price'];
            }
        }
        return $money;
    }

    public static function getInMon($where,$type='0',$payType='')
    {
        $data = self::where($where)->select();
        if($type == '0'){
            if($data){
                $userM=0;
                foreach ($data as $k=>$v){
                    $userM += $v['user_price'];
                }
            }
            return $userM;
        }else if($type=='1'){
            if($data){
                $aliRate = db('pay_type')->where(['id'=>$payType])->value('ali_rate');
                $TabM = 0;
                foreach ($data as $k=>$v){
                    $rate = db('user_pay_rate')->where(['uid'=>$v['uid']])->find();
                    if($rate){
                        $payRate = $rate['rate'];
                    }else{
                        $payRate = '0.97';
                    }
//                    $TabM += $v['pay_price']-$v['user_price'];
                    $TabM += $v['pay_price']*(1-$payRate-$aliRate/100);
                }
            }
            return $TabM;
        }
    }

    public static function TouSuOrder($uid,$page,$size)
    {
        $data['res'] =db('order o')
            ->join('product p','o.pro_id=p.id','left')
            ->join('user u','o.uid=u.id','left')
            ->where(['o.uid'=>$uid])
            ->where(['o.status'=>'4'])
            ->page($page,$size)
            ->field('o.order_num,p.product_name,o.pay_price,o.create_time,u.qq,u.mobile,o.err_msg,o.status,o.evidence,o.user_price')
            ->select();
        if($data['res']){
            $data['count'] = db('order o')
                ->join('product p','o.pro_id=p.id','left')
                ->join('user u','o.uid=u.id','left')
                ->where(['o.uid'=>$uid])
                ->where(['o.status'=>'4'])
                ->count();
        }
        return $data;
    }

    public static function agentList($uid,$order_type,$page,$size,$condition)
    {
        $res['data'] = db('order o')
            ->join('product p','o.pro_id=p.id','left')
            ->where(['o.uid'=>$uid,'o.order_type'=>$order_type])
            ->where($condition)
            ->page($page,$size)
          	->order('o.create_time DESC')
            ->field('o.order_num,o.pay_type,o.price,o.pay_price,o.mobile,o.status,FROM_UNIXTIME(o.create_time) as create_time,o.card_id,p.product_name,o.pro_name')
            ->select();
        if($res['data']){
            foreach($res['data'] as $key=>$value){
                $card_id_arr = explode(',',$value['card_id']);
                $res['data'][$key]['card_pwd'] = CardinfoModel::getField($card_id_arr,'card_pwd');
            }
          $res['count'] =  db('order o')
              ->join('product p','o.pro_id=p.id','left')
              ->where(['o.uid'=>$uid,'o.order_type'=>$order_type])
              ->where($condition)
              ->count();
        }
        return $res;
    }

    public static function getField($order_num,$field)
    {
        return self::where(['order_num'=>$order_num])->value($field);
    }

    public static function suppleOrder($uid,$page,$size,$condition)
    {
        $res['data'] = db('order o')
            ->join('product p','o.pro_id=p.id','left')
            ->where(['o.supple_id'=>$uid,'o.order_type'=>'1'])
            ->where($condition)
            ->field('o.order_num,p.product_name,o.pro_name,o.pay_type,o.price,o.pay_price,o.mobile,o.status,o.card_id,FROM_UNIXTIME(o.create_time) as create_time')
            ->page($page,$size)
          	->order('o.create_time DESC')
            ->select();
        if($res['data']){
            foreach($res['data'] as $key=>$value){
                $card_id_arr = explode(',',$value['card_id']);
                $res['data'][$key]['card_pwd'] = CardinfoModel::getField($card_id_arr,'card_pwd');
            }
            $res['count'] =  db('order o')
                ->join('product p','o.pro_id=p.id','left')
                ->where(['o.supple_id'=>$uid,'o.order_type'=>'1'])
                ->where($condition)
                ->count();
        }
        return $res;
    }

    public static function Orders($mobile)
    {
      $data['res'] = db('order o')
            ->join('user u','o.uid=u.id','left')
            ->where(['o.mobile'=>$mobile])
            ->field('o.id as order_id,u.username,o.mobile,o.order_num,FROM_UNIXTIME(o.create_time) as time,o.status,o.order_type,o.pay_type,o.price,o.pay_price,o.user_price,o.card_id,u.qq')
            ->select();
        if($data['res']){
            foreach($data['res'] as $key=>$value){
                $card_id_arr = explode(',',$value['card_id']);
                $data['res'][$key]['card_pwd'] = CardinfoModel::getField($card_id_arr);
            }
        }
        return $data;
    }

    public static function getOrderByONum($order_num)
    {
      $data['res'] = db('order o')
            ->join('user u','o.uid=u.id','left')
            ->where(['o.order_num'=>$order_num])
            ->field('o.uid,o.id as order_id,u.username,o.mobile,o.order_num,FROM_UNIXTIME(o.create_time) as time,o.status,o.order_type,o.pay_type,o.price,o.pay_price,o.user_price,o.card_id,u.qq')
            ->select();
        if($data['res']){
            foreach($data['res'] as $key=>$value){
                $card_id_arr = explode(',',$value['card_id']);
                $data['res'][$key]['card_pwd'] = CardinfoModel::getField($card_id_arr);
            }
        }
        return $data;
    }

//供货商收益
    public static function suppleInCome($supple_id,$page,$size,$condition)
    {
        $res['data'] = db('order o')
            ->join('agent_product ap','o.pro_id=ap.id','left')
            ->join('product p','ap.supple_pro_id=p.id','left')
            ->where(['o.supple_id'=>$supple_id,'o.order_type'=>'1','o.status'=>'2'])
            ->where($condition)
            ->field('o.order_num,p.product_name,o.pro_num,o.pay_type,o.price,o.pay_price,o.mobile,o.status,o.card_id,o.create_time,ap.product_name,ap.old_price,p.sell_price,p.cost_price')
            ->page($page,$size)
            ->select();
        if($res['data']){
            $money      = '';
            $cost_money = '';
            $profit     = '';
            foreach($res['data'] as $key=>$value){
                $card_id_arr = explode(',',$value['card_id']);
                $res['data'][$key]['card_pwd'] = CardinfoModel::getField($card_id_arr,'card_pwd');
                $money      += ($value['old_price']*$value['pro_num']);
                $cost_money += ($value['cost_price']*$value['pro_num']);
                $profit     +=  $money-$cost_money;
            }
            $res['money']       = $money;
            $res['cost_money']  = $cost_money;
            $res['profit']      = $profit;
            $res['count'] =  db('order o')
                ->join('product p','o.pro_id=p.id','left')
                ->where(['o.supple_id'=>$supple_id,'o.order_type'=>'1','o.status'=>'2'])
                ->where($condition)
                ->count();


        }
        return $res;
    }
//代理商收益分析
    public static function agentShouYi($uid,$page,$size,$condition)
    {
        $res['data'] = db('order o')
            ->join('agent_product ap','o.pro_id=ap.id','left')
            ->where(['o.uid'=>$uid,'o.order_type'=>'1','o.status'=>'2'])
            ->where($condition)
            ->page($page,$size)
            ->field('o.order_num,o.pay_type,o.pro_num,o.price,o.pay_price,o.card_id,o.mobile,o.status,o.create_time,o.card_id,ap.product_name,ap.old_price,ap.now_price')
            ->select();
        if($res['data']){
            $money = '';
            $cost_money = '';
            $profit = '';
            foreach($res['data'] as $key=>$value){
                $card_id_arr = explode(',',$value['card_id']);
                $res['data'][$key]['card_pwd'] = CardinfoModel::getField($card_id_arr,'card_pwd');
                $money += $value['pay_price'];
                $cost_money += ($value['old_price']*$value['pro_num']);
                $profit += ($money-$cost_money);
            }

            $res['count'] =  db('order o')
                ->join('agent_product ap','o.pro_id=ap.id','left')
                ->where(['o.uid'=>$uid,'o.order_type'=>'1','o.status'=>'2'])
                ->where($condition)
                ->count();
            $res['money'] = $money;     //zong
            $res['cost_money'] = $cost_money;     //zong
            $res['profit'] = $profit;
        }
        return $res;
    }

    public static function upDataByOrderNum($order_num,$data)
    {
        return self::where(['order_num'=>$order_num])->update($data);
    }
  
  	public static function NOCommsion_money($id_arr,$YJ_rate)
    {
        $money = '0';
        for($i=0;$i<count($id_arr);$i++){
            $priceS = self::getMoneys($id_arr[$i],$type='1');
            $money += $priceS;
        }
        $no_Com_price = $money*$YJ_rate;
        return $money;
    }

    public static function getMoneys($uid,$type)
    {
        if($type == '1'){   //当月未结
            $res = self::where(['uid'=>$uid,'status'=>'2'])->whereTime('create_time', 'month')->field('pay_price')->select();
            $money = '0';
            foreach ($res as $k=>$v){
                $money += $v['pay_price'];
            }
        }elseif ($type == '2'){ //所有的金额
            $res = self::where(['uid'=>$uid,'status'=>'2'])->field('pay_price')->select();
            $money = '0';
            foreach ($res as $k=>$v){
                $money += $v['pay_price'];
            }
        }
        return $money;
    }
    public static function Commsion_money($id_arr,$YJ_rate)
    {
        $money = '0';
        for($i=0;$i<count($id_arr);$i++){
            $priceS = self::getMoneys($id_arr[$i],$type='2');
            $money += $priceS;
        }
        $no_Com_price = $money*$YJ_rate;
        return $money;
    }
  
  	public static function getOrderInfoByNum($order_num)
    {
    	return self::where(['order_num'=>$order_num])->find();
    }
  
  	public static function orderMessage($order_num)
    {
      	$data = db('order o')
            ->join('user u','o.uid=u.id','left')
            ->where(['o.order_num'=>$order_num])
            ->field('o.uid,o.id as order_id,u.username,o.mobile,o.order_num,FROM_UNIXTIME(o.create_time) as time,o.status,o.order_type,o.pay_type,o.price,o.pay_price,o.user_price,o.card_id,u.qq,o.is_SendSms,o.is_SendEmail,o.email')
            ->find();
        if($data){
          $card_id_arr = explode(',',$data['card_id']);
          $data['card_pwd'] = CardinfoModel::getField($card_id_arr);
        }
        return $data;
    }
  	public static function UpStatus($order_num)
    {
        return self::where(['order_num'=>$order_num])->update(['status'=>'5']);
    }
  
  	public static function getOrderByONum1($order_num)
    {
        $data = db('order o')
            ->join('product p','o.pro_id=p.id','left')
            ->where(['o.order_num'=>$order_num])
            ->field('o.card_id,p.product_name')
            ->find();
        if($data){
          $strN = '';
          $strP = '';
          $cardIdArr = explode(',',$data['card_id']);
          for($i=0;$i<count($cardIdArr);$i++){
            $info = CardinfoModel::getInfo($cardIdArr[$i]);
            $card_num = CardinfoModel::getField1($cardIdArr[$i],'card_num');
            $card_pwd = CardinfoModel::getField1($cardIdArr[$i],'card_pwd');
            $strN .= $card_num.',';
            $strP .= $card_pwd.',';
          }
          $data['card_num'] = trim($strN,',');
          $data['card_pwd'] = trim($strP,',');
        }
        return $data;
    }
  
  	//获取投诉订单总金额
  	public static function getFreezeM($uid)
    {
    	$res = self::where(['uid'=>$uid,'status'=>'4'])->field('SUM(pay_price) as money')->find();
      	return $res['money'];
    }
}