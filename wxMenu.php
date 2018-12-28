<?php

$menu = new Wxmenu;
if(isset($_REQUEST['a'])&&$_REQUEST['a']=='create'){
    $menu->createmenu();//创建菜单
}elseif(isset($_REQUEST['a'])&&$_REQUEST['a']=='select'){
    $menu->selectMenu();//查询菜单
}elseif(isset($_REQUEST['a'])&&$_REQUEST['a']=='delete'){
    $menu->deleteMenu();//删除所有菜单
}else{
    echo "<h2>请传参数a的值操作菜单，已支持a为delete/select/create三种。</h2>";
}

class  Wxmenu
{
    private $APPID = "********";
    private $APPSECRET = "***************";

    //获取access_token
    public function index()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->APPID . "&secret=" . $this->APPSECRET;
        $date = $this->postcurl($url);
        $access_token = $date['access_token'];
        return $access_token;
    }

    //拼接参数，带着access_token请求创建菜单的接口
    public function createmenu()
    {
        $data = '{
        "button":[

         {
           "type":"view",
                "name":"杰新博客",
                "url":"https://neweb.top"
        },

      {
          "name":"博客日常",
           "sub_button":[
            {
                "type":"click",
               "name":"最热文章",
               "key":"hotTitle"
            },
            {
                "type":"click",
               "name":"最新文章",
               "key":"newTitle"
            },
            {
                "type":"click",
               "name":"随机文章",
               "key":"randTitle"
            },
            {
                "type":"click",
               "name":"留言部落",
               "key":"leaveMsg"
            }]
       },

       {
            "name":"生活助手",
           "sub_button":[
           {
                "type":"view",
               "name":"必应壁纸",
               "url":"https://api.neweb.top/"
            },
            {
                "type":"view",
               "name":"免费音乐",
               "url":"https://neweb.top/music/"
            },
            ]
        }
       ]
 }';
        $access_token = $this->index();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $access_token;
        $result = $this->postcurl($url, $data);
        if($result['errcode']==0&&$result['errmsg']=='ok'){
            echo "恭喜您，菜单创建成功！！".date('Y-m-d H:m:s',time());
        }else{
            print_r($result);
        }
    }

    function selectMenu()
    {
        $access_token = $this->index();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=" . $access_token;
        $result = $this->postcurl($url);
        echo "<pre>";print_r($result);
    }

    function deleteMenu()
    {
        $access_token = $this->index();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=" . $access_token;
        $result = $this->postcurl($url);
        echo "<pre>";print_r($result);//{"errcode":0,"errmsg":"ok"} 删除所有菜单
    }

    //请求接口方法
    function postcurl($url,$data = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output = json_decode($output, true);
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

}
