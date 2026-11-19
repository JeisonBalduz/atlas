<?php

declare(strict_types=1);

namespace App\commands;

use flight\commands\AbstractBaseCommand;

class MakeModelCommand extends AbstractBaseCommand
{
    public function __construct($name = 'make:model')
    {
        parent::__construct(
            \is_array($name) ? 'make:model' : $name,
            'Genera un modelo Eloquent individual',
            []
        );

        $this->argument('modelo', 'Nombre del modelo a crear (ej. UsuarioPedro)');
    }

    public function execute($modelo = null)
    {
        if (!$modelo) {
            echo "❌ Error: Debes proporcionar el nombre del modelo.\n";
            echo "👉 Ejemplo: php runway make:model UsuarioPedro\n";
            return;
        }

        $rawName = trim((string) $modelo);
        // Quita "Model" solo si está al final, respetando mayúsculas internas (ej. UsuarioPedro)
        $rawWithoutSuffix = preg_replace('/Model$/', '', $rawName) ?? $rawName;
        $baseName = ucfirst($rawWithoutSuffix);

        $modelName = $baseName . 'Model';

        // Convierte UsuarioPedro -> usuario_pedro y le agrega 's' para el nombre de tabla por defecto
        $snakeName = strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1_$2', $baseName));
        $tableName = $snakeName . 's';

        $modelDir = __DIR__ . "/../Model";
        if (!is_dir($modelDir)) {
            mkdir($modelDir, 0755, true);
        }

        $modelFile = "{$modelDir}/{$modelName}.php";
        if (file_exists($modelFile)) {
            echo "⚠️ El modelo app/Model/{$modelName}.php ya existía.\n";
            return;
        }

        $modelContent = <<<PHP
        <?php

        declare(strict_types=1);

        namespace App\Model;

        use Illuminate\Database\Eloquent\Model;

        class {$modelName} extends Model
        {
            protected \$table = '{$tableName}';

            protected \$primaryKey = '';

            public \$incrementing = true;

            protected \$keyType = 'int';

            public \$timestamps = false;

            protected \$fillable = [
                
            ];

            public static function todoDatos(): array
            {
                return static::query()
                    ->whereNotNull()
                    ->pluck('', '')
                    ->toArray();
            }
        }
        PHP;

        file_put_contents($modelFile, $modelContent);
        echo "✔ Modelo Eloquent creado: app/Model/{$modelName}.php (Tabla: {$tableName})\n";
    }
}
