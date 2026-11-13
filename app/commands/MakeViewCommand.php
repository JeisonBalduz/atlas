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
            // Estructura completa solicitada para Atlas
            $viewContent = "@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}\n\n" .
                           "@section('title', '{$baseName}') {{-- TÍTULO DE LA PÁGINA --}}\n\n" .
                           "@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 pb-12 pt-6 bg-slate-50') {{-- CSS DE LA ETIQUETA MAIN --}}\n\n" .
                           "@section('siderbar'){{-- CARGAR HTML EN ESTE APARTADO --}}\n" .
                           "    @include('layouts.siderbar'){{-- CARGAR SIDERBAR  --}} \n" .
                           "@endsection\n\n" .
                           "@section('header'){{-- CARGAR HTML EN ESTE APARTADO --}}\n" .
                           "    @include('layouts.header'){{-- CARGAR HEADER  --}} \n" .
                           "@endsection\n\n" .
                           "@section('content') {{-- CARGAR CONTENIDO PRINCIPAL  --}}\n" .
                           "<div class=\"max-w-4xl mx-auto bg-white p-6 rounded-xl border border-slate-200 shadow-sm\">\n" .
                           "    <h1 class=\"text-2xl font-bold text-slate-800 mb-4\">Módulo de {$baseName}</h1>\n" .
                           "    <p class=\"text-slate-600\">Vista generada automáticamente para el sistema Atlas.</p>\n" .
                           "</div>\n" .
                           "@endsection\n\n" .
                           "@push('scripts') {{-- CARGAR SCRIPTS DE JS  --}}\n" .
                           "@endpush\n";

            file_put_contents($viewFile, $viewContent);
            echo "✔ Vista creada exitosamente: resources/views/{$lowerName}.blade.php\n";
        } else {
            echo "⚠️ La vista resources/views/{$lowerName}.blade.php ya existía.\n";
        }
    }
}