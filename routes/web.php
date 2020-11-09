<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

    return view('welcome');
});

Route::get('/info', function () {
	phpinfo();
});


Route::prefix('/wx')->group(function(){
	Route::match(['get','post'],'/wx','WxController@wxEvent');//微信接入
	Route::get('/token','WxController@getAccessToken');//获取access_token
	Route::get('/guzzle2','WxController@guzzle2'); //  /test/guzzle2
});

//test 路由分组
Route::prefix('/test')->group(function(){
	// Route::get('/test1','TestController@test1');//测试1
	// Route::get('/test2','TestController@test2');//测试2
	// Route::get('/test3','TestController@test3');//测试3
	// Route::post('/test4','TestController@test4');//测试4
	//Route::get('/Token','TestController@token');//token

	Route::get('/guzzle1','TestController@guzzle1'); //  /test/guzzle1
	Route::get('/guzzle2','TestController@guzzle2'); //  /test/guzzle2

});