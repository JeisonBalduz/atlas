@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Catálogos del Sistema') {{-- TÍTULO DE LA PÁGINA --}}

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/v/dt/dt-3.1.5/datatables.min.css">
    <script src="https://cdn.datatables.net/v/dt/dt-3.1.5/datatables.min.js"></script>
@endpush

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 pb-12 pt-6 bg-slate-50') {{-- CSS DE LA ETIQUETA MAIN --}}

@section('menu_lateral'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.menu_lateral'){{-- CARGAR menu_lateral  --}} 
@endsection

@section('header'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.header'){{-- CARGAR HEADER  --}} 
@endsection

@section('contenido') {{-- CARGAR CONTENIDO PRINCIPAL --}}
<div class="mx-auto max-w-7xl grid grid-cols-1 md:grid-cols-12 gap-6">

    {{-- Cabecera del gestor de catálogos --}}
    <header class="col-span-full flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900">Gestión de Catálogos</h1>
            <p class="mt-1 text-sm text-zinc-500">Administración centralizada de estatus, dependencias, departamentos, cargos, niveles académicos y carreras.</p>
        </div>

        <button
            type="button"
            id="btn-nuevo-catalogo"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] cursor-pointer"
        >
            <i data-lucide="plus" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
            <span>Nuevo Registro</span>
        </button>
    </header>

    {{-- Navegación por pestañas para los 6 catálogos --}}
    <nav aria-label="Selector de catálogos" class="col-span-full rounded-2xl border border-slate-200 bg-white p-2 shadow-[0_15px_30px_rgba(0,0,0,0.05)]">
        <div id="pestanas-catalogo" role="tablist" class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
            <button type="button" role="tab" data-catalogo="estatus" aria-selected="true"
                class="pestana-catalogo rounded-xl bg-marca px-3 py-2.5 text-sm font-medium text-white transition-all focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Estatus
            </button>
            <button type="button" role="tab" data-catalogo="dependencias" aria-selected="false"
                class="pestana-catalogo rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:bg-slate-50 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Dependencias
            </button>
            <button type="button" role="tab" data-catalogo="departamentos" aria-selected="false"
                class="pestana-catalogo rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:bg-slate-50 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Departamentos
            </button>
            <button type="button" role="tab" data-catalogo="cargo" aria-selected="false"
                class="pestana-catalogo rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:bg-slate-50 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Cargos
            </button>
            <button type="button" role="tab" data-catalogo="nivel_academicos" aria-selected="false"
                class="pestana-catalogo rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:bg-slate-50 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Nivel Académico
            </button>
            <button type="button" role="tab" data-catalogo="carrera" aria-selected="false"
                class="pestana-catalogo rounded-xl bg-white px-3 py-2.5 text-sm font-medium text-zinc-500 transition-all hover:bg-slate-50 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                Carreras
            </button>
        </div>
    </nav>

    {{-- Tabla dinámica del catálogo activo (DataTables 3.1.5) --}}
    <section
        id="contenedor-tabla-catalogo"
        data-estados="{{ json_encode($estados ?? [], JSON_UNESCAPED_UNICODE) }}"
        aria-labelledby="titulo-tabla-catalogo"
        class="col-span-full rounded-2xl border border-slate-200 bg-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] overflow-hidden"
    >
        <header class="border-b border-slate-200 px-6 py-4">
            <h2 id="titulo-tabla-catalogo" class="text-base font-semibold text-zinc-900">Catálogo de Estatus</h2>
            <p class="text-xs text-zinc-500">Los encabezados y el formulario se adaptan automáticamente al catálogo seleccionado.</p>
        </header>

        <div id="envoltura-tabla-catalogo" class="overflow-x-auto px-5 py-4">
            <table id="tabla-catalogo" class="w-full text-sm text-left">
                <thead class="text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-slate-50"></thead>
                <tbody class="divide-y divide-slate-100 text-zinc-700"></tbody>
            </table>
        </div>
    </section>
</div>

{{-- Modal dinámico para crear o editar registro del catálogo activo --}}
@component('components.modal', [
    'id' => 'modal-formulario-catalogo',
    'titulo' => 'Nuevo registro',
    'subtitulo' => 'Completa los campos del catálogo seleccionado.',
    'tamano' => 'md',
    'textoCancelar' => 'Cancelar',
    'textoConfirmar' => 'Guardar cambios',
    'varianteConfirmar' => 'marca',
])
@endcomponent

{{-- Modal de confirmación para eliminar/cambiar estado --}}
@component('components.modal', [
    'id' => 'modal-eliminar-catalogo',
    'titulo' => 'Eliminar registro de catálogo',
    'subtitulo' => 'Confirma la operación sobre el registro del catálogo.',
    'tamano' => 'sm',
    'textoCancelar' => 'Cancelar',
    'textoConfirmar' => 'Eliminar',
    'varianteConfirmar' => 'red',
])
@endcomponent
@endsection

@push('scripts') {{-- CARGAR SCRIPTS DE JS --}}
    <script type="module" src="/js/catalogo.js"></script>
@endpush
