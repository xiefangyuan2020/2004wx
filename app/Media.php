<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';
    public $primaryKey = "id";
    public $timestamps = false;
    //protected $guarded=[];
    //protected $fillable = ['time'];
}
