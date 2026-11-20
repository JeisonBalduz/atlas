@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Registros de Familiares') {{-- TÍTULO DE LA PÁGINA --}}

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 pb-12 pt-6 bg-slate-50') {{-- CSS DE LA ETIQUETA MAIN --}}

@section('menu_lateral'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.menu_lateral'){{-- CARGAR menu_lateral  --}} 
@endsection

@section('header'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.header'){{-- CARGAR HEADER  --}} 
@endsection

@section('contenido') {{-- CARGAR CONTENIDO PRINCIPAL --}}
<div class="mx-auto max-w-7xl grid grid-cols-1 md:grid-cols-12 gap-6">

    {{-- Cabecera del módulo de carga familiar --}}
    <header class="col-span-full flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900">Registros de Familiares</h1>
            <p class="mt-1 text-sm text-zinc-500">Carga familiar asociada a los trabajadores activos del instituto.</p>
        </div>

        <a
            href="/registrar_familiar"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98]"
        >
            <i data-lucide="users" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
            <span>Registrar Familiar</span>
        </a>
    </header>

    {{-- Tabla de familiares registrados --}}
    <div class="col-span-full">
        @component('components.tabla', [
            'id' => 'tabla-familiares',
            'titulo' => 'Carga Familiar Registrada',
            'subtitulo' => 'Incluye vínculo con el titular, parentesco y datos de acta civil (tomo y folio).',
            'placeholderBuscador' => 'Buscar por titular, familiar o parentesco...',
            'columnas' => [
                ['clave' => 'cedula_titular', 'etiqueta' => 'Cédula Titular', 'ordenable' => true],
                ['clave' => 'empleado_titular', 'etiqueta' => 'Empleado Titular', 'ordenable' => true],
                ['clave' => 'cedula_familiar', 'etiqueta' => 'Cédula Familiar', 'ordenable' => true],
                ['clave' => 'nombre_familiar', 'etiqueta' => 'Nombre del Familiar', 'ordenable' => true],
                ['clave' => 'parentesco', 'etiqueta' => 'Parentesco', 'ordenable' => true],
                ['clave' => 'edad', 'etiqueta' => 'Edad', 'ordenable' => true, 'alineacion' => 'center'],
                ['clave' => 'genero', 'etiqueta' => 'Género', 'ordenable' => true, 'alineacion' => 'center'],
                ['clave' => 'tomo_folio', 'etiqueta' => 'Tomo / Folio', 'ordenable' => false, 'alineacion' => 'center'],
                ['clave' => 'acciones', 'etiqueta' => 'Acciones', 'ordenable' => false, 'alineacion' => 'right'],
            ],
        ])
            {{-- Filtro por parentesco --}}
            <select
                id="filtro-parentesco"
                aria-label="Filtrar por parentesco"
                class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-zinc-900 transition-all focus:border-marca-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
            >
                <option value="todos">Todos los parentescos</option>
                <option value="Hijo(a)">Hijo(a)</option>
                <option value="Cónyuge">Cónyuge</option>
                <option value="Madre">Madre</option>
                <option value="Padre">Padre</option>
            </select>
        @endcomponent
    </div>
</div>

{{-- Modal de detalle del familiar y acta --}}
@component('components.modal', [
    'id' => 'modal-detalle-familiar',
    'titulo' => 'Detalle del familiar',
    'subtitulo' => 'Datos personales y registro de acta.',
    'tamano' => 'lg',
    'textoCancelar' => 'Cerrar',
])
@endcomponent

{{-- Modal de confirmación para eliminar familiar --}}
@component('components.modal', [
    'id' => 'modal-eliminar-familiar',
    'titulo' => 'Eliminar carga familiar',
    'subtitulo' => 'Esta acción retirará al familiar del listado de prueba.',
    'tamano' => 'sm',
    'textoCancelar' => 'Cancelar',
    'textoConfirmar' => 'Eliminar registro',
    'varianteConfirmar' => 'red',
])
@endcomponent
@endsection

@push('scripts') {{-- CARGAR SCRIPTS DE JS --}}
    <script type="module" src="/js/registros_familiares.js"></script>
@endpush
