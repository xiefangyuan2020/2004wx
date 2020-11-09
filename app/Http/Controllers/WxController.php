<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

use GuzzleHttp\Client;

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
			// echo "无缓存";
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . env('WX_APPID') . "&secret=" . env('WX_APPSEC');
			//echo $url;die;
			// $response = file_get_contents($url);
			//echo $response;

			//使用guzzle发起get请求
		    $client = new Client(); //实例化 客户端
		    $response = $client->request('GET',$url,['verify'=>false]); //发起请求并接收响应
		    $json_str = $response->getBody();  //服务器的响应数据
		    //echo $json_str;die;


			$data = json_decode($json_str, true);
			$token = $data['access_token'];

			//保存到redis中时间为3600

			Redis::set($key, $token);
			Redis::expire($key, 1000);
		}


		return $token;

	}

	//上传素材
	public function guzzle2(){
		$access_token = $this->getAccessToken();
		$type = 'image';
		$url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$access_token.'&type='.$type;
		//使用guzzle发起get请求
		$client = new Client(); //实例化 客户端
		$response = $client->request('POST',$url,[
			'verify'=>false,
			'multipart'=>[
				[
					'name'=>'media',
					'contents' => fopen('5.jpg','r') //上传文件路径
				],
			]
		]); //发起请求并接收响应
		$data = $response->getBody();
		echo $data;
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
					$content = "欢迎您的关注";
					$this->Text($data,$content);
				}

			}
			switch($data->MsgType){
				case "text":
					//把天气截取出来，后面是天气的地址
					$tq = str_replace("天气:","",$data->Content);
					$key = "2f3d1615c28f0a5bc54da5082c4c1c0c";
				break;
			}


		}



	}

	//回复文本消息
	public  function  Text($data,$content){
		//回复用户消息(纯文本格式)
		$toUser = $data->FromUserName;
		$fromUser = $data->ToUserName;
		$msgType = 'text';
		//%s代表字符串(发送信息)
		$template = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";
		$info = sprintf($template, $toUser, $fromUser, time(), $msgType, $content);
		echo $info;
	}




}

