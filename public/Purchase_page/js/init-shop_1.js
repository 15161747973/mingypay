var __curCid,
    __curPid,
    __curProducts,
    __curProduct
$(function () {
    var $category_select = $('#category-select'),
        $product_select = $('#product-select'),
        $kucun = $('.j-kucun'),//库存
        $gmsm = $('.j-gmsm'),//购买说明
        $yhsm = $('.j-yhsm'),//优惠说明
        $spsm = $('.j-spsm'),//商品说明
        $j_count = $('.j-count'),//购买数量
        $j_name = $('.j-name'),//清单部分  名称
        $j_price = $('.j-price'),//清单部分  价格
        $j_count_input = $('.j-count-input'),//清单部分  数量
        $j_totalmount = $('.j-totalmount'),//清单部分  总价
        $j_charger = $('.j-charger'),//清单部分  手续费承担

        $j_contact_mobile = $('.j-contact-mobile'),//发送卡密的手机号
        $j_contact_input = $('.j-contact-input') //联系方式

    $category_select.on('change', function () {
        var $this = $(this)
        var cid = $this.val()
        __curCid = cid
        var products = getProductsByCid(cid)
        console.log(products)
        __curProducts = products
        setProductSelectHtml(products, $product_select)
    })
    $product_select.on('change', function () {
        var $this = $(this)
        var pid = $this.val()
        var curProduct = __curProducts[pid]
        __curProduct = curProduct
        __curPid = pid
        //变换商品信息
        $kucun.find('label').text(curProduct['wh_count'])
        var max_txt = curProduct['max_num'] ? ('最多购买' + curProduct['max_num'] + '张') : '无购买上限'
        var gmsmTxt = '最少购买' + curProduct['min_num'] + '张，' + max_txt
        if (curProduct['min_num'] != 1 || curProduct['max_num'] != 0) {
            $gmsm.show().find('i').text(gmsmTxt)
        }
        if (curProduct['batch_off'].length === 0) {
            $yhsm.hide()
        } else {
            var str = ''
            curProduct['batch_off'].forEach(function (item) {
                str += '满'
                str += item['reach_num']
                str += '件，单价'
                str += item['off'] / 100
                str += '元 ,'
            })
            str = str.slice(0, -1)
            $yhsm.show().find('i').text(str)
        }
        $spsm.html(curProduct['product_introduce'] || '暂无说明')

        $j_name.text(curProduct['name'])
        $j_price.text(curProduct['price'] / 100)

        var owner = curProduct['charger'] ? '买家' : '卖家'
        $j_charger.text(owner + '承担手续费')

        if (curProduct['access_pwd']) {
            $('.js-mima-remind').show(500)
        } else {
            $('.js-mima-remind').hide(500)
        }
        if (curProduct['need_password'] === 10) {
            $('.j-password').show(500)
        } else {
            $('.j-password').hide(500)
        }
        setProductInfoByCountChange()
    })
    $j_count_input.on('change', function () {
        if (!__curProduct) {
            layer.msg('请选择商品~')
            return
        }
        var $this = $(this)
        var count = $this.val()
        if (count < __curProduct['min_num']) {
            layer.msg('购买数量低于购买下限哦~')
            $this.val(__curProduct['min_num'])
        }

        if (__curProduct['max_num'] && count > __curProduct['max_num']) {
            layer.msg('购买数量不能超上限哦~')
            $this.val(__curProduct['max_num'])
        }

        setProductInfoByCountChange()
    })
    $(".j-add").click(function () {
        if (!__curProduct) {
            layer.msg('请选择商品~')
            return
        }
        if ($j_count_input.val() == "" || undefined || null) {
            $j_count_input.val(__curProduct['min_num'] || 1);
        }
        var curCount = parseInt($j_count_input.val())
        if (__curProduct['max_num'] && curCount >= __curProduct['max_num']) {
            layer.msg('购买数量不能超上限哦~')
        } else {
            curCount++
        }
        $j_count_input.val(curCount)
        setProductInfoByCountChange()
    })
    $(".j-decrease").click(function () {
        if (!__curProduct) {
            layer.msg('请选择商品~')
            return
        }
        if ($j_count_input.val() == "" || undefined || null) {
            $j_count_input.val(__curProduct['min_num'] || 1);
        }
        var curCount = parseInt($j_count_input.val())
        if (curCount <= __curProduct['min_num']) {
            layer.msg('购买数量低于购买下限哦~')
        } else {
            curCount--
        }
        $j_count_input.val(curCount)
        setProductInfoByCountChange()
    })

    $(".j-send").on("click", function () {
        var $this = $(this)
        var needSend = $this.attr('data-needSend')
        if (needSend == 1) {
            $(".youxiang").show(100)
        } else {
            $(".youxiang").hide(100)
        }
        $this.addClass('current').siblings('label').removeClass('current')
        setProductInfoByCountChange()
    });

    $('#render-order').on('click', function () {
        if (!__curProduct) {
            layer.msg('请选择商品~')
            return
        }
        if(!$('.js-paymethod-list li.current').length){
            layer.msg('请选择付款方式~')
            return
        }
        if (__curProduct['access_pwd']) {
            layer.prompt({
                title: '请输入下单密码，可找商家询问',
            }, function (value, index, elem) {

                validateAccPwd({
                    product_id: __curProduct['id'],
                    access_pwd: value
                }, function (res) {
                    if (res) {
                        layer.close(index);

                    }
                })
            })
        } else {
            doSubmitOrder()
        }
    })

    function doSubmitOrder() {
        var needSend = $('.j-send.current').attr('data-needSend') == 1
        var params = {
            category_id: __curCid,
            product_id: __curPid,
            count: $j_count_input.val(),
            contact: $j_contact_input.val(),
            pay_method: $('.js-paymethod-list li.current').attr('data-pay_type'),
            need_send: needSend ? 1 : 2,
        }
        if (needSend) {
            params['receieve_mobile'] = $j_contact_mobile.val()
        }
        if (__curProduct['need_password'] === 10) {
            params['password'] = $('.j-password-input').val()
        }
        $.ajax({
            cache: true,
            type: "POST",
            url: "/doSubmitOrder",
            data: params,
            async: false,
            error: function (request) {
                layer.msg("网络错误")
            },
            success: function (data) {
                if (data.errno == 0) {
                    var res = data.errres
                    if (res.charger == 1) {
                        res.amountDetail = (data.errres.unit_price) / 100 + "(单价)x" + data.errres.count + "(数量)+" + parseFloat((data.errres.charge_amount) / 100).toFixed(2) + "(手续费)"
                    } else {
                        res.amountDetail = (data.errres.unit_price) / 100 + "(单价)x" + data.errres.count + "(数量)"
                    }
                    res.totalAmount = parseFloat((data.errres.real_amount) / 100).toFixed(2) + "(元)"

                    showOrderRemind(function(){
                        showOrderDetail(res)
                    })
                } else {
                    layer.msg(data.errmsg)
                }

            }
        })
    }

    $(".li_follow label").on("click", function () {
        $(this).addClass("current");
        $(this).siblings("label").removeClass("current");
    });
    $(".zhifu_list li").on("click", function () {
        $(this).addClass('current');
        $(this).siblings('li').removeClass('current');
    });

    $('.nav_list a').mouseover(function () {
        $(this).addClass('current');
        $(this).siblings('a').removeClass('current');

    })

    function validateForm() {
        if (!__curProduct['id']) {
            layer.msg('商品ID不正确！');
            return false
        }

        if (!this.contact) {
            this.$message.error('请输入联系方式，手机号或QQ！');
            return
        }
        if (__curProduct['need_password'] != 1 && !this.password) {
            this.$message.error('请输入取卡密码！');
            return
        }
        if (!this.payType) {
            this.$message.error('请选择支付方式！');
            return
        }
        if (this.needSend) {
            if (!isPoneAvailable(this.receiveMobile)) {
                this.$message.error('如果选择发送至手机，请输入正确手机号！');
                return
            }
        }
    }

    function showOrderRemind(callback) {
        var content = '<h1>重要声明！</h1>\n' +
            ' <div>不二发卡是自动发货平台,卖家入驻平台销售商品<br><strong style=\'color: red\'>平台自身不销售任何商品！</strong>,商品使用问题请联系卖家！\n' +
            '</div>\n' +
            '<div><p style="margin-bottom: 10px;">本站仅提供自动发货服务,不做任何商品售后<br>虚拟物品，一经出售，概不退换</p><br>发现假卡/欺诈,请于<strong style=\'color: red\'>当天来不二发卡平台申请售后退款</strong>,过期概不受理<br>请于15分钟内完成支付\n' +
            '</div>'
        layer.open({
            type: 1,
            title: false,
            area: ['550px', 'auto'],
            shade: 0.6,
            btn: ['确定', '取消'],
            closeBtn: false,
            shadeClose: false,
            skin: 'self-ui',
            content: content,
            yes: function (i) {
                layer.close(i)
                callback()
            }
        });
    }

    // showOrderRemind()

    function showOrderDetail(orderInfo, callback) {
        var content = '<div class="order-detail">' +
            '<h2 class="order-tit">准备支付</h2>' +
            '<div class="order-container">' +
            '<div class="order-item">' +
            '<span>订单号</span>' +
            '<span>' + orderInfo['order_id'] + '</span>' +
            '</div>' +
            '<div class="order-item">' +
            '<span>创建时间</span>' +
            '<span>' + orderInfo['create_time'] + '</span>' +
            '</div>' +
            '<div class="order-item">' +
            '<span>付款方式</span>' +
            '<span>' + orderInfo['pay_method_name'] + '</span>' +
            '</div>' +
            '<div class="order-item">' +
            '<span>提交金额</span>' +
            '<span>' + orderInfo['totalAmount'] + '</span>' +
            '</div>' +
            '<div class="order-item">' +
            '<span>总价详情</span>' +
            '<span>' + orderInfo['amountDetail'] + '</span>' +
            '</div>' +
            '</div>' +
            '</div>'
        layer.open({
            type: 1,
            title: false,
            area: ['470px', 'auto'],
            shade: 0.6,
            btn: ['去付款', '取消'],
            closeBtn: false,
            shadeClose: false,
            skin: 'self-ui',
            content: content,
            yes: function (i) {
                window.open('/pay?order_id=' + orderInfo['order_id'])
                loopFechPayRes(orderInfo['order_id'])
            }
        });
    }
    // showOrderDetail({})

    function loopFechPayRes(order_id) {
        var loop = true

        function loop() {
            if (!loop) {
                return
            }
            $.get('/doQueryOrder', {orderid: order_id}, function (ret) {
                if (ret.errno == 0) {
                    loop = false;
                    layer.open({
                        content: '付款成功！',
                        success: function (layero, index) {
                            window.location.href = '/orderquery?order_id=' + order_id;
                        }
                    });
                } else {
                    // alert('尚未检测到支付成功,请耐心等待结果同步');
                }
            }, 'json');
            setTimeout(arguments.callee, 3000)
        }

        loop()
    }

    function setProductInfoByCountChange() {
        var count = $j_count_input.val()
        $j_count.text(count)
        $j_totalmount.text(getProductMount())
    }

    function getProductMount() {
        var needSend = $(".j-send.current").attr('data-needSend')
        var count = $j_count_input.val()
        if (!__curProduct) {
            return 0
        }
        return parseFloat(((__curProduct['price'] * count + (needSend - 0) * 10)) / 100).toFixed(2)
    }

});

