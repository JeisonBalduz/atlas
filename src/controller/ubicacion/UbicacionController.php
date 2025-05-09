<?php

namespace App\Atlas\controller;

use App\Atlas\controller\auditoria\AuditoriaController;
use App\Atlas\config\App;
use App\Atlas\models\UbicacionModel;

class UbicacionController
{
    private $auditoriaController;
    private $app;
    private $idUsuario;
    private $nombreUsuario;
    private $ubicacionModel;

    public function __construct()
    {
        $this->app = new App();
        $this->auditoriaController = new AuditoriaController();
        $this->ubicacionModel = new UbicacionModel();
        $this->app->iniciarSession();
        $this->idUsuario = $_SESSION['id'];
        $this->nombreUsuario = $_SESSION['usuario'];
    }

    public function obtenerEstados()
    {
        $data_json = [
            'mensaje' => '',
            'exito' => false
        ];

        $obtenerEstados = $this->ubicacionModel->estados();
        if ($obtenerEstados) {
            foreach ($obtenerEstados as $row) {
                $data_json['exito'] = true;
                $data_json['data'][] = [
                    'id' => $row['id_estado'],
                    'value' => $row['estado']
                ];
                $data_json['mensaje'] = 'Estados obtenidos';
            }
        } else {
            $data_json['exito'] = false;
            $data_json['mensaje'] = 'Estados no obtenidos';
        }

        // Devolver la respuesta en formato JSON
        header('Content-Type: application/json');
        echo json_encode($data_json);
    }

    public function obtenerMunicipio(string $idEstado)
    {
        $data_json = [
            'mensaje' => '',
            'exito' => false
        ];

        $obtenerMunicipio = $this->ubicacionModel->municipio([$idEstado]);
        if ($obtenerMunicipio) {
            foreach ($obtenerMunicipio as $row) {
                $data_json['exito'] = true;
                $data_json['data'][] = [
                    'id' => $row['id_municipio'],
                    'value' => $row['municipio']
                ];
                $data_json['mensaje'] = 'municipio obtenidos';
            }
        } else {
            $data_json['exito'] = false;
            $data_json['mensaje'] = 'Municipios no obtenidos';
        }

        header('Content-Type: application/json');
        echo json_encode($data_json);
    }

    public function obtenerParroquia(string $idMunicipio)
    {
        $data_json = [
            'mensaje' => '',
            'exito' => false
        ];

        $obtenerMunicipio = $this->ubicacionModel->parroquia([$idMunicipio]);
        if ($obtenerMunicipio) {
            foreach ($obtenerMunicipio as $row) {
                $data_json['exito'] = true;
                $data_json['data'][] = [
                    'id' => $row['id_parroquia'],
                    'value' => $row['parroquia']
                ];
                $data_json['mensaje'] = 'parroquia obtenidos';
            }
        } else {
            $data_json['exito'] = false;
            $data_json['mensaje'] = 'Parroquia no obtenidos';
        }
        // Devolver la respuesta en formato JSON
        header('Content-Type: application/json');
        echo json_encode($data_json);
    }
}
