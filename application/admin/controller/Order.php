<?php
/**
 * Created by PhpStorm.
 * User: Panda
 * Date: 2019/8/1
 * Time: 11:14
 */
namespace app\admin\controller;

use app\common\controller\Backend;
use app\api\model\Order as OrderModel;
use app\api\model\Cardinfo as CardinfoModel;
class order extends Backend
{
  
  	protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
    }
  
    public function orderInfo()
    {
        $order_num = $_GET['out_trade_no'];
        $order_info = OrderModel::orderMessage($order_num);
      	$card_id_arr = explode(',',$order_info['card_id']);
        $card_info = CardinfoModel::getField($card_id_arr);
        $this->assign('orderInfo',$order_info);
      	$this->assign('data',$card_info);
      	return view('order/index');
    }
}