function validateAccPwd(data, callback) {
    $.ajax({
        cache: true,
        type: "POST",
        url: "/doCheckAccessPwd",
        data: data,
        async: false,
        error: function (request) {
            layer.msg('网络错误')
        },
        success: function (res) {
            if (res.errno == 0) {
                callback(true)
            } else {
                alert(data.errmsg)
                callback(false)
            }
        }
    });
}

function getProductsByCid(cid) {
    return __category_list[cid]['products']
}

function setProductSelectHtml(products, $container) {
    var html;
    if (!products) {
        html = '<option value="" disabled selected style="display:none;">暂无商品</option>'
    } else {
        html = '<option value="" disabled selected style="display:none;">请选择商品</option>'
        //products是个对象 得排个序
        Object.keys(products).map(function (key) {
            return products[key]
        }).sort(function (a, b) {
            return b.rank - a.rank
        }).forEach(function (product) {
            html += '<option value="' + product['id'] + '">' + product['name'] + '</option>'
        })
    }
    $container.html(html)
}

function checkcontact2() {
    var contact = $("input[name='contact']").val();
    if ($("input[name='is_rev_sms']").is(":checked")) {
        var reg = /^(\d){11}$/;
        if (contact.length < 1) {
            layer.msg('请填写手机号码');
            $('[name=contact]').focus();
        }
        if ((contact.length >= 1 && contact.length < 11) || (contact.length >= 1 && !reg.test(contact))) {
            layer.msg('您输入的手机号码 不是11位数字');
            $('[name=contact]').focus();
        }
    } else {
        if (!contact || contact == null || contact == "" || contact == 0) {
            layer.msg('请填写联系方式');
            $('[name=contact]').focus();
        } else if (contact.length < 6) {
            layer.msg('您输入的联系方式 少于6位');
            $('[name=contact]').focus();
        }
    }
}

