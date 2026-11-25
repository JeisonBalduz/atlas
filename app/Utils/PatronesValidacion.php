<?php

declare(strict_types=1);

namespace App\Utils;

final class PatronesValidacion
{
    public const SOLO_TEXTO = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u';
    public const TEXTO_NUMEROS = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9\s]+$/u';
    public const DIRECCION_BASICA = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9\s\/]+$/u';
    public const REFERENCIA = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9\s\/#.,-]+$/u';
}