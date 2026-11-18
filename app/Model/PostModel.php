<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PostModel extends Model
{
    protected $table = 'cargo';

    protected $primaryKey = ''; 

    public $incrementing = false;
    public $timestamps = false;
}
