<?php

declare(strict_types=1);

namespace App\commands;

use flight\commands\AbstractBaseCommand;

class MakeModelCommand extends AbstractBaseCommand {

    public function __construct($name = 'make:model') {
        parent::__construct(
            \is_array($name) ? 'make:model' : $name, 
            'Genera un modelo Eloquent individual', 
            []
        );

        $this->argument('modelo', 'Nombre del modelo a crear (ej. Empleado)');
    }

    public function execute($modelo = null) {
        if (!$modelo) {
            echo "❌ Error: Debes proporcionar el nombre del modelo.\n";
            echo "👉 Ejemplo: php runway make:model Empleado\n";
            return;
        }

        $baseName = ucfirst(strtolower(trim((string)$modelo)));
        // Si el usuario escribe "Empleado" o "EmpleadoModel", normalizamos para que termine en Model
        if (!str_ends_with($baseName, 'Model')) {
            $modelName = $baseName . 'Model';
            $tableName = strtolower($baseName) . 's';
        } else {
            $modelName = $baseName;
            $tableName = strtolower(str_replace('Model', '', $baseName)) . 's';
        }

        $modelDir = __DIR__ . "/../Model";
        if (!is_dir($modelDir)) {
            mkdir($modelDir, 0755, true);
        }

        $modelFile = "{$modelDir}/{$modelName}.php";
        if (!file_exists($modelFile)) {
            $modelContent = "<?php\n\n" .
                            "namespace App\Model;\n\n" .
                            "use Illuminate\Database\Eloquent\Model;\n\n" .
                            "class {$modelName} extends Model\n" .
                            "{\n" .
                            "    protected \$table = '{$tableName}';\n\n" .
                            "    protected \$primaryKey = ''; \n\n" .
                            "    public \$incrementing = false;\n" .
                            "    public \$timestamps = false;\n" .
                            "}\n";
            
            file_put_contents($modelFile, $modelContent);
            echo "✔ Modelo Eloquent creado: app/Model/{$modelName}.php (Tabla: {$tableName})\n";
        } else {
            echo "⚠️ El modelo app/Model/{$modelName}.php ya existía.\n";
        }
    }
}