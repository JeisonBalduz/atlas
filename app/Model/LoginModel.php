<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LoginModel extends Model
{
    protected $table = 'logins';

    protected $primaryKey = ''; 

    public $incrementing = false;
    public $timestamps = false;
}
