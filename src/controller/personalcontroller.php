<?php

namespace App\Atlas\controller;

use App\Atlas\models\personalModel;
use App\Atlas\models\dependenciasModel;
use App\Atlas\models\estatusModel;
use App\Atlas\controller\archivo;

class personalController extends personalModel
{

    private $dependencia;
    private $estatus;
    private $archivo;

    // public function __construct(dependenciasModel $dependencia, estatusModel $estatus) {
    //     $this->dependencia = $dependencia;
    //     $this->estatus = $estatus;
    // }

    public function registro(
        $primerNombre,
        $segundoNombre,
        $primerApellido,
        $segundoApellido,
        $cedula,
        $civil,
        $correo,
        $ano,
        $mes,
        $dia,
        $idEstatus,
        $idCargo,
        $idDependencia,
        $idDepartamento,
        $telefono
    ) {
        // sleep(5);
        $primerNombre = $this->limpiarCadena($primerNombre);
        $segundoNombre = $this->limpiarCadena($segundoNombre);
        $primerApellido = $this->limpiarCadena($primerApellido);
        $segundoApellido = $this->limpiarCadena($segundoApellido);
        $cedula = $this->limpiarCadena($cedula);
        $civil = $this->limpiarCadena($civil);
        $correo = $this->limpiarCadena($correo);
        $ano = $this->limpiarCadena($ano);
        $mes = $this->limpiarCadena($mes);
        $dia = $this->limpiarCadena($dia);

        // DATOS DE EMPLEAODS
        $idEstatus = $this->limpiarCadena($idEstatus);
        $idCargo = $this->limpiarCadena($idCargo);
        $idDependencia = $this->limpiarCadena($idDependencia);
        $idDepartamento = $this->limpiarCadena($idDepartamento);
        $telefono = $this->limpiarCadena($telefono);

        $data_json = [
            'exito' => false, // Inicializamos a false por defecto
            'mensaje' => '',
        ];

        $extensionesPermitidas = ['pdf'];
        $archivoContrato = $_FILES['contratoArchivo'];
        $archivoNotacion = $_FILES['notacionAchivo'];
        $extensionContrato = strtolower(pathinfo($archivoContrato['name'], PATHINFO_EXTENSION));
        $extensionNotacion = strtolower(pathinfo($archivoNotacion['name'], PATHINFO_EXTENSION));
        if (!in_array($extensionContrato, $extensionesPermitidas)) {
            $data_json['error'] = true;
            $data_json['mensaje'] = "El archivo de contrato no es PDF";
            header('Content-Type: application/json');
        echo json_encode($data_json);
            exit;
        }

        if (!in_array($extensionNotacion, $extensionesPermitidas)) {
            $data_json['error'] = true;
            $data_json['mensaje'] = "El archivo de notación no es PDF";
            header('Content-Type: application/json');
        echo json_encode($data_json);
            exit;
        }

        $personal_datos_reg = [
            [
                "campo_nombre" => "primerNombre",
                "campo_marcador" => ":primerNombre",
                "campo_valor" => $primerNombre
            ],
            [
                "campo_nombre" => "segundoNombre",
                "campo_marcador" => ":segundoNombre",
                "campo_valor" => $segundoNombre
            ],
            [
                "campo_nombre" => "primerApellido",
                "campo_marcador" => ":primerApellido",
                "campo_valor" => $primerApellido
            ],
            [
                "campo_nombre" => "segundoApellido",
                "campo_marcador" => ":segundoApellido",
                "campo_valor" => $segundoApellido
            ],
            [
                "campo_nombre" => "cedula",
                "campo_marcador" => ":cedula",
                "campo_valor" => $cedula
            ],
            [
                "campo_nombre" => "estadoCivil",
                "campo_marcador" => ":estadoCivil",
                "campo_valor" => $civil
            ],
            [
                "campo_nombre" => "anoNacimiento",
                "campo_marcador" => ":anoNacimiento",
                "campo_valor" => $ano
            ],
            [
                "campo_nombre" => "mesNacimiento",
                "campo_marcador" => ":mesNacimiento",
                "campo_valor" => $mes
            ],
            [
                "campo_nombre" => "diaNacimiento",
                "campo_marcador" => ":diaNacimiento",
                "campo_valor" => $dia
            ],
            [
                "campo_nombre" => "fecha",
                "campo_marcador" => ":fecha",
                "campo_valor" => date("Y-m-d")
            ],
            [
                "campo_nombre" => "hora",
                "campo_marcador" => ":hora",
                "campo_valor" => date("H:i:s")
            ]
        ];

        $parametro = [$cedula];
        $check_personal = $this->getExistePersonal($parametro);
        if ($check_personal) {
            $data_json['exito'] = false;
            $data_json['personalEncontrado'] = true;
            $data_json['mensaje'] = 'personal encontrado';
            $data_json['cedula'] = $cedula;
        } else {
            $registrarPersonal = $this->getRegistrar("datosPersonales", $personal_datos_reg);
            if ($registrarPersonal == true) {
                $check_personal_exis = $this->getDatosPersonal($parametro);
                if ($check_personal_exis == true) {
                    foreach ($check_personal_exis as $row) {
                        $idPersonal = $row['id_personal'];
                        if ($idPersonal) {
                            $empleados_datos_reg = [
                                [
                                    "campo_nombre" => "idPersonal",
                                    "campo_marcador" => ":idPersonal",
                                    "campo_valor" =>  $idPersonal
                                ],
                                [
                                    "campo_nombre" => "idEstatus",
                                    "campo_marcador" => ":idEstatus",
                                    "campo_valor" => $idEstatus
                                ],
                                [
                                    "campo_nombre" => "idCargo",
                                    "campo_marcador" => ":idCargo",
                                    "campo_valor" => $idCargo
                                ],
                                [
                                    "campo_nombre" => "idDependencia",
                                    "campo_marcador" => ":idDependencia",
                                    "campo_valor" => $idDependencia
                                ],
                                [
                                    "campo_nombre" => "idDepartamento",
                                    "campo_marcador" => ":idDepartamento",
                                    "campo_valor" => $idDepartamento
                                ],
                                [
                                    "campo_nombre" => "telefono",
                                    "campo_marcador" => ":telefono",
                                    "campo_valor" => $telefono
                                ],
                                [
                                    "campo_nombre" => "fecha",
                                    "campo_marcador" => ":fecha",
                                    "campo_valor" => date("Y-m-d")
                                ],
                                [
                                    "campo_nombre" => "hora",
                                    "campo_marcador" => ":hora",
                                    "campo_valor" => date("H:i:s")
                                ]
                            ];
                            $parametro = [$idPersonal];
                            $check_empleado_exis = $this->getExisteEmpleado($parametro);
                            if ($check_empleado_exis) {
                                $data_json['exito'] = true;
                                $data_json['mensaje'] = "Este trabajador ya esta registrado";
                            } else {
                                $check_empleado = $this->getRegistrarEmpleado("datosempleados", $empleados_datos_reg);
                                if ($check_empleado == true) {
                                    $check_idEmpleado = $this->getExisteEmpleado($parametro);
                                    foreach ($check_idEmpleado as $row) {
                                        $idEmpleado = $row['id_empleados'];
                                        if ($idEmpleado) {
                                            // Iteramos sobre todos los elementos de $_FILES
                                            foreach ($_FILES as $input => $fileInfo) {
                                                $extension = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
                                                $datosArchivos = [
                                                    [
                                                        "campo_nombre" => "idPersonal",
                                                        "campo_marcador" => ":idPersonal",
                                                        "campo_valor" => $idEmpleado
                                                    ],
                                                    [
                                                        "campo_nombre" => "doc",
                                                        "campo_marcador" => ":doc",
                                                        "campo_valor" => $fileInfo['name']
                                                    ],
                                                    [
                                                        "campo_nombre" => "tipoDoc",
                                                        "campo_marcador" => ":tipoDoc",
                                                        "campo_valor" => $extension
                                                    ],
                                                    // [
                                                    //     "campo_nombre" => "codigoArc",
                                                    //     "campo_marcador" => ":codigoArc",
                                                    //     "campo_valor" => microtime(true)+1
                                                    // ]
                                                ];

                                                // Agregar el nuevo elemento al array principal
                                                $registroTotol = $this->getRegistrarDOCS("documentacion", $datosArchivos);
                                                if (!$registroTotol) {
                                                    $data_json['mensaje'] = "no se logro registras los docuemntos del trabjador";
                                                } else {
                                                    move_uploaded_file($archivoContrato['tmp_name'], '../global/archives/contratos/' . $archivoContrato['name']);
                                                    move_uploaded_file($archivoNotacion['tmp_name'], '../global/archives/notacion/' . $archivoNotacion['name']);
                                                    $data_json['exito'] = true;
                                                    $data_json['mensaje'] = "Empleado Registrado Exitosamente.";
                                                }
                                            }
                                        } else {
                                            $data_json['mensaje'] = "no se encontro el trabajador";
                                        }
                                    }
                                } else {
                                    $data_json['exito'] = true;
                                    $data_json['mensaje'] = "No se logro realizar la consulta";
                                }
                            }
                        } else {
                            $data_json['mensaje'] = "No se pudo recorrer el identificador";
                        }
                    }
                } else {
                    $data_json['mensaje'] = "Error al consultar los datos del personal previamente registrado";
                }
            } else {
                $data_json['mensaje'] = "La consulta no se logro realizar correctamente";
            }
        }
        header('Content-Type: application/json');
        echo json_encode($data_json);
    }

