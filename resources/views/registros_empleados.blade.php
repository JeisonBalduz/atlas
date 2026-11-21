@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Registros de Empleados') {{-- TÍTULO DE LA PÁGINA --}}

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/v/dt/dt-3.1.5/datatables.min.css">
    <script src="https://cdn.datatables.net/v/dt/dt-3.1.5/datatables.min.js"></script>
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

    {{-- Card 1 (Bento Hero Oscura): Cabecera del módulo de empleados --}}
    <header class="col-span-full xl:col-span-5 rounded-3xl border border-zinc-900 bg-zinc-950 p-6 sm:p-7 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between gap-5">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-300">
                <i data-lucide="briefcase-business" class="h-3.5 w-3.5" aria-hidden="true"></i>
                <span>Gestión de Personal · INCES</span>
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Registros de Empleados</h1>
            <p class="text-sm text-zinc-400">Consulta general de expedientes, ubicación administrativa, carga familiar y estatus laboral del personal.</p>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-4">
            <a
                href="/empleado/registros/familiar"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-medium text-white transition-all hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-marca-400 focus:ring-offset-2 focus:ring-offset-zinc-950"
            >
                <i data-lucide="users" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
                <span>Ver Carga Familiar</span>
            </a>

            <a
                href="/empleado/registrar"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-500 focus:outline-none focus:ring-2 focus:ring-marca-400 focus:ring-offset-2 focus:ring-offset-zinc-950 active:scale-[0.98]"
            >
                <i data-lucide="user-plus" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
                <span>Registrar Empleado</span>
            </a>
        </div>
    </header>

    {{-- Card 2 (Bento Marca): Total registrados --}}
    <article class="col-span-full sm:col-span-4 xl:col-span-3 rounded-3xl border border-marca bg-marca p-6 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-marca-200">Total registrados</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-marca-100">
                <i data-lucide="users" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p id="resumen-total-empleados" class="text-4xl sm:text-5xl font-bold tracking-tight text-white">{{ $resumen['total'] ?? 0 }}</p>
            <p class="mt-2 text-xs font-medium text-marca-100">Expedientes en base de datos</p>
        </div>
    </article>

    {{-- Card 3 (Bento Blanca con Acento Verde): Personal activo --}}
    <article class="col-span-full sm:col-span-4 xl:col-span-2 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Personal activo</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-green-50 border border-green-200 text-green-800">
                <i data-lucide="user-check" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p id="resumen-empleados-activos" class="text-3xl sm:text-4xl font-bold tracking-tight text-green-800">{{ $resumen['activos'] ?? 0 }}</p>
            <p class="mt-2 text-xs font-medium text-zinc-500">Nómina activa (estatus 1)</p>
        </div>
    </article>

    {{-- Card 4 (Bento Blanca con Acento Ámbar): En reposo / inactivos --}}
    <article class="col-span-full sm:col-span-4 xl:col-span-2 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Inactivos / Baja</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-yellow-50 border border-yellow-200 text-yellow-900">
                <i data-lucide="user-minus" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p id="resumen-empleados-inactivos" class="text-3xl sm:text-4xl font-bold tracking-tight text-yellow-900">{{ $resumen['inactivos'] ?? 0 }}</p>
            <p class="mt-2 text-xs font-medium text-zinc-500">Egresados o desactivados</p>
        </div>
    </article>

    {{-- Card 5 (Bento 12 Columnas): Tabla principal de empleados (DataTables 3.1.5 Server-Side) --}}
    <section aria-labelledby="titulo-directorio-personal" class="col-span-full rounded-3xl border border-slate-200 bg-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] overflow-hidden">
        <header class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50/70 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-3.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-marca-50 border border-marca-200 text-marca-800">
                    <i data-lucide="id-card" class="h-5 w-5" aria-hidden="true"></i>
                </div>
                <div>
                    <h2 id="titulo-directorio-personal" class="text-base font-bold text-zinc-900">Directorio Institucional de Trabajadores</h2>
                    <p class="text-xs text-zinc-500">Filtra por estatus laboral o estado en nómina; pulsa Familiares para consultar su carga familiar en tiempo real.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <select
                    id="filtro-estatus-empleado"
                    aria-label="Filtrar por estatus laboral"
                    class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-zinc-900 transition-all focus:border-marca-500 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
                >
                    <option value="todos">Todos los estatus</option>
                    @foreach(($estatus_catalogo ?? []) as $idEstatus => $nombreEstatus)
                        <option value="{{ $idEstatus }}">{{ $nombreEstatus }}</option>
                    @endforeach
                </select>

                <select
                    id="filtro-activo-empleado"
                    aria-label="Filtrar por estado en nómina"
                    class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-zinc-900 transition-all focus:border-marca-500 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
                >
                    <option value="todos">Activos e inactivos</option>
                    <option value="1">Solo activos</option>
                    <option value="0">Solo inactivos</option>
                </select>
            </div>
        </header>

        <div class="overflow-x-auto px-6 py-5">
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
