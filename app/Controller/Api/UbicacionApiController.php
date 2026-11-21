<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Services\CatalogosService;
use flight\Engine;

/**
 * Endpoints API para selects dependientes de ubicación geográfica (Estado -> Municipio -> Parroquia).
 */
final class UbicacionApiController
{
    /**
     * @param Engine<object> $app
     */
    public function __construct(
        private Engine $app,
        private CatalogosService $catalogos,
    ) {
    }

    /**
     * [GET] Lista los municipios pertenecientes a un estado.
     */
    public function municipios(string $estadoId): void
    {
        if (!ctype_digit($estadoId) || (int) $estadoId <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de estado inválido.'], 400);
            return;
        }

        $this->app->json([
            'ok' => true,
            'data' => $this->catalogos->municipiosPorEstado((int) $estadoId),
        ], 200);
    }

    /**
     * [GET] Lista las parroquias pertenecientes a un municipio.
     */
    public function parroquias(string $municipioId): void
    {
        if (!ctype_digit($municipioId) || (int) $municipioId <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de municipio inválido.'], 400);
            return;
        }

        $this->app->json([
            'ok' => true,
            'data' => $this->catalogos->parroquiasPorMunicipio((int) $municipioId),
        ], 200);
    }
}
