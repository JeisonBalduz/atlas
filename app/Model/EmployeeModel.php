<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EmployeeModel extends Model
{
    protected $table = 'employees';

    protected $primaryKey = ''; 

    public $incrementing = false;
    public $timestamps = false;
}
