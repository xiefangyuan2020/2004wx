<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Log;
class WxController extends Controller
{
    //接入
    public function index(){
    	$signature = $_GET["signature"];
	    $timestamp = $_GET["timestamp"];
	    $nonce = $_GET["nonce"];
		
	    $token = env('WX_TOkEN');
	    $tmpArr = array($token, $timestamp, $nonce);
	    sort($tmpArr, SORT_STRING);
	    $tmpStr = implode( $tmpArr );
	    $tmpStr = sha1( $tmpStr );
	    
	    if( $tmpStr == $signature ){
	        echo $_GET['echostr'];
	    }else{
	        echo "111";
	    }
    }

    //获取access_token
    public function getAccessToken(){

    	$key = 'wx:access_token';

    	//检查是否有token
    	$token = Redis::get($key);
    	if($token){
    		echo "有缓存";echo '</br>';
    		echo $token;
    	}else{
    		echo "无缓存";
    		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WX_APPID')."&secret=".env('WX_APPSEC');
	    	//echo $url;die;
	    	$response = file_get_contents($url);
	    	//echo $response;
	    	$data = json_decode($response,true);
	    	$token = $data['access_token'];

	    	//保存到redis中时间为3600
	    	
	    	Redis::set($key,$token);
	    	Redis::expire($key,1000);
    	}

    	

    	echo "access_token:".$token;

    }


     public function wxEvent(){
    	$signature = $_GET["signature"];
	    $timestamp = $_GET["timestamp"];
	    $nonce = $_GET["nonce"];
		
	    $token = env('WX_TOkEN');
	    $tmpArr = array($token, $timestamp, $nonce);
	    sort($tmpArr, SORT_STRING);
	    $tmpStr = implode( $tmpArr );
	    $tmpStr = sha1( $tmpStr );
	    
	    if( $tmpStr == $signature ){  //验证通过
	    	//1、接收数据
	    	$xml_data = file_get_contents("php://input"); 

	    	//记录日志
	   	    Log::info($xml_data);
	    	$pos=simplexml_load_string($xml_data);
	    	if ($pos->MsgType=="event") {
	    		if ($pos->Event=='subscribe') {
	    		
	    			$Content="谢谢关注";
	    		 $info = $this->info($pos,$Content);	    			
	    		
	    		}	
	    	}    	
	    }
    }
    public function info($pos,$Content){
    	$ToUserName=$pos->FormUserName;
    	$FormUserName=$pos->ToUserName;
    	$CreateTime=time();
    	$MsgType="text";
	    $xml="<xml>
		    	<ToUserName><![CDATA[%s]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[%s]]></MsgType>
				<Content><![CDATA[".$Content."]]></Content>
			</xml>";
		$info=sprintf($xml,$ToUserName,$FormUserName,$CreateTime,$MsgType,$Content);
		file_put_contents($info);
		echo $info;
    }


}
