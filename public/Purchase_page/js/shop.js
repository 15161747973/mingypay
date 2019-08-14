var pay_type=0;function merTypeChange(this_e){var type_id=$(this_e).val();if(!(type_id>0)){formatMerlistHtml([]);return;}
var url=$(this_e).data('url');requestShopApi(url,{type_id:type_id},function(res_data){formatMerlistHtml(res_data['list']);});}
function formatMerlistHtml(mer_list){var mer_html='<option data-pwd="0" value="">请选择商品</option>';for(var k in mer_list){var mer=mer_list[k];mer_html+='<option data-pwd="'+mer['pwd']+'" value="'+mer['id']+'">'+mer['name']+'</option>';}
$('#merid').html(mer_html);formatMerinfoHtml('0.00','',0,'',0,'');}
function requestShopApi(url,params,callback,errcallback,not_show_gif,server_errcall){var code=$('#code').val();params['code']=code;var loader_index=null;$.ajax({url:url,data:params,type:'post',dataType:'json',beforeSend:function(){if(not_show_gif!==true){loader_index=layer.load();}},success:function(res_data){if(loader_index){layer.close(loader_index);}
if(!res_data['ok']){if(errcallback){errcallback(res_data);}else{var msg='';if(res_data.hasOwnProperty('msg')){msg=res_data['msg'];}
if(!msg){msg=res_data['error'];}
layer.msg(msg);}
return;}
if(callback){callback(res_data);}},error:function(){if(loader_index){layer.close(loader_index);}
layer.msg('服务器未响应请稍后重试');if(server_errcall){server_errcall();}}});}
function merChange(this_e){var type_id=$('#typeid').val();if(!(type_id>0)){formatMerlistHtml([]);return;}
var mer_id=$(this_e).val();if(!(mer_id>0)){formatMerinfoHtml('0.00','',0,'',0,'');return;}
var is_pwd=$(this_e).find('option:selected').data('pwd');var params={type_id:type_id,mer_id:mer_id};if(is_pwd){showMerPwdAlert(false,params);return;}else{getMerInfo(params,'');}}
$(function(){function autoShowPwdAlert(){var this_mer_e=$('#merid');var is_pwd=this_mer_e.data('pwd');if(is_pwd){var type_id=$('#typeid').val();var mer_id=this_mer_e.val();showMerPwdAlert(true,{type_id:type_id,mer_id:mer_id});}}
autoShowPwdAlert();});function showMerPwdAlert(is_must,params){formatMerinfoHtml('0.00','',0,'',0,'');var content='';content+='<div class="my_box" style="padding: 10px;">';content+='<div class="my_left" style="width: 90px;">密码：</div>';content+='<div class="my_right">';content+='<input id="u_mer_pwd" style="width: 200px;" name="contact" type="text" placeholder="请输入商品的验证密码">';content+='</div>';content+='</div>';var btn=['验证'];var closeBtn=0;if(!is_must){btn=['验证','取消'];closeBtn=1;}
layer.open({type:1,title:'商品密码验证',content:content,btn:btn,closeBtn:closeBtn,yes:function(index,layero){var pwd=$('#u_mer_pwd').val();if(pwd==''){layer.msg('密码不可以为空');return;}
params['pwd']=pwd;getMerInfo(params,function(res_data){if(res_data['msg']){layer.msg(res_data['msg']);return false;}
$('#mer_pwd').val(pwd);layer.close(index);return true;});},btn1:function(index,layero){layer.close(index);return false;},shade:[0.1,'#393D49'],fixed:true,resize:false});}
function getMerInfo(params,callback){var url=$('#merid').data('url');requestShopApi(url,params,function(res_data){var ok=true;if(typeof callback=='function'){ok=callback(res_data);}
if(ok){formatMerinfoHtml(res_data['price'],res_data['num_str'],res_data['min_buy_num'],res_data['explain'],res_data['max_num'],res_data['wholesale_list']);if(res_data['is_coupon']==1){showCouponBox();}else{hideCouponBox();}}});}
function formatMerinfoHtml(price,num_str,min_buy_num,explain,max_num,wholesale_list){price=parseFloat(price);min_buy_num=parseInt(min_buy_num);$('#price').html(price.toFixed(2));$('#num_str').html(num_str);$('#mer_wholesale').html(JSON.stringify(wholesale_list));$('#buy_num').data('min',min_buy_num).data('price',price.toFixed(2)).data('max',max_num).val(min_buy_num);$('#explain').html(explain);var total_price=price*min_buy_num;total_price+=totalPriceAddSmsPrice();$('#total_price').html(total_price.toFixed(2));$('#mer_pwd').val('');$('#coupon_price').val(0);if(num_str==''){hideCouponBox();}}
function totalPriceAddSmsPrice(){var send_sms_e=$('#send_sms');var add_price=0;if(send_sms_e.prop('checked')){add_price=send_sms_e.data('price');add_price=parseFloat(add_price);}
return add_price;}
$.formatNum={float:function(obj,num){var fixed_num=0;if(num){fixed_num=num;}
var tmep=obj.value.toString();if(tmep==''){obj.value='';return obj.value;}
if(tmep.match(/[^\d.]/g)){tmep=tmep.replace(/[^\d.]/g,"");}
if(tmep.match(/^\./g)){tmep=tmep.replace(/^\./g,"");}
if(tmep.match(/\.{2,}/g)){tmep=tmep.replace(/\.{2,}/g,".");}
if((tmep.split('.').length-1)>=2){tmep=tmep.replace(".","$#$").replace(/\./g,"").replace("$#$",".");}
if(fixed_num>0){var reg=new RegExp("^(-?\\d*)\\.?\\d{1,"+fixed_num+"}$");if(!reg.test(tmep)){var reg2=new RegExp("^(.*\\..{"+fixed_num+"}).*$");tmep=tmep.replace(reg2,"$1");}}
if(tmep!=obj.value){var index=0;var is_have=false;for(var i in tmep){index=i;if(obj.value[i]!=tmep[i]){is_have=true;break;}}
if(!is_have){index=tmep.length;}
obj.value=tmep;this.setMouseIndex(obj,index)
return obj.value;}
return obj.value;},setMouseIndex:function(obj,index){if(obj.setSelectionRange){obj.setSelectionRange(index,index);}else{var rtextRange=obj.createTextRange();rtextRange.moveStart('character',index);rtextRange.collapse(true);rtextRange.select();}},getMouseIndex:function(obj){var cursurPosition=-1;if(obj.selectionStart){cursurPosition=obj.selectionStart;}else{var range=document.selection.createRange();range.moveStart("character",-obj.value.length);cursurPosition=range.text.length;}
return cursurPosition;}};$('#buy_num').keyup(function(){var this_e=$(this);var num=$.formatNum.float(this,0);var max_num=this_e.data('max');num=parseInt(num);max_num=parseInt(max_num);if(num<=0||num=='NaN'||num==NaN||num==undefined||isNaN(num)){num=0;}
if(num>max_num){layer.msg('库存不足，无法购买');num=max_num;}
var json_str=$('#mer_wholesale').html();if(json_str==''||json_str==undefined||json_str=='NaN'){json_str='[]';}
var wholesale_arr=JSON.parse(json_str);var now_num=0;var now_price=0;for(var k in wholesale_arr){if(num>=wholesale_arr[k]['num']){if(now_num<wholesale_arr[k]['num']){now_num=wholesale_arr[k]['num'];now_price=wholesale_arr[k]['price'];}}}
if(now_num>0){$('#price').html(now_price);}else{$('#price').html(this_e.data('price'));}
if(num>0){this_e.val(num);}
formatNowAllPrice();});$('.pay_list .lab3').click(function(){$('.pay_list .pay_checked').removeClass('pay_checked');$('.choose_pay_type i').removeClass('icon-fuxuan2').addClass('icon-fuxuan1');$(this).addClass('pay_checked');pay_type=$(this).find('.pay_type').val();$(this).find('.choose_pay_type i').removeClass('icon-fuxuan1').addClass('icon-fuxuan2');});function hideCouponBox(){$('#mer_coupon').html('');$('#mer_coupon_input').val('');$('#mer_coupon_box').hide();}
function showCouponBox(){$('#mer_coupon').html('');$('#mer_coupon_input').val('');$('#mer_coupon_box').show();}
$('#mer_coupon_input').blur(function(){var msg_e=$('#mer_coupon');var type_id=$('#typeid').val();if(!(type_id>0)){msg_e.html('请先选择商品分类');return false;}
var mer_id=$('#merid').val();if(!(mer_id>0)){msg_e.html('请先选择商品');return;}
var this_e=$(this);var url=$(this_e).data('url');var coupon=this_e.val();msg_e.html('');$('#coupon_price').val(0);if(coupon==''){formatNowAllPrice();return false;}
requestShopApi(url,{type_id:type_id,mer_id:mer_id,coupon:coupon},function(res_data){msg_e.html(res_data['msg']);$('#coupon_price').val(res_data['price']);formatNowAllPrice();},function(res_data){var msg='';if(res_data.hasOwnProperty('msg')){msg=res_data['msg'];}
if(!msg){msg=res_data['error'];}
layer.msg(msg);formatNowAllPrice();});});function formatNowAllPrice(){var num=parseFloat($('#buy_num').val());if(num<=0||num=='NaN'||num==NaN||num==undefined||isNaN(num)){num=0;}
var price=parseFloat($('#price').html());var coupon_price=parseFloat($('#coupon_price').val());var total_price=price*num-coupon_price;if(total_price<0){total_price=0.01;}
total_price+=totalPriceAddSmsPrice();$('#total_price').html(total_price.toFixed(2));}
$('#submit_pay').click(function(){var mer_id=$('#merid').val();if(!(mer_id>0)){layer.msg('请先选择商品');return;}
var contact=$('#order_contact').val();if(contact==''){layer.msg('请输入联系方式');return;}
var send_sms=0;if($('#send_sms').prop('checked')){if(!isRealMobile(contact)){layer.msg('请输入正确的手机号码');return;}
send_sms=1;}
var email=$('#my_email').val();var send_email=0;if($('#send_email').prop('checked')){if(!isRealEmail(email)){layer.msg('请输入正确的邮箱地址');return;}
send_email=1;}
var coupon=$('#mer_coupon_input').val();var pwd=$('#mer_pwd').val();var url=$(this).data('url');var num=$('#buy_num').val();var min_num=$('#buy_num').data('min');min_num=parseFloat(min_num);if(num<=0||num=='NaN'||num==NaN||num==undefined||isNaN(num)){num=0;}
if(num<min_num){layer.msg('该商品一次最少购买 '+min_num+' 个');return;}
if(num<=0){layer.msg('购买数量必须大于0');return;}
var verify_code=$('#verify_code').val();if(verify_code==""||verify_code=='NaN'||verify_code==undefined){layer.msg('请输入验证码');return;}
if(verify_code.length!=4){layer.msg('验证码输入错误');return;}
if(pay_type<=0){layer.msg('请选择支付方式');return;}
var is_mobile=$('#_is_mobile').val();var post_params={pay_type_id:pay_type,mer_id:mer_id,num:num,contact:contact,coupon:coupon,pwd:pwd,send_sms:send_sms,email:email,send_email:send_email,verify_code:verify_code,is_mobile:is_mobile==1?1:0};layer.confirm('确定购买该商品？',{icon:3,title:'提示'},function(index){layer.close(index);requestShopApi(url,post_params,function(res_data){refreshVerifyCode();var area=['400px','460px'];var content='';content+='<div class="pay_qrcode_info">';content+='<div class="pay_name">'+res_data['payname']+'</div>';content+='<div class="pay_order">订单号：'+res_data['code']+'</div>';if(res_data['type']=='url'){content+='<img class="qrcode" src="'+res_data['qrcode_url']+'">';}else{content+='<a id="_payforit" class="payforit" href="'+res_data['qrcode_url']+'" target="_blank">点击去'+res_data['payname']+'</a>';area=['400px','250px'];}
content+='<span class="tips">等待付款中...</span>';content+='</div>';var interval_index=null;var options={type:1,title:'订单支付',content:content,btn:[],cancel:function(){if(layer_order_index!==null){clearInterval(interval_index);}},shade:[0.1,'#393D49'],fixed:true,resize:false};if(is_mobile==0){options['area']=area;}
var layer_order_index=layer.open(options);var order_code=res_data['code'];interval_index=setInterval(function(){requestShopApi(res_data['status_url'],{order_code:order_code},function(res){if(res['status']=='-1'){layer.close(layer_order_index);clearInterval(interval_index);}else if(res['status']=='1'){layer.msg('支付成功');clearInterval(interval_index);window.location.href=res['url'];}else if(res['status']=='2'){$('.pay_qrcode_info .tips').html('支付失败').css('color','red');clearInterval(interval_index);}},null,true);},2000);},function(res_data){var msg='';if(res_data.hasOwnProperty('msg')){msg=res_data['msg'];}
if(!msg){msg=res_data['error'];}
layer.msg(msg);refreshVerifyCode();},false,function(){refreshVerifyCode();});});});function refreshVerifyCode(){$('#verify_code').val('');$('#verify_code_img').attr('src',$('#verify_code_img').attr('src'));}
$('#verify_code_img').click(function(){refreshVerifyCode();});$('.receive_type').click(function(){var this_e=$(this);var type=this_e.find('input').data('type');if(this_e.hasClass('checked')){this_e.removeClass('checked');$('#send_'+type).prop('checked',false);if(type=='sms'){$('#order_contact').attr('placeholder','填写QQ号或手机号 订单查询的重要凭证').blur();var total_price=$('#total_price').html();total_price=parseFloat(total_price);total_price-=parseFloat($('#send_sms').data('price'));$('#total_price').html(total_price.toFixed(2));}else{$('.email_box').hide();}}else{this_e.addClass('checked');$('#send_'+type).prop('checked',true);if(type=='sms'){$('#order_contact').attr('placeholder','请填写你的手机号').focus();var total_price=$('#total_price').html();total_price=parseFloat(total_price);total_price+=totalPriceAddSmsPrice();$('#total_price').html(total_price.toFixed(2));}else{$('#order_contact').blur();$('.email_box').show();}}});function isRealMobile(str){var re=/^1\d{10}$/;return re.test(str);}
function isRealEmail(email){var reg=new RegExp("^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$");if(email===""){return false;}else if(!reg.test(email)){return false;}
return true;}