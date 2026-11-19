<?php

declare(strict_types=1);

namespace App\commands;

use flight\commands\AbstractBaseCommand;

class MakeControllerCustomCommand extends AbstractBaseCommand
{
    public function __construct($name = 'make:controller_custom')
    {
        parent::__construct(
            \is_array($name) ? 'make:controller_custom' : $name,
            'Genera un controlador individual con tu estructura estándar',
            []
        );

        $this->argument('controlador', 'Nombre del controlador a crear (ej. empleadoEstatus)');
    }

    public function execute($controlador = null)
    {
        if (!$controlador) {
            echo "❌ Error: Debes proporcionar el nombre del controlador.\n";
            echo "👉 Ejemplo: php runway make:controller_custom empleadoEstatus\n";
            return;
        }

        $rawName = trim((string) $controlador);
        $rawWithoutSuffix = preg_replace('/Controller$/i', '', $rawName) ?? $rawName;
        $baseName = ucfirst($rawWithoutSuffix); // Preserva mayúsculas internas (ej. empleadoEstatus -> EmpleadoEstatus)
        $controllerName = $baseName . 'Controller';
        $modelName = $baseName . 'Model';
        $snakeName = strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1_$2', $baseName));

        $controllerDir = __DIR__ . "/../Controller";
        if (!is_dir($controllerDir)) {
            mkdir($controllerDir, 0755, true);
        }

        $controllerFile = "{$controllerDir}/{$controllerName}.php";
        if (file_exists($controllerFile)) {
            echo "⚠️ El controlador app/Controller/{$controllerName}.php ya existía.\n";
            return;
        }

        $controllerContent = <<<PHP
        <?php

        declare(strict_types=1);

        namespace App\Controller;

        use App\Utils\Config;
        use App\Utils\ContextoSesion;
        use App\Model\\{$modelName};
        use flight\Engine;

        class {$controllerName}
        {
            private Engine \$app;

            private Config \$config;

            public function __construct(Engine \$app, Config \$config)
            {
                \$this->app = \$app;
                \$this->config = \$config;
            }

            /**
             * [GET] Muestra el listado completo de recursos.
             * Trae los registros desde el modelo y renderiza la vista principal con datos de contexto.
             */
            public function render(): void
            {
                \$this->app->render('{$snakeName}', [
                    'env' => \$this->config->env(),
                    'debug' => \$this->config->isDebug(),
                    'usuario' => (object) (ContextoSesion::todo() ?? []),
                    'registros' => {$modelName}::todoDatos(),
                ]);
            }

            /**
             * [GET] Muestra el formulario para crear un recurso nuevo.
             * Prepara catálogos/opciones necesarias y renderiza la vista de creación.
             */
            public function create(): void
            {
                \$this->app->render('{$snakeName}.create', [
                    'env' => \$this->config->env(),
                    'debug' => \$this->config->isDebug(),
                    'usuario' => (object) (ContextoSesion::todo() ?? []),
                ]);
            }

            /**
             * Normaliza, limpia o valida datos antes de persistir (crear o actualizar).
             */
            private function beforeStorage(array \$data): array
            {
                return \$data;
            }

            /**
             * [POST] Recibe los datos del formulario y los almacena en la base de datos.
             * Lee el payload, pasa por beforeStorage(), guarda en {$modelName} y responde/redirige.
             */
            public function store(): void
            {
                \$payload = \$this->beforeStorage(\$this->app->request()->data->getData());

                // TODO: Validar y guardar registro en {$modelName}
            }

            /**
             * [GET] Muestra los detalles de un recurso específico.
             * Busca el registro por ID/clave y renderiza su vista de detalle o devuelve respuesta.
             */
            public function show(int|string \$id): void
            {
                // TODO: Consultar registro por \$id en {$modelName} y renderizar vista de detalle
            }

            /**
             * [GET] Muestra el formulario para editar un recurso existente.
             * Carga el registro por ID (y catálogos si aplica) y renderiza la vista de edición.
             */
            public function edit(int|string \$id): void
            {
                // TODO: Consultar registro por \$id en {$modelName} y renderizar vista de edición
            }

            /**
             * [PUT / PATCH] Recibe los cambios y actualiza el recurso en la base de datos.
             * Busca el registro, procesa los datos con beforeStorage(), actualiza y responde/redirige.
             */
            public function update(int|string \$id): void
            {
                \$payload = \$this->beforeStorage(\$this->app->request()->data->getData());

                // TODO: Validar y actualizar registro \$id en {$modelName}
            }

            /**
             * [DELETE] Elimina un recurso de la base de datos.
             * Localiza el registro por ID, aplica borrado/baja lógica según corresponda y responde.
             */
            public function destroy(int|string \$id): void
            {
                // TODO: Eliminar o desactivar registro \$id en {$modelName}
            }
        }
        PHP;

        file_put_contents($controllerFile, $controllerContent);
        echo "✔ Controlador creado: app/Controller/{$controllerName}.php\n";
    }
}
