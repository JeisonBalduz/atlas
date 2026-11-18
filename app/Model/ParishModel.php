<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ParishModel extends Model
{
    protected $table = 'parroquias';

    protected $primaryKey = ''; 

    public $incrementing = false;
    public $timestamps = false;
}
