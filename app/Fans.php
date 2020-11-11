<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fans extends Model
{
     protected $table = 'fans';
    public $primaryKey = "id";
    public $timestamps = false;

    //白名单
    public  $fillable = ["openid","nickname","sex","headimgurl","country","province","city","status"];
}
