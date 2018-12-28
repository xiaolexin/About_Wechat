<?php

// 本页面链接地址格式：
// https://open.weixin.qq.com/connect/oauth2/authorize?appid=**********&redirect_uri=http://你的域名/getUserInfo.php&response_type=code&scope=snsapi_base&state=1#wechat_redirect


//具体封装，自己研究。这是最根本的PHP网页授权逻辑，仅供初学者学习参考，大牛请自觉路过~
$appid='***************';
$appkey='***********************';
//用户同意授权后,获取code.
//code说明 ： code作为换取access_token的票据，每次用户授权带上的code将不一样，code只能使用一次，5分钟未被使用自动过期。
$code=$_GET['code'];

//第二步：通过code换取网页授权access_token
$access=file_get_contents('https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appkey.'&code='.$code.'&grant_type=authorization_code');
/*
{ "access_token":"ACCESS_TOKEN",
"expires_in":7200,
"refresh_token":"REFRESH_TOKEN",
"openid":"OPENID",
"scope":"SCOPE" }
*/
$a=json_decode($access,1);

//第三步：刷新access_token（如果需要）
$re_access=file_get_contents('https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='.$appid.'&grant_type=refresh_token&refresh_token='.$a['refresh_token']);
/*{ "access_token":"ACCESS_TOKEN",
"expires_in":7200,
"refresh_token":"REFRESH_TOKEN",
"openid":"OPENID",
"scope":"SCOPE" }*/
$b=json_decode($re_access,1);
$userInfo=file_get_contents('https://api.weixin.qq.com/sns/userinfo?access_token='.$b['access_token'].'&openid='.$b['openid'].'&lang=zh_CN');
$user=json_decode($userInfo,1);

echo "<pre>";print_r(
    "<h2 style='font-size: 35px'>openid:".$user['openid']."</h2>
     <h2 style='font-size: 35px'>昵称:".$user['nickname']."</h2>
     <h2 style='font-size: 35px'>性别:".$user['sex']."</h2>
     <h2 style='font-size: 35px'>语言:".$user['language']."</h2>
     <h2 style='font-size: 35px'>城市:".$user['city']."</h2>
     <h2 style='font-size: 35px'>省:".$user['province']."</h2>
     <h2 style='font-size: 35px'>国家:".$user['country']."</h2>
     <h2 style='font-size: 35px'>头像:<img src='".$user['headimgurl']."' wdith='80%'></h2>
     <hr/>
     <h2 style='font-size: 35px'>本次访问access_token:".$b['access_token']."</h2>
     <h2 style='font-size: 35px'>本次访问code:".$code."</h2>
     "
);


// 附加部分：

//检查access_token是否有效
function checkAccessState(){
    $result=file_get_contents('https://api.weixin.qq.com/sns/auth?access_token=ACCESS_TOKEN&openid=OPENID');
//    无效：{ "errcode":40003,"errmsg":"invalid openid"}
//    有效:{ "errcode":0,"errmsg":"ok"}
}
//刷新access_token  2小时有效期
function refreshToke($refreshCode){
    $access=file_get_contents('https://api.weixin.qq.com/sns/oauth2/refresh_token?appid='.$appid.'&grant_type=refresh_token&refresh_token='.$refreshCode);
/*{ "access_token":"ACCESS_TOKEN",
"expires_in":7200,
"refresh_token":"REFRESH_TOKEN",
"openid":"OPENID",
"scope":"SCOPE" }*/
}


