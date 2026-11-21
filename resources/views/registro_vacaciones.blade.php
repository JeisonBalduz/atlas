@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Registro de Vacaciones') {{-- TÍTULO DE LA PÁGINA --}}

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

    {{-- Card 1 (Bento Hero Oscura): Cabecera del módulo de vacaciones --}}
    <header class="col-span-full lg:col-span-6 rounded-3xl border border-zinc-900 bg-zinc-950 p-6 sm:p-7 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between gap-3">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-300">
                <i data-lucide="palmtree" class="h-3.5 w-3.5" aria-hidden="true"></i>
                <span>Períodos y Disfrute Vacacional</span>
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Registro de Vacaciones</h1>
            <p class="text-sm text-zinc-400">Solicitud de días vacacionales, control de saldo acumulado por período e historial de movimientos de aprobación.</p>
        </div>
    </header>

    {{-- Card 2 (Bento Marca): Personal activo elegible --}}
    <article class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl border border-marca bg-marca p-6 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-marca-200">Personal Elegible</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-marca-100">
                <i data-lucide="user-check" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-4xl font-bold tracking-tight text-white">{{ count($empleados ?? []) }}</p>
            <p class="mt-2 text-xs font-medium text-marca-100">Trabajadores activos en nómina</p>
        </div>
    </article>

    {{-- Card 3 (Bento Blanca con Acento Sky): Nivel de aprobación --}}
    <article class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Flujo de Aprobación</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-sky-50 border border-sky-200 text-sky-800">
                <i data-lucide="calendar-check-2" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-xl sm:text-2xl font-bold tracking-tight text-zinc-900">RRHH y Gerencia</p>
            <p class="mt-2 text-xs font-medium text-zinc-500">Descuento automático por período vencido</p>
        </div>
    </article>

    {{-- Bloque 1: Formulario de nueva solicitud de vacaciones --}}
    <section class="col-span-full lg:col-span-5 xl:col-span-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)]">
        <header class="mb-5">
            <h2 class="text-lg font-semibold text-zinc-900">Nueva Solicitud</h2>
            <p class="text-sm text-zinc-500">Campos basados en SolicitudVacaciones y PeriodoVacaciones.</p>
        </header>

        <form id="formulario-vacaciones" class="space-y-5" novalidate>
            @component('components.select', [
                'label' => 'Empleado solicitante',
                'id' => 'empleado_id',
                'name' => 'empleado_id',
                'placeholder' => 'Seleccionar empleado...',
                'required' => true,
                'search' => true,
                'options' => $empleados ?? [],
            ])
            @endcomponent

            {{-- Tarjeta de saldo de días pendientes del empleado --}}
            <article id="tarjeta-saldo-vacaciones" class="hidden rounded-xl border border-sky-200 bg-sky-50 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium text-sky-800">Días pendientes acumulados</p>
                        <p id="saldo-dias-pendientes" class="mt-0.5 text-2xl font-bold text-sky-900">0 días</p>
                    </div>
                    <div class="text-right text-xs text-sky-800">
                        <p id="saldo-periodos-activos" class="font-medium"></p>
                    </div>
                </div>
            </article>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @component('components.input', [
                    'label' => 'Fecha de inicio',
                    'id' => 'fecha_inicio',
                    'name' => 'fecha_inicio',
                    'type' => 'date',
                    'required' => true,
                ])
                @endcomponent

                @component('components.input', [
                    'label' => 'Fecha final',
                    'id' => 'fecha_final',
                    'name' => 'fecha_final',
                    'type' => 'date',
                    'required' => true,
                ])
                @endcomponent
            </div>

            @component('components.input', [
                'label' => 'Días solicitados (cálculo automático)',
                'id' => 'dias_solicitado',
                'name' => 'dias_solicitado',
                'type' => 'number',
                'placeholder' => '0',
                'readonly' => true,
                'required' => true,
            ])
            @endcomponent

            {{-- Switch / checkbox de autorización gerencial --}}
            <label class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 cursor-pointer">
                <div>
                    <span class="block text-sm font-medium text-zinc-900">Requiere firma de Gerencia</span>
                    <span class="block text-xs text-zinc-500">Marca esta casilla si el solicitante pertenece a nivel gerencial.</span>
                </div>
                <input
                    type="checkbox"
                    id="gerencia"
                    name="gerencia"
                    class="h-4 w-4 rounded border-slate-300 text-marca focus:ring-2 focus:ring-marca-500"
                />
            </label>

            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] cursor-pointer"
            >
                <i data-lucide="calendar-check" class="h-4 w-4" aria-hidden="true"></i>
                <span>Registrar Solicitud</span>
            </button>
        </form>
    </section>

    {{-- Bloque 2: Tabla de solicitudes de vacaciones (DataTables 3.1.5) --}}
    <section aria-labelledby="titulo-tabla-vacaciones" class="col-span-full lg:col-span-7 xl:col-span-8 rounded-3xl border border-slate-200 bg-white shadow-[0_15px_30px_rgba(0,0,0,0.05)]">
        <header class="flex flex-col gap-3 rounded-t-3xl border-b border-slate-200 bg-slate-50/70 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-marca-50 border border-marca-200 text-marca-800">
                    <i data-lucide="calendar-days" class="h-5 w-5" aria-hidden="true"></i>
                </div>
                <div>
                    <h2 id="titulo-tabla-vacaciones" class="text-base font-bold text-zinc-900">Solicitudes de Vacaciones</h2>
                    <p class="text-xs text-zinc-500">Consulta el estatus de aprobación y el desglose de períodos descontados.</p>
                </div>
            </div>

            <div class="w-full sm:w-64">
                @component('components.select', [
                    'id' => 'filtro-estatus-vacaciones',
                    'name' => 'filtro_estatus_vacaciones',
                    'placeholder' => 'Todos los estatus',
                    'selected' => 'todos',
                    'search' => true,
                    'options' => [
                        'todos' => 'Todos los estatus',
                        'Pendiente' => 'Pendiente',
                        'Aprobada por Talento Humano' => 'Aprobada por Talento Humano',
                        'Negada por Talento Humano' => 'Negada por Talento Humano',
                        'Aprobada por Gerencia' => 'Aprobada por Gerencia',
                        'Reposo' => 'Reposo',
                        'Terminada' => 'Terminada',
                    ],
                ])
                @endcomponent
            </div>
        </header>

        <div class="overflow-x-auto px-6 py-5">
            <table id="tabla-vacaciones" class="w-full text-sm text-left">
                <thead class="text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-slate-50">
                    <tr>
                        <th>Empleado</th>
                        <th>Cédula</th>
                        <th class="text-center">Días</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th class="text-center">Estatus</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-zinc-700"></tbody>
            </table>
        </div>
    </section>
</div>

{{-- Modal de desglose de períodos descontados e historial --}}
@component('components.modal', [
    'id' => 'modal-detalle-vacaciones',
    'titulo' => 'Detalle de solicitud de vacaciones',
    'subtitulo' => 'Desglose de períodos descontados e historial de movimientos.',
    'tamano' => 'lg',
    'textoCancelar' => 'Cerrar',
])
@endcomponent
@endsection

@push('scripts') {{-- CARGAR SCRIPTS DE JS --}}
    <script type="module" src="/js/registro_vacaciones.js"></script>
@endpush
