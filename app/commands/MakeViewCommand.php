<?php

declare(strict_types=1);

namespace App\Command;

use flight\commands\AbstractBaseCommand;

class MakeViewCommand extends AbstractBaseCommand
{

    public function __construct($name = 'make:view')
    {
        parent::__construct(
            \is_array($name) ? 'make:view' : $name,
            'Genera una vista Blade individual',
            []
        );

        $this->argument('vista', 'Nombre de la vista a crear (ej. empleado)');
    }

    public function execute($vista = null)
    {
        if (!$vista) {
            echo "❌ Error: Debes proporcionar el nombre de la vista.\n";
            echo "👉 Ejemplo: php runway make:view empleado\n";
            return;
        }

        $lowerName = strtolower(trim((string) $vista));
        $baseName = ucfirst($lowerName);

        $viewDir = __DIR__ . "/../../resources/views";
        if (!is_dir($viewDir)) {
            mkdir($viewDir, 0755, true);
        }

        $viewFile = "{$viewDir}/{$lowerName}.blade.php";
        if (!file_exists($viewFile)) {
            $viewContent = "@extends('layouts.app')\n\n" .
                "@section('title', '$lowerName')\n\n" .
                "@push('styles') @endpush\n\n".
                "@section('content')\n" .
                "<div>\n" .
                "    <h1 class=\"text-2xl font-bold text-slate-800 mb-4\">Vista de {$baseName}s</h1>\n" .
                "    <p class=\"text-slate-600\">Vista generada para el sistema Atlas.</p>\n" .
                "</div>\n" .
                "@endsection\n\n".
                "@push('scripts') @endpush";

            file_put_contents($viewFile, $viewContent);
            echo "✔ Vista creada: resources/views/{$lowerName}.blade.php\n";
        } else {
            echo "⚠️ La vista resources/views/{$lowerName}.blade.php ya existía.\n";
        }
    }
}