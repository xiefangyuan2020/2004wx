<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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


	//回复消息
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
					 $array = ['欢迎您的关注','茶花小铺欢迎您','有什么帮助您的吗?'];
                    $content = $array[array_rand($array)];
					echo $this->Text($data,$content);
				}

				//自定义菜单栏
				if(strtolower($data->Event=='CLICK')){
					$eventKey = $data->EventKey;
					switch ($eventKey) {
						case 'V1001_TODAY_MUSIC':
							$array = ['鹦鹉','水瓶','绿色'];
							$content = $array[array_rand($array)];
							$this->Text($data,$content);
							break;
						case 'V1001_GOOD':
							$count = Cache::add('good',1)?:Cache::increment('goods');
							$content = '点赞人数:'.$count;
							$this->Text($data,$content);
						default:
							break;
					}
				}

			}


			switch($data->MsgType){
				case "text":
					//把天气截取出来，后面是天气的地址
					$tq = urlencode(str_replace("天气:","",$data->Content));
					//echo $this->Text($data,$tq);
					$key = "2f3d1615c28f0a5bc54da5082c4c1c0c";
					$url = "http://apis.juhe.cn/simpleWeather/query?city=".$tq."&key=".$key;
					$cr = file_get_contents($url);
					$jm = json_decode($cr,true);
					if($jm["error_code"]==0){
						//走到这儿说明成功
						$content = "";
						$content .= $jm["result"]["city"]."当前天气"."\n";//查询城市
						$dtian = $jm["result"]["realtime"];
						$content .= "温度:".$dtian["temperature"]."\n";
						$content .= "湿度:".$dtian["humidity"]."\n";
						$content .= "天气情况:".$dtian["info"]."\n";
						$content .= "风向:".$dtian["direct"]."\n";
						$content .= "风力:".$dtian["power"]."\n";
						$content .="以下是未来天气状况:"."\n";
						$aa = $jm["result"]["future"];
							foreach($aa as $k=>$v){
								$content .= date("Y-m-d",strtotime($v["date"])).":";
								$content .= $v["temperature"].",";
								$content .= $v["weather"].",";
								$content .= $v["direct"]."\n";
							}
						echo $this->Text($data,$content);
					}elseif($data->Event=='text'){
						$msg = $data->Content;
						switch ($msg) {
							case '在吗':
								$content = "您好!有什么帮助您的吗";
								$this->Text($data,$content);
								break;
							case '在':
								$content = "您好!有什么帮助您的吗";
								$this->Text($data,$content);
								break;
							case '红包':
								$content = "想的挺美,天上有掉馅饼的好事么ლ(′◉❥◉｀ლ)";
								$this->Text($data,$content);
								break;
							default:
								$content = "欢迎您!";
								$this->Text($data,$content);
								break;
						}
					}

				break;
			}
		}
	}


	//自定义菜单栏
	public function createMenu(){
		$menu = '{
			"button":[
			    {	
			        "type":"click",
			        "name":"今日歌曲",
			        "key":"V1001_TODAY_MUSIC"
			    },
			    {
			        "name":"菜单",
			        "sub_button":[
			    {	
			        "type":"view",
			        "name":"搜索",
			        "url":"http://www.soso.com/"
			    },
			    {
			        "type":"click",
			        "name":"赞一下我们",
			        "key":"V1001_GOOD"
			    }]
		    }]
		}';
		$access_token = $this->getAccessToken();
		$url = " https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
		$res = $this->curl($url,$menu);
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
		$info = sprintf($template,$toUser, $fromUser, time(), $msgType, $content);
		echo $info;
	}


	public function curl($url,$menu){
        //1.初始化
        $ch = curl_init();
        //2.设置
        curl_setopt($ch,CURLOPT_URL,$url);//设置提交地址
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);//设置返回值返回字符串
        curl_setopt($ch,CURLOPT_POST,1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS,$menu);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        //3.执行
        $output = curl_exec($ch);
        //4.关闭
        curl_close($ch);
        return $output;
    }


}

