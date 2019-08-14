<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/22
 * Time: 9:33
 */
namespace app\api\validate;

use think\Validate;

class UsersValidate extends Validate
{
    protected $regex = [ 'zip' => '/^1[3|4|5|8|7][0-9]{9}$/'];
    protected $rule = [
        'username'      =>  'require|min:6|max:18',
        'password'      =>  'require|max:18|min:6',
        'repeatpass'    =>  'require|confirm:password',
        'mobile'        =>  'require|regex:zip',//正则验证用户名（手机号码）
        'qq'            =>  'require',
    ];
    protected $message  =   [
        'username.require'      => '请填写用户名',
        'username.min'          => '用户名不小于6位',
        'username.max'          => '用户名不大于18位',
        'password.require'      => '请输入密码',
        'password.min'          => '密码不能小于6位',
        'password.max'          => '密码不能大于18位',
        'repeatpass.require'    => '请重复输入密码',
        'repeatpass.confirm'    => '两次密码输入不一致',
        'mobile.require'        => '请输入手机号码',
        'QQ.require'            => '请输入QQ号码',
        'mobile.regex'          => '手机号格式不正确',
    ];
    public function sceneLand()
    {
        return $this->only(['username','password']);
    }

}