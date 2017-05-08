<?php
require_once __DIR__ ."/../WechatPay.php";
$cfg = include './config.php';

if(empty( $_REQUEST['openid'])) {
	$redirect = "http://{$_SERVER['HTTP_HOST']}/demo/wxoauth.php";
	$this->redirect($url);
	exit;
}

$payment = new WechatPay($cfg);
$openid = $_REQUEST['openid'];
$stamp = date('YmdHis');
$ext = ['attach'=>''];
$desc = "desc$stamp";
$amt = 1;
$prepay_id = $payment->getPrepayId("$desc", "$stamp", $amt, $openid, $ext);
if(!$prepay_id){
	die("获取prepay_id失败");
}
$package = $payment->get_package($prepay_id);
?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js" ></script>
<script>
	wx.ready(function(){
		wx.chooseWXPay({
			appId: '<?=$package["appId"]?>',
			timestamp: <?=$package["timeStamp"]?>, // 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
			nonceStr: '<?=$package["nonceStr"]?>', // 支付签名随机串，不长于 32 位
			package: '<?=$package["package"]?>', // 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
			signType: '<?=$package["signType"]?>', // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
			paySign: '<?=$package["paySign"]?>', // 支付签名
			success: function (res) {
				// 支付成功后的回调函数
				alert('支付成功！');
			}
		});
		// config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
	});
	wx.error(function(res){// config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
		alert(res);
	});
</script>