<?php

namespace App\Atlas\ajax;

require_once '../../vendor/autoload.php';

use App\Atlas\controller\totalDateController;
use App\Atlas\controller\notificacionController;

$notificacion = new notificacionController();
$totalDatos = new totalDateController();

switch ($_GET['modulo_Datos']) {
    case 'totalDatos':
        $totalDatos->totalDatosCard();
        break;

    case 'totalArchivosMes':
        $totalDatos->totalArchivosMes();
        break;

    case 'totalArchivosDia':
        $totalDatos->totalArchivosDia();
        break;


    default:
        # code...
        break;
}
