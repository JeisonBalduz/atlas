<?php

declare(strict_types=1);

namespace App\commands;

use flight\commands\AbstractBaseCommand;

class MakeRouteCommand extends AbstractBaseCommand {

    public function __construct($name = 'make:route') {
        parent::__construct(
            \is_array($name) ? 'make:route' : $name, 
            'Añade la ruta GET al web.php validando que el controlador exista', 
            []
        );

        $this->argument('modulo', 'Nombre del módulo para la ruta (ej. login, empleado)');
    }

    public function execute($modulo = null) {
        if (!$modulo) {
            echo "❌ Error: Debes proporcionar el nombre del módulo.\n";
            echo "👉 Ejemplo: php runway make:route login\n";
            return;
        }

        // Normalizar nombres (ej. "login" -> "Login")
        $baseName = ucfirst(strtolower(trim((string)$modulo)));
        $lowerName = strtolower($baseName); // ej. login
        $controllerName = $baseName . 'Controller'; // ej. LoginController

        // 1. Verificar si el archivo físico del controlador existe antes de hacer nada
        $controllerFile = __DIR__ . "/../Controller/{$controllerName}.php";
        
        if (!file_exists($controllerFile)) {
            echo "❌ Error: No existe un controlador llamado '{$controllerName}' en app/Controller/.\n";
            echo "👉 Sugerencia: Crea primero el controlador o el CRUD antes de registrar la ruta.\n";
            return; // Detiene la ejecución por completo
        }

        // 2. Ruta hacia tu archivo web.php existente
        $webFile = __DIR__ . "/../../app/router/web.php"; 

        if (!file_exists($webFile)) {
            echo "❌ Error: No se encontró el archivo web.php en: {$webFile}\n";
            return;
        }

        $content = file_get_contents($webFile);

        // 3. Añadir el use del controlador si no existe
        $useStatement = "use App\\Controller\\{$controllerName};";
        if (!str_contains($content, $useStatement)) {
            $lastUsePos = strrpos($content, "use ");
            if ($lastUsePos !== false) {
                $endOfLine = strpos($content, ";", $lastUsePos);
                $content = substr_replace($content, "\n" . $useStatement, $endOfLine + 1, 0);
            } else {
                $content = str_replace("<?php", "<?php\n\n{$useStatement}", $content);
            }
        }

        // 4. Añadir la línea del router al final del archivo
        $newRoute = "\n// Ruta para el módulo {$baseName}\n\$router->get('/{$lowerName}', [{$controllerName}::class, 'render']);\n";

        if (!str_contains($content, "/{$lowerName}")) {
            $content .= $newRoute;
            file_put_contents($webFile, $content);
            echo "✔ ¡Controlador '{$controllerName}' verificado con éxito!\n";
            echo "✔ Ruta GET añadida a web.php: \$router->get('/{$lowerName}', [{$controllerName}::class, 'render']);\n";
        } else {
            echo "⚠️ La ruta para '/{$lowerName}' ya existía en web.php.\n";
        }
    }
}