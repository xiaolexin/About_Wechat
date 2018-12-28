<?php
header("Content-type: text/html; charset=utf-8");
//以下为固定用法，实现和微信的对接、验证
define("TOKEN", "XinBlogs");
 
$wechatObj = new wxapi();

// 首次验证，下面四行代码放开注释。成功后，改回原样。

// if($_GET['echostr']){
//  $wechatObj->valid();
// }else{
    $wechatObj->responseMsg();
// }

class wxapi
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
 
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
 
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
 
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
 
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
 
            switch ($RX_TYPE)
            {
                case "text":
                    $resultStr = $this->receiveText($postObj);
                    break;
                case "event":
                    $resultStr = $this->receiveEvent($postObj);
                    break;
                case "image":
                    $resultStr = $this->receiveImage($postObj);
            }
            echo $resultStr;
        }else {
            echo "";
            exit;
        }
    }
//处理接受到用户消息的事件
    private function receiveText($object)
    {
        $funcFlag = 0;
        $keyword = trim($object->Content);
        if($keyword=='5'){//最热文章
            $data=file_get_contents('https://neweb.top/content/templates/FLY/wxecho.php?a=hot');
            $data=json_decode($data,true);
            $content=$data['data'];
        }elseif($keyword=='3'){//最新文章
            $data=$this->http_request('https://neweb.top/content/templates/FLY/wxecho.php?a=new');
            $data=json_decode($data,true);
            $content=$data['data'];
        }elseif($keyword=='2'){//随机文章
            $data=$this->http_request('https://neweb.top/content/templates/FLY/wxecho.php?a=rand');
            $data=json_decode($data,true);
            $content=$data['data'];
        }elseif($keyword=='6'){
            $content="<a href='https://neweb.top/contact.html'>博客留言本</a>";
            $resultStr = $this->transmitText($object, $content,$funcFlag);
            return $resultStr;
        }else{
            $content = callTuling($keyword);
            $resultStr = $this->transmitText($object, $content,$funcFlag);
            return $resultStr;
        }

        if(isset($content)&&!empty($content)){
            $resultStr = $this->transmitImgText($object, $content,$funcFlag);
        }else{
            $resultStr = $this->transmitText($object, '接口数据处理故障，请联系开发者。感谢！！！',$funcFlag);
        }

        return $resultStr;
    }
    //处理接受到用户图片的事件
    private function receiveImage($object)
    {
        $imgUrl=$object->PicUrl;
        // face++接口调用
        $data=$this->http_request('https://api-us.faceplusplus.com/facepp/v3/detect?api_key=**********&api_secret=************&image_url='.$imgUrl.'&return_landmark=0&return_attributes=gender,age,smiling,beauty,skinstatus,emotion,ethnicity');
        $result=json_decode($data,true);
        $checkResult=$result['faces'][0]['attributes'];
        $contentStr="本次检测结果出来啦，抱歉让您久等了！🎈🎈🎈\n
        🕗年龄：".$checkResult['age']['value']."\n
        🐼性别：".$this->getCnSex($checkResult['gender']['value'])."\n
        😊微笑度：".$checkResult['smile']['value']."%\n
        🍎情绪指数：\n ".$this->getEmoNum($checkResult['emotion'])."\n
        ❤民族：".$checkResult['ethnicity']['value']."\n
        【检测结果来自Face++,本账号不承担任何后果，仅供娱乐。建议上传清晰单人像检测】";
        $resultStr = $this->transmitText($object, $contentStr, $funcFlag);
        return $resultStr;
    }
//处理公众号被关注的事件
    private function receiveEvent($object)
    {
        $contentStr = "";
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = "您好，非常感谢您的关注。☺   我是<a href='https://neweb.top'>杰新博客</a>！快捷查看文章，请回复2⃣（随机文章）、3⃣（最新文章）、5⃣（最热文章）、6⃣ （博客留言本）即可得到精准推送。新功能：人像免费鉴定系统接入，快去上传照片吧！💚💛  更多有趣功能等待开发，或者您有什么好的建议，欢迎联系我。✅ QQ：996265368";
        }
        $resultStr = $this->transmitText($object, $contentStr);
        return $resultStr;
    }
