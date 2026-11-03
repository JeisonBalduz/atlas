<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ViewsFamiliarModel extends Model
{
    // Indicamos que apunta a tu vista de PostgreSQL
    protected $table = 'vw_familiares';

    // Si tu vista tiene una llave primaria específica (ej: id_empleado o id)
    protected $primaryKey = 'empleado_id'; 

    // Las vistas no son autoincrementales ni tienen timestamps de Laravel
    public $incrementing = false;
    public $timestamps = false;
}