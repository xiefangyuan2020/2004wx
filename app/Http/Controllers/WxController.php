<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
