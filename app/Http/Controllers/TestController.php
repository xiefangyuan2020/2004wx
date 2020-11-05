<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
   public function test1(){
   		//echo __METHOD__;

   		// $list = DB::table('admin')->limit(3)->get()->toArray();
   		// dd($list);

   		$key = 'wx2004';
   		Redis::set($key,time());
   		echo Redis::get($key);

   }

   //测试2
   public function test2(){
   	echo __METHOD__;
   }

}
