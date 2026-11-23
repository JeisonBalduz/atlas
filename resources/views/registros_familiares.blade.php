@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Registros de Familiares') {{-- TÍTULO DE LA PÁGINA --}}

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

    {{-- Card 1 (Bento Hero Oscura): Cabecera del módulo de carga familiar --}}
    <header class="col-span-full lg:col-span-6 rounded-3xl border border-zinc-900 bg-zinc-950 p-6 sm:p-7 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between gap-5">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-300">
                <i data-lucide="heart-handshake" class="h-3.5 w-3.5" aria-hidden="true"></i>
                <span>Filiación y Carga Familiar</span>
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Registros de Familiares</h1>
            <p class="text-sm text-zinc-400">Control centralizado de carga familiar, parentescos y datos de actas civiles asociados al personal del instituto.</p>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-4">
            <a
                href="/empleado/registros"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-medium text-white transition-all hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-marca-400 focus:ring-offset-2 focus:ring-offset-zinc-950"
            >
                <i data-lucide="arrow-left" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
                <span>Directorio de Empleados</span>
            </a>

            <a
                href="/empleado/registrar/familiar"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-500 focus:outline-none focus:ring-2 focus:ring-marca-400 focus:ring-offset-2 focus:ring-offset-zinc-950 active:scale-[0.98]"
            >
                <i data-lucide="user-plus" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
                <span>Registrar Familiar</span>
            </a>
        </div>
    </header>

    {{-- Card 2 (Bento Marca): Vínculos de parentesco --}}
    <article class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl border border-marca bg-marca p-6 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-marca-200">Tipos de parentesco</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-marca-100">
                <i data-lucide="network" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-4xl sm:text-5xl font-bold tracking-tight text-white">{{ count($parentescos ?? []) }}</p>
            <p class="mt-2 text-xs font-medium text-marca-100">Categorías de filiación habilitadas</p>
        </div>
    </article>

    {{-- Card 3 (Bento Blanca con Acento): Verificación de Actas --}}
    <article class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Registro Civil</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-50 border border-sky-200 text-sky-800">
                <i data-lucide="book-open-check" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-xl sm:text-2xl font-bold tracking-tight text-zinc-900">Tomo y Folio</p>
            <p class="mt-2 text-xs font-medium text-zinc-500">Trazabilidad por acta civil y titular responsable</p>
        </div>
    </article>

    {{-- Card 4 (Bento 12 Columnas): Tabla de familiares registrados (DataTables 3.1.5 Server-Side) --}}
    @component('components.contenedor_tabla', [
        'idTitulo' => 'titulo-carga-familiar',
        'icono' => 'users',
        'titulo' => 'Carga Familiar Registrada',
        'subtitulo' => 'Incluye vínculo con el titular, parentesco, cálculo automático de edad y datos de acta civil.',
    ])
        @slot('filtros')
            <div class="w-full sm:w-56">
                @component('components.select', [
                    'id' => 'filtro-parentesco',
                    'name' => 'filtro_parentesco',
                    'placeholder' => 'Todos los parentescos',
                    'selected' => 'todos',
                    'search' => true,
                    'options' => ['todos' => 'Todos los parentescos'] + ($parentescos ?? []),
                ])
                @endcomponent
            </div>
        @endslot

        <table id="tabla-familiares" class="w-full text-sm text-left">
            <thead class="text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-slate-50">
                <tr>
                    <th>Cédula Titular</th>
                    <th>Empleado Titular</th>
                    <th>Cédula Familiar</th>
                    <th>Nombre del Familiar</th>
                    <th>Parentesco</th>
                    <th class="text-center">Edad</th>
                    <th class="text-center">Género</th>
                    <th class="text-center">Tomo / Folio</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-zinc-700"></tbody>
        </table>
    @endcomponent
</div>

{{-- Modal de detalle del familiar y acta --}}
@component('components.modal_detalle', [
    'id' => 'modal-detalle-familiar',
    'titulo' => 'Detalle del familiar',
    'subtitulo' => 'Datos personales y registro de acta.',
])
@endcomponent

{{-- Modal de confirmación para eliminar familiar --}}
@component('components.modal_confirmacion', [
    'id' => 'modal-eliminar-familiar',
    'titulo' => 'Eliminar carga familiar',
    'subtitulo' => 'Esta acción eliminará el vínculo familiar del trabajador.',
    'textoConfirmar' => 'Eliminar registro',
])
@endcomponent
@endsection

@push('scripts') {{-- CARGAR SCRIPTS DE JS --}}
    <script type="module" src="/js/registros_familiares.js"></script>
@endpush
