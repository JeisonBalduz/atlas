<?php

namespace App\Atlas\ajax;

require_once '../../vendor/autoload.php';

use App\Atlas\config\Conexion;
use App\Atlas\controller\userController;
use App\Atlas\controller\loginController;
use App\Atlas\controller\notificacionController;

$conexion = new Conexion();
$usercontroller = new userController();
$userLogin = new userController();
$notificacion = new notificacionController();
$id = isset($_POST['id']) ? $conexion->limpiarCadena($_POST['id']) : "";

switch ($_GET['modulo_usuario']) {
    case 'login':
        $user = $userLogin->limpiarCadena($_POST['usuario']);
        $password = $userLogin->limpiarCadena($_POST['password']);
        $userLogin->logearse($user, $password);
        break;

    case 'redireccionar':
        $url = $_POST['url'];
        echo $userLogin->redireccionarUsuario($url);
        break;

    case 'cerrarSession':
        $url = $_POST['url'];
        echo $userLogin->cerrarSession_total($url);
        break;
    case 'DatosUsuariosBasicos':
        $usercontroller->DatosUsuariosBasicos();
        break;

    case 'DatosUsuarios':
        $usercontroller->datosUsuarios();
        break;

    case 'desactivarUsuario':
        $usercontroller->desactivarUsuario($id);
        break;

    default:
        # code...
        break;
}
