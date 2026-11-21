@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Registros de Empleados') {{-- TÍTULO DE LA PÁGINA --}}

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

    {{-- Cabecera del módulo de empleados --}}
    <header class="col-span-full flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900">Registros de Empleados</h1>
            <p class="mt-1 text-sm text-zinc-500">Consulta, gestión de expedientes y estatus laboral del personal registrado.</p>
        </div>

        <a
            href="/empleado/registrar"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98]"
        >
            <i data-lucide="user-plus" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
            <span>Registrar Empleado</span>
        </a>
    </header>

    {{-- Tarjetas resumen (Bento Grid) --}}
    <section aria-label="Resumen de personal" class="col-span-full grid grid-cols-1 gap-4 sm:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)]">
            <p class="text-xs font-medium text-zinc-500">Total registrados</p>
            <p id="resumen-total-empleados" class="mt-2 text-3xl font-bold text-zinc-900">{{ $resumen['total'] ?? 0 }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)]">
            <p class="text-xs font-medium text-zinc-500">Personal activo</p>
            <p id="resumen-empleados-activos" class="mt-2 text-3xl font-bold text-green-800">{{ $resumen['activos'] ?? 0 }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)]">
            <p class="text-xs font-medium text-zinc-500">En reposo / inactivos</p>
            <p id="resumen-empleados-inactivos" class="mt-2 text-3xl font-bold text-yellow-900">{{ $resumen['inactivos'] ?? 0 }}</p>
        </article>
    </section>

    {{-- Tabla principal de empleados (DataTables 3.1.5 Server-Side) --}}
    <section aria-labelledby="titulo-directorio-personal" class="col-span-full rounded-2xl border border-slate-200 bg-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] overflow-hidden">
        <header class="flex flex-col gap-3 border-b border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 id="titulo-directorio-personal" class="text-base font-semibold text-zinc-900">Directorio de Personal</h2>
                <p class="text-xs text-zinc-500">Filtra por estatus laboral o estado en nómina; haz clic en Familiares para consultar su carga familiar.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <select
                    id="filtro-estatus-empleado"
                    aria-label="Filtrar por estatus laboral"
                    class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-zinc-900 transition-all focus:border-marca-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
                >
                    <option value="todos">Todos los estatus</option>
                    @foreach(($estatus_catalogo ?? []) as $idEstatus => $nombreEstatus)
                        <option value="{{ $idEstatus }}">{{ $nombreEstatus }}</option>
                    @endforeach
                </select>

                <select
                    id="filtro-activo-empleado"
                    aria-label="Filtrar por estado en nómina"
                    class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-zinc-900 transition-all focus:border-marca-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
                >
                    <option value="todos">Activos e inactivos</option>
                    <option value="1">Solo activos</option>
                    <option value="0">Solo inactivos</option>
                </select>
            </div>
        </header>

        <div class="overflow-x-auto px-5 py-4">
            <table id="tabla-empleados" class="w-full text-sm text-left">
                <thead class="text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-slate-50">
                    <tr>
                        <th>Código</th>
                        <th>Cédula</th>
                        <th>Nombre completo</th>
                        <th>Cargo</th>
                        <th>Departamento</th>
                        <th>Dependencia</th>
                        <th class="text-center">Estatus</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-zinc-700"></tbody>
            </table>
        </div>
    </section>
</div>

{{-- Modal de carga familiar asociada al trabajador --}}
@component('components.modal', [
    'id' => 'modal-familiares-empleado',
    'titulo' => 'Carga familiar del trabajador',
    'subtitulo' => 'Familiares registrados asociados al empleado titular.',
    'tamano' => 'xl',
    'textoCancelar' => 'Cerrar',
])
@endcomponent

{{-- Modal de ficha técnica resumida del empleado --}}
@component('components.modal', [
    'id' => 'modal-ficha-empleado',
    'titulo' => 'Ficha técnica del empleado',
    'subtitulo' => 'Información laboral y de contacto registrada.',
    'tamano' => 'lg',
    'textoCancelar' => 'Cerrar',
])
@endcomponent

{{-- Modal de confirmación para desactivar/eliminar empleado --}}
@component('components.modal', [
    'id' => 'modal-eliminar-empleado',
    'titulo' => 'Desactivar empleado',
    'subtitulo' => 'Esta acción actualizará el estatus actual del trabajador.',
    'tamano' => 'sm',
    'textoCancelar' => 'Cancelar',
    'textoConfirmar' => 'Confirmar baja',
    'varianteConfirmar' => 'red',
])
@endcomponent
@endsection

@push('scripts') {{-- CARGAR SCRIPTS DE JS --}}
    <script type="module" src="/js/registros_empleados.js"></script>
@endpush
