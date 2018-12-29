<?php

require 'WxpayService.php';

$mchid = 'xxxx';          //微信支付商户号 PartnerID 通过微信支付商户资料审核后邮件发送
$appid = 'xxxx';  //公众号APPID 通过微信支付商户资料审核后邮件发送
$apiKey = 'xxxx';   //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥
$wxPay = new WxpayService($mchid,$appid,$apiKey);
$outTradeNo = uniqid();     //你自己的商品订单号
$payAmount = 0.01;          //付款金额，单位:元
$orderName = '支付测试';    //订单标题
$notifyUrl = 'https://www.xxx.com/wx/notify.php';     //付款成功后的回调地址(不要有问号)
$payTime = time();      //付款时间
$arr = $wxPay->createJsBizPackage($payAmount,$outTradeNo,$orderName,$notifyUrl,$payTime);
//生成二维码
$url = 'https://www.kuaizhan.com/common/encode-png?large=true&data='.$arr['code_url'];
echo "<img src='{$url}' style='width:300px;'><br>";
echo '二维码内容：'.$arr['code_url'];