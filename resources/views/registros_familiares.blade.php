@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Registros de Familiares') {{-- TÍTULO DE LA PÁGINA --}}

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

    {{-- Cabecera del módulo de carga familiar --}}
    <header class="col-span-full flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900">Registros de Familiares</h1>
            <p class="mt-1 text-sm text-zinc-500">Carga familiar asociada a los trabajadores activos del instituto.</p>
        </div>

        <a
            href="/empleado/registrar/familiar"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98]"
        >
            <i data-lucide="users" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
            <span>Registrar Familiar</span>
        </a>
    </header>

    {{-- Tabla de familiares registrados (DataTables 3.1.5 Server-Side) --}}
    <section aria-labelledby="titulo-carga-familiar" class="col-span-full rounded-2xl border border-slate-200 bg-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] overflow-hidden">
        <header class="flex flex-col gap-3 border-b border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 id="titulo-carga-familiar" class="text-base font-semibold text-zinc-900">Carga Familiar Registrada</h2>
                <p class="text-xs text-zinc-500">Incluye vínculo con el titular, parentesco y datos de acta civil (tomo y folio).</p>
            </div>

            <div class="flex items-center gap-2.5">
                <select
                    id="filtro-parentesco"
                    aria-label="Filtrar por parentesco"
                    class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-zinc-900 transition-all focus:border-marca-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
                >
                    <option value="todos">Todos los parentescos</option>
                    @foreach(($parentescos ?? []) as $idParentesco => $nombreParentesco)
                        <option value="{{ $idParentesco }}">{{ $nombreParentesco }}</option>
                    @endforeach
                </select>
            </div>
        </header>

        <div class="overflow-x-auto px-5 py-4">
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
        </div>
    </section>
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
    'subtitulo' => 'Esta acción eliminará el vínculo familiar del trabajador.',
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
