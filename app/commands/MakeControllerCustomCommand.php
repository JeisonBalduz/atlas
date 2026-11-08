<?php

declare(strict_types=1);

namespace App\Command;

use flight\commands\AbstractBaseCommand;

class MakeControllerCustomCommand extends AbstractBaseCommand {

    public function __construct($name = 'make:controller_custom') {
        parent::__construct(
            \is_array($name) ? 'make:controller_custom' : $name, 
            'Genera un controlador individual con tu estructura estándar', 
            []
        );

        $this->argument('controlador', 'Nombre del controlador a crear (ej. Empleado)');
    }

    public function execute($controlador = null) {
        if (!$controlador) {
            echo "❌ Error: Debes proporcionar el nombre del controlador.\n";
            echo "👉 Ejemplo: php runway make:controller_custom Empleado\n";
            return;
        }

        $baseName = ucfirst(strtolower(trim((string)$controlador)));
        $controllerName = str_ends_with($baseName, 'Controller') ? $baseName : $baseName . 'Controller';
        $lowerName = strtolower(str_replace('Controller', '', $baseName));

        $controllerDir = __DIR__ . "/../Controller";
        if (!is_dir($controllerDir)) {
            mkdir($controllerDir, 0755, true);
        }

        $controllerFile = "{$controllerDir}/{$controllerName}.php";
        if (!file_exists($controllerFile)) {
            $controllerContent = "<?php\n\n" .
                                "declare(strict_types=1);\n\n" .
                                "namespace App\Controller;\n\n" .
                                "use App\Utils\Config;\n" .
                                "use flight\Engine;\n\n" .
                                "class {$controllerName}\n" .
                                "{\n" .
                                "    private Engine \$app;\n\n" .
                                "    private Config \$config;\n\n" .
                                "    public function __construct(Engine \$app, Config \$config)\n" .
                                "    {\n" .
                                "        \$this->app = \$app;\n" .
                                "        \$this->config = \$config;\n" .
                                "    }\n\n" .
                                "    public function render(): void\n" .
                                "    {\n" .
                                "        // Lógica del controlador {$baseName}\n" .
                                "        \$this->app->render('{$lowerName}');\n" .
                                "    }\n" .
                                "}\n";
            
            file_put_contents($controllerFile, $controllerContent);
            echo "✔ Controlador creado: app/Controller/{$controllerName}.php\n";
        } else {
            echo "⚠️ El controlador app/Controller/{$controllerName}.php ya existía.\n";
        }
    }
}