<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:89:"/www/wwwroot/test.mingypay.com/mingypay/public/../application/admin/view/order/index.html";i:1565341929;s:82:"/www/wwwroot/test.mingypay.com/mingypay/application/admin/view/layout/default.html";i:1563933564;s:79:"/www/wwwroot/test.mingypay.com/mingypay/application/admin/view/common/meta.html";i:1563933565;s:81:"/www/wwwroot/test.mingypay.com/mingypay/application/admin/view/common/script.html";i:1563933565;}*/ ?>
<!DOCTYPE html>
<html lang="<?php echo $config['language']; ?>">
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>
    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !$config['fastadmin']['multiplenav']): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>订单查询</title>
    <link rel="stylesheet" href="css/swiper.min.css" />
    <link rel="stylesheet" type="text/css" href="css/layer.css" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/animate.css">
    <style>
        body {
            min-width: 1320px;
            padding: 0;
            margin: 0;
            width: 100%;
            height: 1000px;
        }

        .mukuai img {
            width: 100%;
        }

        .mukuai {
            width: 100%;
            height: auto;
            position: fixed;
            top: 0;
            overflow: hidden;
            z-index: 0;
        }

        .jiewei {
            margin-top: 405px;
            width: 100%;
            /*height: 500px;*/
            /*position: absolute;*/
            /*bottom: 0;*/
            z-index: 1111;
        }

        .navbar ul li a:after {
            content: "";
            width: 0;
            height: 1px;
            position: absolute;
            top: 75%;
            left: 50%;
            transition: all .5s;
            border-bottom: 2px solid #FFFFFF;
        }

        .navbar li a:hover:after {
            left: 25%;
            width: 50%;
            text-align: center;
        }

        .collapse {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 90%;
            height: 70px;
        }

        .my-navbar a {
            background: transparent !important;
            color: #fff !important;
            font-size: 1.2em;
        }

        .my-navbar a:hover {
            background: transparent;
            outline: 0
        }

        .navbar-nav>li>a {
            padding-top: 15px;
            padding-bottom: 15px;
            padding-left: 20px;
            padding-right: 20px;
        }

        .nav-left {
            width: 300px;
            margin-right: 400px;
        }

        .navbar2 {
            border: 1px solid #FFFFFF;
            position: fixed;
            width: 100%;
            opacity: 0;
            transition: 0.3s all;
            z-index: 2222;
            background-color: #FFFFFF;
            border-bottom: 1px solid #E5E5E5;
        }

        .my-navbar2 a {
            background: transparent !important;
            color: #333 !important;
            font-size: 1.2em;
        }

        .my-navbar2 a:hover {
            background: transparent;
            outline: 0
        }

        .navbar2 ul li a:after {
            content: "";
            width: 0;
            height: 1px;
            position: absolute;
            top: 75%;
            left: 50%;
            transition: all .5s;
            border-bottom: 2px solid #000000;
        }

        .navbar2 li a:hover:after {
            left: 25%;
            width: 50%;
            text-align: center;
        }

        .foot {
            background-color: #F3F3F3;
            padding-top: 30px;
            border-top: 1px solid #E5E5E5;
            padding-bottom: 20px;
        }

        .foot h4 {
            font-weight: 100;
            font-family: "Microsoft YaHei", Arial;
            line-height: 1.34;
            color: #000000;
        }

        .foot p {
            color: #666;
            font-size: 15px;
            line-height: 35px;
        }

        .foot_con {
            display: flex;
            justify-content: space-between;
            width: 70%;
            margin: 0 auto;
        }

        .registerBtn {
            cursor: pointer;
            border-radius: 20px;
            width: 60%;
            text-align: center;
            margin-left: 10px;
            background: linear-gradient(to right, #ffa213, #ffc50a);
            padding: 6px 0;
        }

        .loginBtn {
            cursor: pointer;
            border-radius: 20px;
            width: 60%;
            text-align: center;
            margin-left: 5px;
            background: linear-gradient(to right, #ff6a3c, #ff2953);
            padding: 6px 0;
        }

        .loginBox {
            margin-top: 9px;
            display: flex;
            color: #FFFFFF;
            font-size: 18px;
            width: 156px;
            justify-content: space-between;
            /*margin-left:5px;*/
        }

        .bgImages{
            background-image: url("image/titleImage.png");
            background-size: 100% 70%;
            background-repeat: no-repeat;
            width: 100%;
            height:10px;
            text-align: center;
            padding-top: 130px;
        }

        .searchBox{
            margin: 80px auto;
            width: 55%;
            border: 2px solid #ff6a3c;
            padding: 15px 30px;
            border-radius: 50px;
            display: flex;
            justify-content: space-between;
        }



        .textBox::-webkit-input-placeholder { /* WebKit browsers */
            color: #000000;
            font-size: 20px;
        }

        .textBox::-moz-placeholder { /* Mozilla Firefox 19+ */
            color: #000000;
            font-size: 20px;
        }

        .textBox:-ms-input-placeholder { /* Internet Explorer 10+ */
            color: #000000;
            font-size: 20px;
        }
        .textBox{
            width: 70%;font-size: 20px;background-color: transparent;border: 0;
            color: #000000;
            outline: none;
        }

        .searchBtn{
            background-color: #b34834;
            border-radius: 30px;
            padding: 10px 30px;
            font-size: 20px;
            color: #FFFFFF;
            margin-top: 10px;
        }

        .orderInfoBox{
            margin: 40px auto;
            width: 80%;
        }

        .orderInfo{
            border: 1px solid #eeeeee;padding: 15px;margin: 0 auto;width: 60%;
        }
        .orderInfoText{
            font-size: 20px;font-weight: 600;color: #666666;
        }
        .firstLind{
            border-top: 1px solid #eeeeee;margin-top: -5px;
        }
        .orderData{
            font-weight: 600
        }
        .CardsClose{
            display: flex;justify-content: space-between;
        }
        .CardsCloseText{
            font-size: 18px;font-weight: 600;color: #d10000;margin-left: 10px;
        }
        .textFile{
            background: linear-gradient(to right, #5274b8, #453eac);border-radius: 40px;padding: 10px 15px;color: #FFFFFF;
        }
        .scondLine{
            border-top: 1px solid #eeeeee;margin-top: -5px;
        }
        .cardNum{
            border: 1px dashed #cd6a4c;background-color: #cfada0;display: flex;justify-content: space-between;margin-top: -10px;padding: 5px 10px;line-height: 20px;
        }
        .number{
            margin-top: 10px;color: #cd4012;
            width: 200px;
        }
        .copy{
            color:#cd4012;margin-top: 10px;font-size: 18px;
        }
        .tradeDescription{
            font-size: 18px;color: #666666;font-weight: 600;margin-top: 10px
        }
        .thirdlyLine{
            border-top: 1px solid #eeeeee;margin-top: -0px;
        }
        .buttonBox{display: flex;justify-content: space-between;}
        .qqBox{display: flex;background: linear-gradient(to right, #5274b8, #453dac);color: #FFFFFF;border-radius: 30px;padding: 7px 10px 0 10px}
        .qq{margin-top: 3px;}
        .qqImage{width: 16px;height: 16px;margin-top: 4px;}
        .rightBtn{background: linear-gradient(to right, #5274b8, #453dac);color: #FFFFFF;border-radius: 30px;padding: 5px 10px;}
        .btnInfo{background-color:#6b93e0 }
        .btnInfo{background:linear-gradient(to right, #4870be, #84a4e2) }
    </style>

</head>

<body>
<div style="position: absolute;z-index: 1113;width: 100%">

    <nav class="navbar navbar-top my-navbar" role="navigation">
        <div class="collapse container">
            <div class="nav-left"><img src="image/logo.png" alt="" /></div>
            <div class="nav-right navbar-collapse" id="example-navbar-collapse">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="http://test.mingypay.com">网站首页</a>
                    </li>
                    <li>
                        <a href="Serve.html">公司简介</a>
                    </li>
                    <li>
                        <a href="Case.html">联系我们</a>
                    </li>
                    <li>
                        <a href="viewpoint.html">订单查询</a>
                    </li>
                    <li>
                        <a href="ComplaintsQuery.html">[查询投诉]</a>
                    </li>
                </ul>
                <div class="loginBox">
                  <p class="loginBtn" onclick="toLogin()"><a style="font-size:16px" href="http://test.mingypay.com/login.html">登录</a></p>
                    <p class="registerBtn" onclick="toRegister()"><a style="font-size:16px" href="http://test.mingypay.com/register.html">注册</a></p>
                </div>
            </div>
        </div>
    </nav>
</div>
<div id="head2">
    <nav class="navbar2 navbar-top my-navbar2" role="navigation">
        <div class="collapse container">
            <div class="nav-left"><img src="image/logo.png" alt="" /></div>
            <div class="nav-right navbar-collapse">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="index.html">网站首页</a>
                    </li>
                    <li>
                        <a href="Serve.html">公司简介</a>
                    </li>
                    <li>
                        <a href="Case.html">联系我们</a>
                    </li>
                    <li>
                        <a href="viewpoint.html">订单查询</a>
                    </li>
                    <li>
                        <a href="ComplaintsQuery.html">[查询投诉]</a>
                    </li>
                </ul>
                <div class="loginBox">
                    <p class="loginBtn" onclick="toLogin()"><a style="font-size:16px" href="http://test.mingypay.com/login.html">登录</a></p>
                    <p class="registerBtn" onclick="toRegister()"><a style="font-size:16px" href="http://test.mingypay.com/register.html">注册</a></p>
                </div>
            </div>
        </div>
    </nav>
</div>
<div class="bgImages">
    <div class="searchBox">
        <input type="text" id="order_nums" class="textBox" placeholder="请输入订单号或联系方式查询"  value="<?php echo $orderInfo['order_num']; ?>">
        <p class="searchBtn" onclick="search_order()" style="cursor: pointer">搜索</p>
    </div>
</div>
<div style="width: 100%;margin-top: 230px">
    <div style="margin: 0 auto;width: 50%">
        <p style="color: #666666;font-size: 18px">免责声明：平台为次日结算，款项结算给商户所出现的售后问题请自行与卖家协商。订单投诉：通过订单号查询订单，可在【订单投诉】等待平台处理。</p>
        <p style="text-align: left;font-weight: 600;color: #333333;">发卡网防骗提醒：</p>
        <p style="color: #666666;font-size: 18px">1、卡密为“联系QQ拿货，加QQ拿货”2、卡密为“付款成功等待充值”3、商品有问题，卖家不售后4、以各种理由推脱到第二天遇到以上售后问题，请及时联系我们的客服QQ：888888888</p>
    </div>
</div>
<!--		订单信息-->
<div class="orderInfoBox">
    <div id="lists">
        <div order_info="" class="orderInfo" style="margin-top: 15px">
            <p class="orderInfoText">订单信息</p>
            <hr class="firstLind">
            <p><span>订单编号：<?php echo $orderInfo['order_num']; ?> </span></p>
            <p><span>下单时间：<?php echo $orderInfo['time']; ?> </span></p>
            <p><span>交易金额：<?php echo $orderInfo['price']; ?> </span></p>
            <p><span>支付金额：<?php echo $orderInfo['pay_price']; ?> </span></p>
            <p><span>订单状态：
              	<?php if($orderInfo['status'] == '2'): ?>
                        支付成功
                        <?php elseif($orderInfo['status'] == '4'): ?>
                        投诉订单
                        <?php endif; ?>
              	</span>
          	</p>
            <div class="CardsClose">
                <p class="CardsCloseText">卡密信息</p>
                <p id="export" card_info_plus='+ JSON.stringify(datas[i].card_pwd) + ' class="textFile export_file" style="cursor:pointer">导出为TXT文件</p>
            </div>
            <hr class="scondLine">
          	<?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
              <div class="cardNum">
                		<?php if($vo['card_num'] == null): ?>
                        <p class="number" style="">卡密：<?php echo $vo['card_pwd']; ?></p>
                        <?php else: ?>
                        <p class="number foo">卡号：<?php echo $vo['card_num']; ?></p>
                  		<p class="number" style="">卡密：<?php echo $vo['card_pwd']; ?></p>
                        <?php endif; ?>
                  <P class="copy" id="copy_msg_one" style="cursor:pointer" data-clipboard-target=".foo">一键复制</P>
              </div>
          <?php endforeach; endif; else: echo "" ;endif; ?>
            <p class="tradeDescription">商品说明</p>
            <hr class="thirdlyLine">
            <div class="buttonBox">
                <div class="qqBox" id="changeColor">
                    <p class="qq" style="cursor:pointer">商品问题，联系商户QQ：888888888</p>
                    <img class="qqImage" src="image/qqPlus.png" alt="">
                </div>
              <p id="btnPlus" class="rightBtn is_show" style="cursor:pointer;">
                <a class="c1" href="http://test.mingypay.com/OrderComplaints.html?order_num=<?php echo $orderInfo['order_num']; ?>&order_type=<?php echo $orderInfo['order_type']; ?>">投诉该卖家（请当天投诉）</a>
              </p>
            </div>
        </div>
    </div>
</div>
<div class="jiewei">
    <div class="foot" id="foot">
        <div class="foot_con">
            <div>
                <h4>网站导航</h4>
                <p>
                    首页 / 服务</br>
                    网站设计 / 手机APP / 软件设计 / UE原型</br>
                    工作流程 / 设计案例 / 新闻中心</br>
                    我们观点 / 关于公司</br>
                </p>
            </div>
            <div>
                <h4>新闻中心</h4>
                <p>
                    公司2019年会通知
                </p>
            </div>
            <div>
                <h4>联系方式</h4>
                <p>
                    地址: 徐州市泉山区矿大科技园B座130</br>
                    热线: 400-690-8077</br>
                    手机: 13225262011</br>
                </p>
            </div>
            <div>
                <h4>关注我们</h4>
                <p>
                    <img src="img/erweim.png" alt="" />
                </p>
            </div>
        </div>
        <div class="text-center" style="color: #666;font-size: 18px;">
            Copyright xxxxx.com, All Rights Reserved. 徐州融达网络科技有限公司
        </div>
    </div>
</div>
</body>

<script src="js/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="js/important.js" type="text/javascript" charset="utf-8"></script>
<script src="js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="js/clipboard.min.js"></script>
<script src="layer/layer.js"></script>
<script src="js/baseUrl.js"></script>
<script src="js/download.js"></script>
<script type="text/javascript">
    let card_info = [];
    let count = 0;
    let array = [];
    //页面跳转
    $("#btnPlus").click(function () {
        window.location.href = 'OrderComplaints.html';
    });

    //获取url传参
    function getUrlParam(name) {
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)"); //构造一个含有目标参数的正则表达式对象
        var r = window.location.search.substr(1).match(reg);  //匹配目标参数
        if (r!=null) return unescape(r[2]); return null; //返回参数值
    }
    let url_order_num = getUrlParam("order_number");
    let statu = getUrlParam("status");

    //判断是否从其他页面带过来订单号了,如果有订单号就查询带来的订单号
    if( url_order_num !== null ){
        //如果不等于空 订单号赋值给input查询
        $('#order_nums').val(url_order_num);
        order_notice(url_order_num);
    }

    function search_order() {
        if( $('#order_nums').val() === '' ){
            layer.msg("请输入订单号或联系方式");
            return false;
        }
        //判断输入的是订单号还是手机号//进行正则验证
        var phoneNum = /^1[3|4|5|8|7][0-9]{9}$/;
        if (!phoneNum.test($('#order_nums').val())) {
            statu = '1';//如果是订单号状态就是1
        } else {
            statu = '0';//手机号查询状态0
        }
        order_notice($('#order_nums').val());//调用查询订单号接口
    }

    function order_notice(num) {
        $.ajax({
            url: baseUrl + "/Order/searchOrder",//接口地址
            dataType: 'json',
            type: "POST",  //类型，
            data:{keyword: num},
            success: function (res) {
                console.log(res);
                var datas = res.data.res;
                if (res.code === "200") {
                    // let dataList = '';
                    // let dataLists_plus = '';
                    let html = '';
                    if ( res.data.res.length !== 0 ) {
                        if( statu === '0' ){
                            for (var i = 0;i < datas.length;i++) {
                                if (datas[i].status === 2) {
                                    datas[i].status = '支付成功';
                                } else if (datas[i].status === 1) {
                                    datas[i].status = '等待支付';
                                } else if (datas[i].status === 3) {
                                    datas[i].status = '支付失败';
                                } else if (datas[i].status === 4) {
                                    datas[i].status = '投诉订单';
                                } else if (datas[i].status === 5) {
                                    datas[i].status = '退款订单';
                                } else if (datas[i].status === 6) {
                                    datas[i].status = '代理订单';
                                } else if (datas[i].status === 7) {
                                    datas[i].status = '供货订单';
                                }
                                if (datas[i].pay_price === null) {
                                    datas[i].pay_price = '';
                                }

                                html += '<div order_info="${datas[i]}" class="orderInfo" style="margin-top: 15px">';
                                html += '<p class="orderInfoText">订单信息</p>';
                                html += '<hr class="firstLind">';
                                html += '<p><span>订单编号：</span><span class="orderdata">' + datas[i].order_num + '</span></p>';
                                html += '<p><span>下单时间：</span><span class="orderData">' + datas[i].time + '</span></p>';
                                html += '<p><span>交易金额：</span><span class="orderData">' + datas[i].price + '</span></p>';
                                html += '<p><span>支付金额：</span><span class="orderData">' + datas[i].pay_price + '</span></p>';
                                html += '<p><span>订单状态：</span><span style="color: #004200;">' + datas[i].status + '</span></p>';
                                html += '<div class="CardsClose">';
                                html += '<p class="CardsCloseText">卡密信息</p>';
                                html += '<p id="export" card_info_plus='+ JSON.stringify(datas[i].card_pwd) + ' class="textFile export_file" style="cursor:pointer">导出为TXT文件</p>';
                                html += '</div>';
                                html += '<hr class="scondLine">';
                                for (var j = 0; j < datas[i].card_pwd.length; j++){
                                    if( datas[i].card_pwd[j].card_num === null ){
                                        datas[i].card_pwd[j].card_num = '空';
                                    }
                                    if( datas[i].card_pwd[j].card_pwd === null ){
                                        datas[i].card_pwd[j].card_pwd = '空';
                                    }
                                    html += '<div class="cardNum">';
                                    html += '<p class="number foo">卡号：<span id="cartNamber">' + datas[i].card_pwd[j].card_num + '</span></p>';
                                    html += '<p class="number" style="margin-left: -350px">卡密：<span id="cartNamber">' + datas[i].card_pwd[j].card_pwd + '</span></p>';
                                    html += '<P class="copy" id="copy_msg_one" style="cursor:pointer" data-clipboard-target=".foo">一键复制</P>';
                                    html += '</div>';
                                }
                                html += '<p class="tradeDescription">商品说明</p>';
                                html += '<hr class="thirdlyLine">';
                                html += '<div class="buttonBox">';
                                html += '<div class="qqBox" id="changeColor">';
                                html += '<p class="qq" style="cursor:pointer">商品问题，联系商户QQ：' + datas[i].qq + '</p>';
                                html += '<img class="qqImage" src="image/qqPlus.png" alt="">';
                                html += '</div>';
                                html += '<p id="btnPlus" class="rightBtn is_show" style="cursor:pointer;">投诉该卖家（请当天投诉）</p>';
                                html += '</div>';
                                html += '</div>';
                                $("#lists").html(html);
                                //没支付成功的订单不能投诉
                                if( datas[i].status !== '支付成功' ){
                                    $('.is_show').css('display','none');
                                }
                            }
                            //点击导出按钮
                            $("body").on('click','.export_file',function () {
                                array.length = 0;//点击一次把上次数组清空
                                let card_password = JSON.parse($(this).attr('card_info_plus'));
                                for (var d = 0;d < card_password.length;d++){
                                    array.push("卡号：" + card_password[d].card_num + ';' + '卡密：' + card_password[d].card_pwd);
                                }
                                export_file(array.toString())
                            });
                        }else if( statu === '1' ){
                            if( datas[0].status === 2 ){
                                datas[0].status = '支付成功';
                            }else {
                                datas[0].status = '支付失败';
                            }
                            if( datas[0].pay_price === null ){
                                datas[0].pay_price = '';
                            }
                            let d = `<div class="orderInfo" style="margin-top: 15px">
													<p class="orderInfoText">订单信息</p>
													<hr class="firstLind">
													<p><span>订单编号：</span><span class="orderdata">${datas[0].order_num}</span></p>
													<p><span>下单时间：</span><span class="orderData">${datas[0].time}</span></p>
													<p><span>交易金额：</span><span class="orderData">${datas[0].price}</span></p>
													<p><span>支付金额：</span><span class="orderData">${datas[0].pay_price}</span></p>
													<p><span>订单状态：</span><span style="color: #004200;">${datas[0].status}</span></p>
													<div class="CardsClose">
														<p class="CardsCloseText">卡密信息</p>
														<p id="export" class="textFile" style="cursor: pointer">导出为TXT文件</p>
													</div>
													<hr class="scondLine">
													<div id="test"></div>
													<p class="tradeDescription">商品说明</p>
													<hr class="thirdlyLine">
													<div class="buttonBox">
														<div class="qqBox" id="changeColor">
															<p class="qq" style="cursor: pointer">商品问题，联系商户QQ：${datas[0].qq}</p>
															<img class="qqImage" src="image/qqPlus.png" alt="">
														</div>
														<p id="btnPlus" order_type="${datas[0].order_type}" order_num="${datas[0].order_num}" class="rightBtn" style="cursor: pointer;">投诉该卖家（请当天投诉）</p>
													</div>
												</div>`;
                            $("#lists").html(d);
                            let res_data = '';
                            for( var j = 0;j < datas[0].card_pwd.length;j++ ){
                                res_data += `<div class="cardNum">
													 <div class="foo" style="display: flex">
														 <p class="number">卡号：<span id="cartNamber">${datas[0].card_pwd[j].card_num}</span></p>
														 <p class="number">卡密：<span id="cartNamber">${datas[0].card_pwd[j].card_pwd}</span></p>
													 </div>
													 <P class="copy" id="copy_msg_one" data-clipboard-target=".foo" style="cursor: pointer">一键复制</P>
												 </div>`;
                                $("#test").html(res_data);
                            }
                            $("#export").click(function () 
                                        card_info.push('卡号：' + datas[0].card_pwd[j].card_num + ';'+'卡密：' + datas[0].card_pwd[j].card_pwd)
                                    }
                                }
                                export_file(card_info.toString())
                            });
                            //如果是失败订单隐藏投诉按钮
                            if( datas[0].status !== '支付成功' ){
                                $("#btnPlus").css('display','none');
                            }
                            //点击携带参数跳转页面
                            $("#btnPlus").click(function(){
                                window.location.href = 'OrderComplaints.html?order_type=' + $(this).attr("order_type") + '&order_num=' + $(this).attr("order_num");
                            });
                        }
                    }else {
                        layer.msg("请输如正确订单号或手机号");
                    }
                } else {
                    layer.msg(res.msg)
                }
            }
        });
    }
    //导出卡密信息接口
    function export_file(card_num) {
        download(card_num, "index.txt", "text/plain");
    }

    //一键复制
    $(document).ready(function(){
        var clipboard = new Clipboard('.copy');
        clipboard.on('success', function(e) {
            console.log(e);
            alert("测试3复制成功！")
        });
        clipboard.on('error', function(e) {
            console.log(e);
            alert("测试3复制失败！请手动复制")
        });
    });

    //投诉按钮鼠标移入事件 按钮变颜色
    window.onload = function() {
        //联系qq
        $("#changeColor").mouseover(function () {
            $("#changeColor").addClass('btnInfo');
        });
        $("#changeColor").mouseout(function () {
            $("#changeColor").removeClass('btnInfo');
        });
        //投诉按钮
        $("#btnPlus").mouseover(function () {
            $("#btnPlus").addClass('btnInfo');
        });
        $("#btnPlus").mouseout(function () {
            $("#btnPlus").removeClass('btnInfo');
        });
        //导出按钮
        $("#export").mouseover(function () {
            $("#export").addClass('btnInfo');
        });
        $("#export").mouseout(function () {
            $("#export").removeClass('btnInfo');
        });
    };

    //跳转登录页面
    function toLogin(){
        window.location.href = 'login.html'
    }
    //跳转注册页面
    function toRegister(){
        window.location.href = 'register.html'
    }


    //上方导航栏底部横线
    setInterval(function() {
        var a = parseInt($(document).scrollTop());
        /*$("#a").text(a)*/
        if(a >= 800) {
            $(".navbar2").css("top", "0px").css("opacity", "1");
        } else {
            $(".navbar2").css("top", "-50px").css("opacity", "0");
        }
    });

</script>

</html>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo $site['version']; ?>"></script>
    </body>
</html>