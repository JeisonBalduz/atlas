<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utils\Config;
use App\Services\ContextoSesion;
use App\Model\Catalogos\EstatusModel;
use flight\Engine;

class EstatusController
{
    private Engine $app;

    private Config $config;

    public function __construct(Engine $app, Config $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    /**
     * [GET] Muestra el listado completo de recursos.
     * Trae los registros desde el modelo y renderiza la vista principal con datos de contexto.
     */
    public function render(): void
    {
        $this->app->render('estatus', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) (ContextoSesion::todo() ?? []),
            'registros' => EstatusModel::todoDatos(),
        ]);
    }


    /**
     * Normaliza, limpia o valida datos antes de persistir (crear o actualizar).
     */
    private function beforeStorage(array $data): array
    {
        return $data;
    }

    /**
     * [POST] Recibe los datos del formulario y los almacena en la base de datos.
     * Lee el payload, pasa por beforeStorage(), guarda en EstatusModel y responde/redirige.
     */
    public function store(): void
    {
        $payload = $this->beforeStorage($this->app->request()->data->getData());

        // TODO: Validar y guardar registro en EstatusModel
    }

    /**
     * [GET] Muestra los detalles de un recurso específico.
     * Busca el registro por ID/clave y renderiza su vista de detalle o devuelve respuesta.
     */
    public function show(int|string $id): void
    {
        // TODO: Consultar registro por $id en EstatusModel y renderizar vista de detalle
    }

    /**
     * [GET] Muestra el formulario para editar un recurso existente.
     * Carga el registro por ID (y catálogos si aplica) y renderiza la vista de edición.
     */
    public function edit(int|string $id): void
    {
        // TODO: Consultar registro por $id en EstatusModel y renderizar vista de edición
    }

    /**
     * [PUT / PATCH] Recibe los cambios y actualiza el recurso en la base de datos.
     * Busca el registro, procesa los datos con beforeStorage(), actualiza y responde/redirige.
     */
    public function update(int|string $id): void
    {
        $payload = $this->beforeStorage($this->app->request()->data->getData());

        // TODO: Validar y actualizar registro $id en EstatusModel
    }

    /**
     * [DELETE] Elimina un recurso de la base de datos.
     * Localiza el registro por ID, aplica borrado/baja lógica según corresponda y responde.
     */
    public function destroy(int|string $id): void
    {
        // TODO: Eliminar o desactivar registro $id en EstatusModel
    }
}