<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Keywords extends Model
{
    protected $table = 'keywords';
    public $primaryKey = "id";
    public $timestamps = false;
}
