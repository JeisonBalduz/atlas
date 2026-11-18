<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class StateModel extends Model
{
    protected $table = 'estados';

    protected $primaryKey = ''; 

    public $incrementing = false;
    public $timestamps = false;
}