    public function obtenerDatosPersonal(string $cedula_familiar)
    {
        $cedula_familiar = $this->limpiarCadena($cedula_familiar);
        $data_json = [
            'exito' => false, // Inicializamos a false por defecto
            'mensaje' => 'data del principio',
        ];
        if ($cedula_familiar == "") {
            $data_json['exito'] = true;
            $data_json['mensaje'] = "Debes de llenar el campo de la Cédula para hacer la busqueda del empleado";
            $data_json['logrado'] = false;
        } else {
            $parametro = [$cedula_familiar];
            $check_personal = $this->getDatosPersonal($parametro);
            if ($check_personal == true) {
                foreach ($check_personal as $row) {
                    $data_json['exito'] = true;
                    $data_json['idPersonal'] = $row['id_personal'];
                    $data_json['cedula'] = $row['cedula'];
                    $data_json['nombre'] = $row['primerNombre'];
                    $data_json['apellido'] = $row['primerApellido'];
                    $data_json['mensaje'] = "Trabajador Encontrado";
                    $data_json['logrado'] = true;
                }
            } else {
                $data_json['exito'] = true;
                $data_json['mensaje'] = "Este trabajador no se encuentra registrado en nuestro sistema.";
                $data_json['logrado'] = false;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($data_json);
    }

    public function registrarFamilia($cedula_familiar, $primerNombre, $segundoNombre, $primerApellido, $segundoApellido, $cedula, $edad, $ano, $mes, $dia)
    {
        sleep(5);
        $cedula_familiar = $this->limpiarCadena($cedula_familiar);
        $primerNombre = $this->limpiarCadena($primerNombre);
        $segundoNombre = $this->limpiarCadena($segundoNombre);
        $primerApellido = $this->limpiarCadena($primerApellido);
        $segundoApellido = $this->limpiarCadena($segundoApellido);
        $cedula = $this->limpiarCadena($cedula);
        $edad = $this->limpiarCadena($edad);
        $ano = $this->limpiarCadena($ano);
        $mes = $this->limpiarCadena($mes);
        $dia = $this->limpiarCadena($dia);

        $data_json = [
            'exito' => true, // Inicializamos a false por defecto
            'mensaje' => 'si lelgo hasta aqui',
        ];

        // $check_familiar

        header('Content-Type: application/json');
        echo json_encode($data_json);
    }

    public function obtenerDependencias()
    {
        $dependencias = $this->getDependenciasPersonales();
        foreach ($dependencias as $row) {
            $data_json['exito'] = true;
            $data_json['data'][] = [
                'iddependencia' => $row['id_dependencia'],
                'dependencia' => $row['dependencia']
            ];
            $data_json['mensaje'] = "todas las dependencias exitoso";
        }
        header('Content-Type: application/json');
        echo json_encode($data_json);
    }

    public function obtenerEstatus()
    {
        $estatus = $this->getEstatusPersonales();
        foreach ($estatus as $row) {
            $data_json['exito'] = true;
            $data_json['data'][] = [
                'idestatus' => $row['id_estatus'],
                'estatus' => $row['estatus']
            ];
            $data_json['mensaje'] = "todas las estatus exitoso";
        }
        header('Content-Type: application/json');
        echo json_encode($data_json);
    }

    public function obtenerCargo()
    {
        $cargo = $this->getCargoPersonales();
        foreach ($cargo as $row) {
            $data_json['exito'] = true;
            $data_json['data'][] = [
                'idcargo' => $row['id_cargo'],
                'cargo' => $row['cargo']
            ];
            $data_json['mensaje'] = "todas las cargo exitoso";
        }
        header('Content-Type: application/json');
        echo json_encode($data_json);
    }

    public function obtenerDepartamento()
    {
        $departamento = $this->getDepartamentosPersonales();
        foreach ($departamento as $row) {
            $data_json['exito'] = true;
            $data_json['data'][] = [
                'iddepartamento' => $row['id_departamento'],
                'departamento' => $row['departamento']
            ];
            $data_json['mensaje'] = "todas las departamento exitoso";
        }
        header('Content-Type: application/json');
        echo json_encode($data_json);
    }

    public function objetoDependencia()
    {
        return $this->dependencia = new dependenciasModel();
    }

    public function objetoEstatus()
    {
        return $this->estatus = new estatusModel();
    }

    // public function objetoArchivo()
    // {
    //     return $this->archivo = new archivo();
    // }

    // public function getArchivoDatos()
    // {
    //     $datosArch = $this->objetoArchivo();
    //     return $datosArch->datosArchivos();
    // }

    protected function getEstatusPersonales()
    {
        $estatusPersonal = $this->objetoEstatus();
        return $estatusPersonal->getDatosEstatus();
    }

    protected function getCargoPersonales()
    {
        $cargoPersonal = $this->objetoEstatus();
        return $cargoPersonal->getDatosCargo();
    }
    protected function getDependenciasPersonales()
    {
        $dependenciaPersonal = $this->objetoDependencia();
        return $dependenciaPersonal->getDatosDependencia();
    }
    protected function getDepartamentosPersonales()
    {
        $departamentoPersonal = $this->objetoDependencia();
        return $departamentoPersonal->getDatosDepartamentos();
    }
}
