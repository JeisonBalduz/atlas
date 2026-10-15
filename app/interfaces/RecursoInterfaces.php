<?php

namespace App\Atlas\interfaces;

interface RecursoInterfaces{

    public static function obtenerRuta(string $indice): ? string;

    public static function cargarRuta (string $ruta): ? string;


}