<?php

namespace App\Atlas\models;

use App\Atlas\config\EjecutarSQL;

class DepartamentoModel extends EjecutarSQL
{
    // Metodos de la Clase Privada
    protected function datosDepartamento()
    {
        $sql = $this->ejecutarConsulta("SELECT * FROM departamento");
        return $sql;
    }

    protected function obtenerDepartamentoGeneral(){
        $sql = $this->ejecutarConsulta("SELECT * FROM departamento depa WHERE depa.activo = 1");
        return $sql;
    }

    protected function actulizarDepartamento($tabla, $datos, $condicion)
    {
        $sql = $this->actualizarDatos($tabla, $datos, $condicion);
        return $sql;
    }

    protected function registrarDepartamento($tabla, $datos)
    {
        $sql = $this->guardarDatos($tabla, $datos);
        return $sql;
    }

    protected function verificarDepartamento($tabla, $departamento)
    {
        $parametro = [$departamento];
        $sql = $this->ejecutarConsulta("SELECT * FROM $tabla WHERE departamento = ?", $parametro);

        return $sql;
    }

    protected function obtenerDatosDepartamento($parametro)
    {
        $sql = $this->ejecutarConsulta("SELECT * FROM departamento WHERE id_departamento = ?", $parametro);

        return $sql;
    }
    // Getters Para Accder a los Metodos de la Clase
    public function getDatosDepartamento()
    {
        return $this->datosDepartamento();
    }

    public function getActulizarDepartamento($tabla, $datos, $condicion)
    {
        return $this->actulizarDepartamento($tabla, $datos, $condicion);
    }

    public function getRegistrarDepartamento($tabla, $datos)
    {
        return $this->registrarDepartamento($tabla, $datos);
    }

    public function getValidarDepartamento($tabla, $departamento)
    {
        return $this->verificarDepartamento($tabla, $departamento);
    }

    public function getObtenerDatosDepartamento($parametro)
    {
        return $this->obtenerDatosDepartamento($parametro);
    }

    public function getObtenerDepartamentoGeneral(){
        return $this->obtenerDepartamentoGeneral();
    }
}
