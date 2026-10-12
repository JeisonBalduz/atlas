<?php

namespace App\Atlas\Interfaces;

interface RecursoInterfaces{

    public static function obtenerRuta(string $indice): ? string;

    public static function cargarRuta (string $ruta): ? string;


}