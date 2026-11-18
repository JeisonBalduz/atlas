<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StatusModel extends Model
{
    protected $table = 'estatus';

    protected $primaryKey = ''; 

    public $incrementing = false;
    public $timestamps = false;
}
