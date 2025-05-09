<?php

namespace App\Atlas\controller;

use App\Atlas\models\AdministradorModel;
use App\Atlas\controller\auditoria\AuditoriaController;
use App\Atlas\models\PersonalModel;
use App\Atlas\config\App;
use App\Atlas\config\Conexion;
use App\Atlas\config\Server;
use App\Atlas\config\EjecutarSQL;

class AdministradorController extends AdministradorModel
{
    private $auditoriaController;
    private $app;
    private $personalModel;
    private $conexion;
    private $idUsuario;
    private $nombreUsuario;
    private $consultas;

    public function __construct()
    {
        parent::__construct();
        $this->app = new App();
        $this->personalModel = new PersonalModel();
        $this->auditoriaController = new AuditoriaController();
        $this->conexion = new Conexion();
        $this->consultas = new EjecutarSQL();
    }

    public function datosMasivosUsuario($nombreUser)
    {
        $this->app->iniciarSession();
        $this->idUsuario = $_SESSION['id'];
        $this->nombreUsuario = $_SESSION['usuario'];
        $nombreUser = $this->consultas->limpiarCadena($nombreUser);
        $data_json = [
            'exito' => false,
            'messenger' => 'No se pudo obtener los datos de la dependencia',
        ];

        if (empty($nombreUser)) {
            $sql = $this->totaldatosMasivosTodoUsuarios();
        } elseif (ctype_digit($nombreUser)) {
            // Si $nombreUser contiene solo números
            $parametros = [
                '1' => '%act%',
                '2' => '%regis%',
                '3' => '%descar%'
            ];

            if (array_key_exists($nombreUser, $parametros)) {
                $sql = $this->totaldatosMasivosTodoUsuariosTIPOEVENTO([$parametros[$nombreUser]]);
            } else {
                $sql = false;
            }
        } else {
            // Si $nombreUser contiene texto (letras o combinación de letras y números)
            $sql = $this->totaldatosMasivosUsuario([$nombreUser]); // Ejemplo de otra función
        }

        if ($sql) {
            $data_json['exito'] = true;
            $data_json['messenger'] = 'datos encontrados';
            $data_json['data'] = $sql;
        }

        // Devolver la respuesta en formato JSON
        header('Content-Type: application/json');
        echo json_encode($data_json);
    }

    public function descargarBaseDatos()
    {
        $this->app->iniciarSession();
        $this->idUsuario = $_SESSION['id'];
        $this->nombreUsuario = $_SESSION['usuario'];
        $data_json = [
            'exito' => false,
            'messenger' => 'NO SE PUEDE DESCARGAR LOS DATOS DE LA BVASE DE DATOS',
        ];

        $backupFile = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
        $command = "\"$mysqldumpPath\" --user=" . server::DB_USER . " --password=" . server::DB_PASS . " --host=" . server::DB_SERVER . " " . server::DB_NAME . " > " . $backupFile;

        // Para depuración: imprime el comando que se está ejecutando
        echo "Ejecutando comando: $command\n";

        exec($command . ' 2>&1', $output, $return_var);

        // Para depuración: imprime el resultado del comando
        echo "Resultado del comando: $return_var\n";
        echo "Salida del comando: " . implode("\n", $output) . "\n";

        if ($return_var === 0) {
            $registroAuditoria = $this->auditoriaController->registrarAuditoria($this->idUsuario, 'Descargar base de datos', 'El usuario ' . $this->nombreUsuario . ' ha descargo la base de datos');
            if ($registroAuditoria) {
                $data_json["exitoAuditoria"] = true;
                $data_json['messengerAuditoria'] = "Auditoria registrada con exito.";
            } else {
                $data_json["exitoAuditoria"] = false;
                $data_json['messengerAuditoria'] = "Error al registrar la auditoria.";
            }

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($backupFile));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($backupFile));
            readfile($backupFile);
            // No eliminar el archivo después de la descarga
            // unlink($backupFile);

            $data_json['exito'] = true;
            $data_json['messenger'] = 'Ya se logro descargar la base de datos';
        } else {
        }

        header('Content-Type: application/json');
        echo json_encode($data_json);
    }

    public function existeEmpleado(string $cedula)
    {
        $cedula = $this->consultas->limpiarCadena($cedula);
        $data_json = [
            'exito' => false,
            'mensaje' => 'No se pudo obtener los datos de la dependencia',
        ];

        if (empty($cedula)) {
            $data_json['mensaje'] = 'La cedula no puede estar vacia';
        } elseif (ctype_digit($cedula)) {
            $sql = $this->getExisteUsuario([$cedula], 'cedula');
            if ($sql) {
                $data_json['exito'] = true;
                $data_json['mensaje'] = 'Empleado con usuario encontrado';
                $data_json['existeEmpleado'] = $sql;
            }else{
                $data_json['mensaje'] = 'Este empleado no tiene un usuario asignado';
            }
        } else {
            $data_json['mensaje'] = 'La cedula no puede contener letras';
        }

        // Devolver la respuesta en formato JSON
        header('Content-Type: application/json');
        echo json_encode($data_json);
    }

    public function preguntasSeguridad(string $cedula)
    {
        $cedula = $this->consultas->limpiarCadena($cedula);
        $data_json = [
            'exito' => false,
            'mensaje' => 'No se pudo obtener los datos de la dependencia',
        ];

        if (empty($cedula)) {
            $data_json['mensaje'] = 'La cedula no puede estar vacia';
        } elseif (ctype_digit($cedula)) {
            $sql = $this->getPreguntasSeguridad([$cedula]);
            if ($sql) {
                $data_json['exito'] = true;
                $data_json['mensaje'] = 'Preguntas de seguridad encontradas';
                $data_json['preguntas'] = $sql;
            }else{
                $data_json['mensaje'] = 'Este empleado no tiene preguntas de seguridad asignadas';
            }
        } else {
            $data_json['mensaje'] = 'La cedula no puede contener letras';
        }

        // Devolver la respuesta en formato JSON
        header('Content-Type: application/json');
        echo json_encode($data_json);
    }
}
