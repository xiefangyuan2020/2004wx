<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

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
	    	Redis::expire($key,5);
    	}

    	

    	echo "access_token:".$token;

    }

}
