<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Interfaces\RenderInterfaces;
use App\Services\ContextoSesion;
use App\Utils\Config;
use flight\Engine;

/**
 * Controlador base con utilidades HTTP compartidas (renderizado BladeOne, respuestas JSON,
 * validación de identificadores y lectura de payloads de DataTables).
 */
abstract class BaseController implements RenderInterfaces
{
    /**
     * @param Engine<object> $app
     */
    public function __construct(
        protected Engine $app,
        protected Config $config,
    ) {
    }

    /**
     * Renderiza una plantilla BladeOne inyectando automáticamente `env`, `debug` y `usuario`.
     *
     * @param array<string, mixed> $datos
     */
    protected function renderVista(string $vista, array $datos = []): void
    {
        $this->app->render($vista, array_merge([
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) (ContextoSesion::todo() ?? []),
        ], $datos));
    }

    /**
     * Envía una respuesta JSON exitosa estándar.
     */
    protected function jsonOk(mixed $data = null, string $message = '', int $status = 200): void
    {
        $payload = ['ok' => true];
        if ($message !== '') {
            $payload['message'] = $message;
        }
        if ($data !== null) {
            $payload['data'] = $data;
        }

        $this->app->json($payload, $status);
    }

    /**
     * Envía una respuesta JSON de error estándar.
     */
    protected function jsonError(string $message, int $status = 400): void
    {
        $this->app->json([
            'ok' => false,
            'message' => $message,
        ], $status);
    }

    /**
     * Valida que el parámetro de ruta sea un entero positivo; si no, responde 400 JSON.
     */
    protected function idPositivo(string $id, string $entidad = 'registro'): ?int
    {
        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->jsonError("Identificador de {$entidad} inválido.", 400);
            return null;
        }

        return (int) $id;
    }

    /**
     * Extrae la entrada de DataTables usando LimpiarInputs con respaldo GET/POST del Engine.
     *
     * @return array<string, mixed>
     */
    protected function entradaDataTable(): array
    {
        $entrada = (new LimpiarInputs())->todo();
        if ($entrada !== []) {
            return $entrada;
        }

        $request = $this->app->request();

        return $request->method === 'GET'
            ? $request->query->getData()
            : $request->data->getData();
    }
}