function checkcontact12() {
    var contacts = $("input[name='contacts']").val();
    if ($("input[name='is_rev_sms']").is(":checked")) {
        var reg = /^(\d){11}$/;
        if (contacts.length < 1) {
            layer.msg('请填写手机号码');
            $('[name=contacts]').focus();
        }
        if ((contacts.length >= 1 && contacts.length < 11) || (contacts.length >= 1 && !reg.test(contacts))) {
            layer.msg('您输入的手机号码 不是11位数字');
            $('[name=contacts]').focus();
        }
    } else {
        if (!contacts || contacts == null || contacts == "" || contacts == 0) {
            layer.msg('请填写联系方式');
            $('[name=contacts]').focus();
        } else if (contacts.length < 11) {
            layer.msg('您输入的联系方式 少于11位');
            $('[name=contacts]').focus();
        }
    }
}

function checkemail2() {
    var email = $("input[name='email']").val();
    var reg = /^([0-9a-zA-Z_-])+@([0-9a-zA-Z_-])+((\.[0-9a-zA-Z_-]{2,3}){1,2})$/;
    if (!email || email == null || email == "" || email == 0) {
        layer.msg('请填写邮箱地址');
        $('[name=email]').focus();
    } else if (!reg.test(email)) {
        layer.msg('请填写正确的邮箱地址');
        $('[name=email]').focus();
    }
}

