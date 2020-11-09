<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;

class TestController extends Controller
{
   // public function test1(){
   // 		//echo __METHOD__;

   // 		// $list = DB::table('admin')->limit(3)->get()->toArray();
   // 		// dd($list);

   // 		$key = 'wx2004';
   // 		Redis::set($key,time());
   // 		echo Redis::get($key);

   // }

   // //测试2
   // public function test2(){
   // 	echo md5(rand());
   // }

  //  public function token(){
  //     $echostr=request()->get('echostr','');
  //     if($this->checkSignature() && !empty($echostr)){
  //        echo $echostr;
  //     }
  //  }

  // private function checkSignature()
  // {
  //     $signature = $_GET["signature"];
  //     $timestamp = $_GET["timestamp"];
  //     $nonce = $_GET["nonce"];
     
  //     $token = "Token";
  //     $tmpArr = array($token, $timestamp, $nonce);
  //     sort($tmpArr, SORT_STRING);
  //     $tmpStr = implode( $tmpArr );
  //     $tmpStr = sha1( $tmpStr );
      
  //     if( $tmpStr == $signature ){
  //         return true;
  //     }else{
  //         return false;
  //     }

  // }



  // public function test3(){
  //   print_r($_GET);
  // }

  // public function test4(){
  //   //print_r($_POST);

  // }

  public function guzzle1(){


    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".env('WX_APPID')."&secret=".env('WX_APPSEC');
      //echo $url;

    //使用guzzle发起get请求
    $client = new Client(); //实例化 客户端

    $response = $client->request('GET',$url,['verify'=>false]); //发起请求并接收响应

    $json_str = $response->getBody();  //服务器的响应数据
    echo $json_str;


  }

  public function guzzle2(){
    $access_token = "";
    $type = 'image';
    $url = 'https https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$access_token.'&type='.$type;
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

}