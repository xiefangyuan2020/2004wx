<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class WxController extends Controller
{
	//接入
	public function index()
	{
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];

		$token = env('WX_TOkEN');
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);

		if ($tmpStr == $signature) {
			echo $_GET['echostr'];
		} else {
			echo "111";
		}
	}

	//获取access_token
	public function getAccessToken()
	{

		$key = 'wx:access_token';

		//检查是否有token
		$token = Redis::get($key);
		if ($token) {
			echo "有缓存";
			echo '</br>';
			echo $token;
		} else {
			echo "无缓存";
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . env('WX_APPID') . "&secret=" . env('WX_APPSEC');
			//echo $url;die;
			$response = file_get_contents($url);
			//echo $response;
			$data = json_decode($response, true);
			$token = $data['access_token'];

			//保存到redis中时间为3600

			Redis::set($key, $token);
			Redis::expire($key, 1000);
		}


		echo "access_token:" . $token;

	}


	public function wxEvent(Request $request)
	{
		$echostr = $request->echostr;
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];

		$token = env('WX_TOkEN');
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);

		if ($tmpStr == $signature) {  //验证通过
			//1.接收数据
			$xml_str = file_get_contents('php://input');
			//记录日志
//            file_put_contents('wx_event.log',$xml_str,'FILE_APPEND');
//            echo "$echostr";
//            die;
			//2.把xml文本转换成php的数组或者对象
			$data = simplexml_load_string($xml_str, 'SimpleXMLElement', LIBXML_NOCDATA);
			//判断该数据包是否是订阅的事件推送
			if (strtolower($data->MsgType) == "event") {
				//关注
				if (strtolower($data->Event == 'subscribe')) {
					//回复用户消息(纯文本格式)
					$toUser = $data->FromUserName;
					$fromUser = $data->ToUserName;
					$msgType = 'text';
					$content = '欢迎您的关注٩(๑❛ᴗ❛๑)۶';
					//%s代表字符串(发送信息)
					$template = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
					$info = sprintf($template, $toUser, $fromUser, time(), $msgType, $content);
					return $info;
				}
				//取关
				if (strtolower($data->Event == 'unsubscribe')) {
					//清除用户的信息
				}
			}
		} else {
			return false;
		}
	}

	//接收消息
    $obj=$this->receiveMsg();
    switch($obj->MsgType){
    case "text":
        //获取城市的名称
        $city=urlencode($obj->Content);
        $key="97e7508b0d7a44b957eddf115df0101e";
                       //使用get方式往第三方服务器上发送请求
                       $url="http://v.juhe.cn/weather/index?cityname=".$city."&key=".$key;
                       $result = json_decode(file_get_contents($url),true);
                       if($result["resultcode"]==200){
                           //查询天气预报成功
                           //今天的数据
                           $today=$result["result"]["today"];
                           $content="查询的城市:".$today["city"]."\n";
                           $content.="今天的日期是".$today["date_y"]." ".$today["week"]."\n";
                           $content.="天气:".$today["weather"]."温度:".$today["temperature"]."\n";
                           $content.="风级指数:".$today["wind"]."\n";
                           $content.="温度:".$today["dressing_advice"].",穿衣指数:".$today["dressing_advice"]."\n";
                           //未来七天
                           $future=$result["result"]["future"];
                           foreach($future as $key=>$val){
                               $content.=date("Y-m-d",strtotime($val["date"])).":";
                               $content.=$val["week"].",";
                               $content.= $val["weather"].",";
                               $content.=$val["weather"]."\n";
                           }
                       }else{
                           $content="查询错误,你的格式是:天气:地区名，请检查中国有没有这个城市";
                       }
                       //回复
                       $this->responseText($obj,$content);
                       break;
                   case "voice":
                       //开启语音识别后获取到了用户发的语音转化为汉字。
                       $text=urlencode($obj->Recognition);//将汉字转码
                       //调用图灵机器人的接口
                       $url="http://openapi.tuling123.com/openapi/api/v2";
                       //整理请求接口的数据
                       $param=[
                           "reqType"=>0,
                           "perception"=>[
                               "inputText"=>[
                                   "text"=>$text
                               ]
                           ],
                           "userInfo"=>[
                               "apiKey"=>"cf377b0562294eaab219780a3703d478",
                               "userId"=>1
                           ]
                       ];
                       $param=urldecode(json_encode($param));
                       //使用curl往接口url上面发送post请求
                       $result=$this->http_post($url,$param);
                       //将json格式的数据转化为数组
                       $result=json_decode($result,true);
                       if($result["intent"]["code"]!=5000){
                           $content=$result["results"][0]["values"]["text"];
                           $this->responseText($obj,$content);
                       }else{
                           $content="机器人出错，请联系管理员,123456789";
                           $this->responseText($obj,$content);
                       }
                       break;
               }

	//接收微信公众平台传过来的消息
    private function  receiveMsg(){
    //获取到微信公众平台发过来的消息，是个xml格式的数据
        $xml=file_get_contents("php://input");
        //为了方便操作我们将xml转化为对象。
        $obj=simplexml_load_string($xml,"SimpleXMLElement",LIBXML_NOCDATA);
        //返回
        return $obj;
    }

}

