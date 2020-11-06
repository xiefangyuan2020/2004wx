<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Illuminate\Support\Facades\Redis;

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


}