@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Tabla Maestras del Sistema') {{-- TÍTULO DE LA PÁGINA --}}

@push('styles')
    @include('components.datatables_assets')
@endpush

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 xl:px-12 2xl:px-16 pb-12 pt-6 bg-slate-50') {{-- CSS DE LA ETIQUETA MAIN --}}

@section('menu_lateral'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.menu_lateral'){{-- CARGAR menu_lateral  --}} 
@endsection

@section('header'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.header'){{-- CARGAR HEADER  --}} 
@endsection

@section('contenido') {{-- CARGAR CONTENIDO PRINCIPAL --}}
<div class="w-full max-w-none mx-auto grid grid-cols-1 md:grid-cols-12 gap-6 atlas-vista-entrada">

    {{-- Card 1 (Bento Hero Oscura): Cabecera del gestor de Tabla Maestras --}}
    <header class="col-span-full xl:col-span-5 rounded-3xl border border-zinc-900 bg-zinc-950 p-6 sm:p-7 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between gap-5">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-300">
                <i data-lucide="layers" class="h-3.5 w-3.5" aria-hidden="true"></i>
                <span>Configuración Maestra</span>
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Gestión de Tabla Maestras</h1>
            <p class="text-sm text-zinc-400">Administración centralizada de estatus, dependencias, departamentos, cargos, niveles académicos, carreras y ubicación geográfica.</p>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-white/10 pt-4">
            <span class="text-xs font-medium text-zinc-400">9 Tabla Maestras institucionales conectados</span>
            <button
                type="button"
                id="btn-nuevo-catalogo"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-500 focus:outline-none focus:ring-2 focus:ring-marca-400 focus:ring-offset-2 focus:ring-offset-zinc-950 active:scale-[0.98] cursor-pointer"
            >
                <i data-lucide="plus" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
                <span>Nuevo Registro</span>
            </button>
        </div>
    </header>

    {{-- Card 2 (Bento Selector de Pestañas): 7 columnas --}}
    <nav aria-label="Selector de Tabla Maestras" class="col-span-full xl:col-span-7 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between gap-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Módulos de Tabla Maestra</p>
                <p class="text-sm font-medium text-zinc-900">Selecciona una categoría para consultar o editar sus registros</p>
            </div>
        </div>

        <div id="pestanas-catalogo" role="tablist" class="grid grid-cols-2 gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-2 sm:grid-cols-3">
            <button type="button" role="tab" data-catalogo="estatus" aria-selected="true"
                class="pestana-catalogo rounded-xl bg-marca px-3 py-2.5 text-sm font-medium text-white transition-all focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Estatus
            </button>
            <button type="button" role="tab" data-catalogo="dependencias" aria-selected="false"
                class="pestana-catalogo rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:bg-slate-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Dependencias
            </button>
            <button type="button" role="tab" data-catalogo="departamentos" aria-selected="false"
                class="pestana-catalogo rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:bg-slate-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Departamentos
            </button>
            <button type="button" role="tab" data-catalogo="cargo" aria-selected="false"
                class="pestana-catalogo rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:bg-slate-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Cargo
            </button>
            <button type="button" role="tab" data-catalogo="nivel_academicos" aria-selected="false"
                class="pestana-catalogo rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:bg-slate-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Nivel Académico
            </button>
            <button type="button" role="tab" data-catalogo="carrera" aria-selected="false"
                class="pestana-catalogo rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:bg-slate-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Carrera
            </button>
            <button type="button" role="tab" data-catalogo="estados" aria-selected="false"
                class="pestana-catalogo rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:bg-slate-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Estados
            </button>
            <button type="button" role="tab" data-catalogo="municipios" aria-selected="false"
                class="pestana-catalogo rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:bg-slate-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Municipios
            </button>
            <button type="button" role="tab" data-catalogo="parroquias" aria-selected="false"
                class="pestana-catalogo rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:bg-slate-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Parroquias
            </button>
        </div>
    </nav>

    {{-- Card 3 (Bento 12 Columnas): Tabla dinámica del Tabla Maestra activo (DataTables 3.1.5) --}}
    @component('components.contenedor_tabla', [
        'idSeccion' => 'contenedor-tabla-catalogo',
        'idTitulo' => 'titulo-tabla-catalogo',
        'idEnvoltura' => 'envoltura-tabla-catalogo',
        'icono' => 'database',
        'titulo' => 'Tabla Maestra de Estatus',
        'subtitulo' => 'Los encabezados y el formulario se adaptan automáticamente al Tabla Maestra seleccionado.',
        'atributosSeccion' => 'data-estados="' . htmlspecialchars(json_encode($estados ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') . '"',
    ])
        @slot('filtros')
            <div id="filtros-tabla-catalogo" class="flex flex-wrap items-center gap-2.5 hidden">
                <div id="envoltura-filtro-estado" class="w-full sm:w-52 hidden">
                    @component('components.select', [
                        'id' => 'filtro-catalogo-estado',
                        'name' => 'filtro_catalogo_estado',
                        'placeholder' => 'Todos los estados',
                        'selected' => 'todos',
                        'search' => true,
                        'options' => ['todos' => 'Todos los estados'] + ($estados ?? []),
                    ])
                    @endcomponent
                </div>

                <div id="envoltura-filtro-municipio" class="w-full sm:w-52 hidden">
                    @component('components.select', [
                        'id' => 'filtro-catalogo-municipio',
                        'name' => 'filtro_catalogo_municipio',
                        'placeholder' => 'Todos los municipios',
                        'selected' => 'todos',
                        'search' => true,
                        'options' => ['todos' => 'Todos los municipios'],
                    ])
                    @endcomponent
                </div>

                <div id="envoltura-filtro-activo" class="w-full sm:w-48 hidden">
                    @component('components.select', [
                        'id' => 'filtro-catalogo-activo',
                        'name' => 'filtro_catalogo_activo',
                        'placeholder' => 'Todos',
                        'selected' => 'todos',
                        'options' => [
                            'todos' => 'Todos',
                            '1' => 'Solo activos',
                            '0' => 'Solo inactivos',
                        ],
                    ])
                    @endcomponent
                </div>
            </div>
        @endslot

        <table id="tabla-catalogo" class="w-full text-sm text-left">
            <thead class="text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-slate-50"></thead>
            <tbody class="divide-y divide-slate-100 text-zinc-700"></tbody>
        </table>
    @endcomponent
</div>

{{-- Modal dinámico para crear o editar registro del Tabla Maestra activo --}}
@component('components.modal', [
    'id' => 'modal-formulario-catalogo',
    'titulo' => 'Nuevo registro',
    'subtitulo' => 'Completa los campos del Tabla Maestra seleccionado.',
    'tamano' => 'md',
    'textoCancelar' => 'Cancelar',
    'textoConfirmar' => 'Guardar cambios',
    'varianteConfirmar' => 'marca',
])
@endcomponent

{{-- Modal de confirmación para eliminar/cambiar estado --}}
@component('components.modal_confirmacion', [
    'id' => 'modal-eliminar-catalogo',
    'titulo' => 'Eliminar registro de Tabla Maestra',
    'subtitulo' => 'Confirma la operación sobre el registro del Tabla Maestra.',
    'textoConfirmar' => 'Eliminar',
])
@endcomponent
@endsection

@push('scripts') {{-- CARGAR SCRIPTS DE JS --}}
    <script type="module" src="/js/catalogo.js"></script>
@endpush
