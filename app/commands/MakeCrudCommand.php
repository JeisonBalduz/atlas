<?php

declare(strict_types=1);

namespace App\Command;

use flight\commands\AbstractBaseCommand;

class MakeCrudCommand extends AbstractBaseCommand {

    public function __construct($name = 'make:crud') {
        parent::__construct(
            \is_array($name) ? 'make:crud' : $name, 
            'Genera un CRUD completo (Modelo Eloquent, Controlador y Vista)', 
            []
        );

        $this->argument('modulo', 'Nombre del módulo a crear (ej. empleado)');
    }

    public function execute($modulo = null) {
        if (!$modulo) {
            echo "❌ Error: Debes proporcionar el nombre del módulo.\n";
            echo "👉 Ejemplo: php runway make:crud empleado\n";
            return;
        }

        // Normalizar nombres (ej. "empleado" -> "Empleado")
        $baseName = ucfirst(strtolower(trim((string)$modulo)));
        $lowerName = strtolower($baseName); // ej. empleado
        
        $controllerName = $baseName . 'Controller'; 
        $modelName = $baseName . 'Model';           // Ej. EmpleadoModel
        $tableName = $lowerName . 's';              // Ej. empleados

        echo "🚀 Generando estructura completa para el módulo: {$baseName}...\n\n";

        // 1. Crear el Modelo Eloquent en app/Model/
        $modelDir = __DIR__ . "/../Model";
        if (!is_dir($modelDir)) {
            mkdir($modelDir, 0755, true);
        }

        $modelFile = "{$modelDir}/{$modelName}.php";
        if (!file_exists($modelFile)) {
            $modelContent = "<?php\n\n" .
                            "declare(strict_types=1);\n\n" .
                            "namespace App\Model;\n\n" .
                            "use Illuminate\Database\Eloquent\Model;\n\n" .
                            "class {$modelName} extends Model\n" .
                            "{\n" .
                            "    protected \$table = '';\n\n" .
                            "    protected \$primaryKey = ''; \n\n" .
                            "    public \$incrementing = false;\n" .
                            "    public \$timestamps = false;\n" .
                            "}\n";
            
            file_put_contents($modelFile, $modelContent);
            echo "✔ Modelo Eloquent creado: app/Model/{$modelName}.php\n";
        } else {
            echo "⚠️ El modelo app/Model/{$modelName}.php ya existía.\n";
        }

        // 2. Crear el Controlador en app/Controller/ con tu estructura exacta
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
                                "        // Lógica del módulo {$baseName}\n" .
                                "        \$this->app->render('{$lowerName}');\n" .
                                "    }\n" .
                                "}\n";
            
            file_put_contents($controllerFile, $controllerContent);
            echo "✔ Controlador creado: app/Controller/{$controllerName}.php\n";
        } else {
            echo "⚠️ El controlador app/Controller/{$controllerName}.php ya existía.\n";
        }

        // 3. Crear la vista .blade.php directamente en resources/views/
        $viewDir = __DIR__ . "/../../resources/views";
        if (!is_dir($viewDir)) {
            mkdir($viewDir, 0755, true);
        }

        $viewFile = "{$viewDir}/{$lowerName}.blade.php";
        if (!file_exists($viewFile)) {
            $viewContent = "@extends('layouts.app')\n\n" .
                           "@section('content')\n" .
                           "<div class=\"max-w-4xl mx-auto bg-white p-6 rounded-xl border border-slate-200 shadow-sm\">\n" .
                           "    <h1 class=\"text-2xl font-bold text-slate-800 mb-4\">Listado de {$baseName}s</h1>\n" .
                           "    <p class=\"text-slate-600\">Módulo generado automáticamente para el sistema Atlas.</p>\n" .
                           "</div>\n" .
                           "@endsection\n";
            
            file_put_contents($viewFile, $viewContent);
            echo "✔ Vista creada: resources/views/{$lowerName}.blade.php\n";
        } else {
            echo "⚠️ La vista resources/views/{$lowerName}.blade.php ya existía.\n";
        }

        echo "\n✨ ¡Listo! Módulo {$baseName} generado correctamente con Eloquent y tu estructura estándar.\n";
    }
}