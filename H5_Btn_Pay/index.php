<?php

// 第一步：引入支付逻辑处理文件
require 'WxpayService.php';

// 第二步：处理支付逻辑流程，为页面数据渲染作铺垫

//①、获取用户openid
$wxPay = new WxpayService($mchid,$appid,$appKey,$apiKey);
$openId = $wxPay->GetOpenid();      //获取openid
if(!$openId) exit('获取openid失败');
//②、统一下单
$outTradeNo = uniqid();     //你自己的商品订单号
$payAmount = 0.01;          //付款金额，单位:元
$orderName = '支付测试';    //订单标题
$notifyUrl = 'https://www.xxx.com/wx/notify.php';     //付款成功后的回调地址(不要有问号)
$payTime = time();      //付款时间
$jsApiParameters = $wxPay->createJsBizPackage($openId,$payAmount,$outTradeNo,$orderName,$notifyUrl,$payTime);
$jsApiParameters = json_encode($jsApiParameters);

//第三步：页面内容渲染
iclude('pay.html');