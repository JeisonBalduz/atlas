<?php

declare(strict_types=1);

namespace App\commands;

use flight\commands\AbstractBaseCommand;

class MakeMiddlewareCommand extends AbstractBaseCommand
{
    public function __construct($name = 'make:middleware')
    {
        parent::__construct(
            \is_array($name) ? 'make:middleware' : $name,
            'Genera un archivo de Middleware individual en app/Middleware/',
            []
        );

        $this->argument('middleware', 'Nombre del middleware a crear (ej. Auth, authRol)');
    }

    public function execute($middleware = null)
    {
        if (!$middleware) {
            echo "❌ Error: Debes proporcionar el nombre del middleware.\n";
            echo "👉 Ejemplo: php runway make:middleware Auth\n";
            return;
        }

        $rawName = trim((string) $middleware);
        // Quita "Middleware" solo si va al final, conservando mayúsculas internas
        $rawWithoutSuffix = preg_replace('/Middleware$/', '', $rawName) ?? $rawName;
        $baseName = ucfirst($rawWithoutSuffix);
        $middlewareName = $baseName . 'Middleware';

        $middlewareDir = __DIR__ . "/../Middleware";
        if (!is_dir($middlewareDir)) {
            mkdir($middlewareDir, 0755, true);
        }

        $middlewareFile = "{$middlewareDir}/{$middlewareName}.php";
        if (!file_exists($middlewareFile)) {
            $middlewareContent = <<<PHP
            <?php

            declare(strict_types=1);

            namespace App\Middleware;

            use flight\Engine;

            class {$middlewareName}
            {
                public function before(array \$params): void
                {
                    // Lógica que se ejecuta antes de la ruta
                }

                public function after(array \$params): void
                {
                    // Lógica que se ejecuta después de la ruta
                }
            }
            PHP;

            file_put_contents($middlewareFile, $middlewareContent);
            echo "✔ Middleware creado exitosamente: app/Middleware/{$middlewareName}.php\n";
        } else {
            echo "⚠️ El middleware app/Middleware/{$middlewareName}.php ya existía.\n";
        }
    }
}