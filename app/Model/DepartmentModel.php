<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class DepartmentModel extends Model
{
    protected $table = 'departamento';

    protected $primaryKey = ''; 

    public $incrementing = false;
    public $timestamps = false;
}
