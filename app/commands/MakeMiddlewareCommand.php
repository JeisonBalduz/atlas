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

        $this->argument('middleware', 'Nombre del middleware a crear (ej. Auth, Role)');
    }

    public function execute($middleware = null)
    {
        if (!$middleware) {
            echo "❌ Error: Debes proporcionar el nombre del middleware.\n";
            echo "👉 Ejemplo: php runway make:middleware Auth\n";
            return;
        }

        // Normalizar nombres respetando mayúsculas iniciales y sufijo Middleware
        $baseName = ucfirst(trim((string) $middleware));
        $middlewareName = str_ends_with($baseName, 'Middleware') ? $baseName : $baseName . 'Middleware';

        $middlewareDir = __DIR__ . "/../Middleware";
        if (!is_dir($middlewareDir)) {
            mkdir($middlewareDir, 0755, true);
        }

        $middlewareFile = "{$middlewareDir}/{$middlewareName}.php";
        if (!file_exists($middlewareFile)) {
            $middlewareContent = "<?php\n\n" .
                "declare(strict_types=1);\n\n" .
                "namespace App\Middleware;\n\n" .
                "use flight\Engine;\n\n" .
                "class {$middlewareName}\n" .
                "{\n" .
                "    public function before(array \$params): void\n" .
                "    {\n" .
                "        // Lógica que se ejecuta antes de la ruta\n" .
                "    }\n\n" .
                "    public function after(array \$params): void\n" .
                "    {\n" .
                "        // Lógica que se ejecuta después de la ruta\n" .
                "    }\n" .
                "}\n";

            file_put_contents($middlewareFile, $middlewareContent);
            echo "✔ Middleware creado exitosamente: app/Middleware/{$middlewareName}.php\n";
        } else {
            echo "⚠️ El middleware app/Middleware/{$middlewareName}.php ya existía.\n";
        }
    }
}