//把图灵机器人返回的数据转换成微信使用的文本数据格式
    private function transmitText($object, $content, $flag = 0)
    {
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
<FuncFlag>%d</FuncFlag>
</xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }
    //单、多图文接口
    private function transmitImgText($object,$content) {
        $template="<xml>
                   <ToUserName><![CDATA[%s]]></ToUserName>
                   <FromUserName><![CDATA[%s]]></FromUserName>
                   <CreateTime>%s</CreateTime>
                   <MsgType><![CDATA[news]]></MsgType>
                   <ArticleCount>1</ArticleCount>
                   <Articles>";
        $templateBody="<item>
                   <Title><![CDATA[%s]]></Title>
                   <Description><![CDATA[%s]]></Description>
                   <PicUrl><![CDATA[%s]]></PicUrl>
                   <Url><![CDATA[%s]]></Url>
                   </item>";
        $templateFooter="</Articles>
                   </xml>";
        $header=sprintf($template, $object->FromUserName, $object->ToUserName, time());
        $footer = sprintf($templateFooter);
        $body= sprintf($templateBody, $content['title'], $content['description']='', $content['picUrl'], $content['url']);
//       foreach ($content as $k => $value) {//多图文回复
//              $body.= sprintf($templateBody, $value['title'], $value['description'], $value['picUrl'], $value['url']);
//       }

        return $header.$body.$footer;
    }
    //图片消息
    private function transmitImg($object,$content) {
        //暂缺media_id. 解决方法前往https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444738726
        $template="<xml>
                        <ToUserName>< ![CDATA[%s] ]></ToUserName>
                        <FromUserName>< ![CDATA[%s] ]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType>< ![CDATA[image] ]></MsgType>
                        <Image><MediaId>< ![CDATA[media_id] ]></MediaId>
                        </Image>
                   </xml>";
        $result= sprintf($template,$object->FromUserName,$object->ToUserName,time());
        return $result;
    }
    function getCnSex($data){
        if($data=='Female'){
            return "女性";
        }else{
            return "男性";
        }
    }
    function getEmoNum($data){
        foreach($data as $dk => $dv){
            if($dk=='sadness'){
                $sadness='悲伤值：'.$dv.'%';
            }elseif($dk=='neutral'){
                $neutral='中性值：'.$dv.'%';
            }elseif($dk=='disgust'){
                $disgust='厌恨值：'.$dv.'%';
            }elseif($dk=='anger'){
                $anger='愤怒值：'.$dv.'%';
            }elseif($dk=='surprise'){
                $surprise='惊讶值：'.$dv.'%';
            }elseif($dk=='fear'){
                $fear='恐惧值：'.$dv.'%';
            }else{
                $happiness='开心值：'.$dv.'%';
            }
        }
        return $sadness."   ".$neutral."   ".$disgust."   ".$anger."   ".$surprise."   ".$fear."   ".$happiness;
    }
    function http_request($url,$data=array()){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

}//创建函数调用图灵机器人接口


function callTuling($keyword)
{
    $apiKey = "6c370333a8d*********"; //填写后台提供的key
    $apiURL = "http://www.tuling123.com/openapi/api?key=KEY&info=INFO"; 
 
    $reqInfo = $keyword; 
    $url = str_replace("INFO", $reqInfo, str_replace("KEY", $apiKey, $apiURL));
    $ch = curl_init(); 
    curl_setopt ($ch, CURLOPT_URL, $url); 
   curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
    $file_contents = curl_exec($ch);
    curl_close($ch); 
//获取图灵机器人返回的数据，并根据code值的不同获取到不用的数据
    $message = json_decode($file_contents,true);
    $result = "";
    if ($message['code'] == 100000){
        $result = $message['text'];
    }else if ($message['code'] == 200000){
        $text = $message['text'];
        $url = $message['url'];
        $result = $text . " " . $url;
    }else if ($message['code'] == 302000){
        $text = $message['text'];
        $url = $message['list'][0]['detailurl'];
        $result = $text . " " . $url;
    }else {
        $result = "好好说话我们还是基佬";
    }
    return $result;
}
?>
