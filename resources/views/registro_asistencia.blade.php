@extends('layouts.app')

@section('titulo', 'Control de Asistencia')

@push('styles')
    @include('components.datatables_assets')
@endpush

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 xl:px-12 2xl:px-16 pb-12 pt-6 bg-slate-50')

@section('menu_lateral')
    @include('layouts.menu_lateral')
@endsection

@section('header')
    @include('layouts.header')
@endsection

@section('contenido')
@php
    $resAsis = $resumen_asistencia ?? [
        'presentes_hoy' => 0,
        'en_jornada_hoy' => 0,
        'cerradas_hoy' => 0,
        'en_reposo_hoy' => 0,
        'nomina_activa' => 0,
    ];
@endphp
<div class="w-full max-w-none mx-auto grid grid-cols-1 md:grid-cols-12 gap-6 atlas-vista-entrada">

    {{-- Card 1 (Bento Hero Oscura): Cabecera del módulo de asistencia --}}
    <header class="col-span-full lg:col-span-6 rounded-3xl border border-zinc-900 bg-zinc-950 p-6 sm:p-7 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between gap-3">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-300">
                <i data-lucide="calendar-check-2" class="h-3.5 w-3.5" aria-hidden="true"></i>
                <span>Jornada Laboral · Cláusula 6</span>
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Control de Asistencia</h1>
            <p class="text-sm text-zinc-400">Marcaje diario de entrada y salida con verificación en tiempo real de reposos médicos vigentes.</p>
        </div>
    </header>

    {{-- Card 2 (Bento Marca): Presentes en la fecha seleccionada --}}
    <article class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl border border-marca bg-marca p-6 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-marca-200">Presentes del Día</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-marca-100">
                <i data-lucide="user-check" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p id="resumen-asistencia-presentes" class="text-4xl font-bold tracking-tight text-white">{{ $resAsis['presentes_hoy'] ?? 0 }}</p>
            <p id="resumen-asistencia-jornada" class="mt-2 text-xs font-medium text-marca-100">
                {{ $resAsis['en_jornada_hoy'] ?? 0 }} en jornada · {{ $resAsis['cerradas_hoy'] ?? 0 }} salidas marcadas
            </p>
        </div>
    </article>

    {{-- Card 3 (Bento Blanca): Personal en reposo médico hoy --}}
    <article class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">En Reposo Médico</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-yellow-50 border border-yellow-200 text-yellow-900">
                <i data-lucide="stethoscope" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p id="resumen-asistencia-reposos" class="text-4xl font-bold tracking-tight text-zinc-900">{{ $resAsis['en_reposo_hoy'] ?? 0 }}</p>
            <p id="resumen-asistencia-nomina" class="mt-2 text-xs font-medium text-zinc-500">
                Bloqueados para marcar entrada · {{ $resAsis['nomina_activa'] ?? 0 }} activos en nómina
            </p>
        </div>
    </article>

    {{-- Bloque 1: Formulario de marcaje Entrada / Salida --}}
    <section class="col-span-full lg:col-span-5 xl:col-span-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)]">
        <header class="mb-5">
            <h2 class="text-lg font-semibold text-zinc-900">Registrar Entrada o Salida</h2>
            <p class="text-sm text-zinc-500">Selecciona al trabajador activo para registrar su movimiento.</p>
        </header>

        <form id="formulario-asistencia" class="space-y-5" novalidate>
            @component('components.alerta_formulario')
            @endcomponent

            @component('components.select', [
                'label' => 'Trabajador',
                'id' => 'empleado_id',
                'name' => 'empleado_id',
                'placeholder' => 'Buscar empleado por nombre o cédula...',
                'required' => true,
                'search' => true,
                'remoteUrl' => '/api/select/empleados',
                'options' => [],
            ])
            @endcomponent

            {{-- Tarjeta de estado en vivo del trabajador (disponibilidad o reposo médico activo) --}}
            <article id="tarjeta-estado-empleado-asistencia" class="hidden rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-2 text-xs">
                <div class="flex items-center justify-between gap-2">
                    <span id="asis-emp-nombre" class="text-sm font-bold text-zinc-900"></span>
                    <span id="asis-emp-badge"></span>
                </div>
                <p id="asis-emp-meta" class="text-zinc-600"></p>
                <p id="asis-emp-aviso" class="font-medium"></p>
            </article>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @component('components.input', [
                    'label' => 'Fecha de jornada',
                    'id' => 'fecha_asistencia',
                    'name' => 'fecha',
                    'type' => 'date',
                    'value' => $fecha_actual ?? date('Y-m-d'),
                    'required' => true,
                ])
                @endcomponent

                @component('components.input', [
                    'label' => 'Hora (HH:MM)',
                    'id' => 'hora_asistencia',
                    'name' => 'hora',
                    'type' => 'time',
                    'value' => date('H:i'),
                    'required' => true,
                ])
                @endcomponent
            </div>

            @component('components.select', [
                'label' => 'Turno laboral',
                'id' => 'turno',
                'name' => 'turno',
                'selected' => 'Diurno',
                'options' => [
                    'Diurno' => 'Diurno (7:30 a.m. – 4:00 p.m.)',
                    'Mañana' => 'Mañana',
                    'Tarde' => 'Tarde',
                    'Especial' => 'Especial / Guardia',
                ],
            ])
            @endcomponent

            {{-- 2 Botones claros: Entrada y Salida --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <button
                    type="button"
                    id="btn-marcar-entrada"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-marca px-4 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] disabled:opacity-45 disabled:cursor-not-allowed cursor-pointer"
                >
                    <i data-lucide="log-in" class="h-4 w-4" aria-hidden="true"></i>
                    <span>Entrada</span>
                </button>

                <button
                    type="button"
                    id="btn-marcar-salida"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 active:scale-[0.98] disabled:opacity-45 disabled:cursor-not-allowed cursor-pointer"
                >
                    <i data-lucide="log-out" class="h-4 w-4" aria-hidden="true"></i>
                    <span>Salida</span>
                </button>
            </div>
        </form>
    </section>

    {{-- Bloque 2: Tabla de asistencias registradas (DataTables 3.1.5) --}}
    @component('components.contenedor_tabla', [
        'colSpan' => 'col-span-full lg:col-span-7 xl:col-span-8',
        'idTitulo' => 'titulo-tabla-asistencia',
        'icono' => 'clipboard-list',
        'titulo' => 'Registros de Asistencia',
        'subtitulo' => 'Consulta las entradas y salidas registradas en la base de datos.',
    ])
        @slot('filtros')
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="w-full sm:w-44 relative">
                    <input
                        id="filtro-fecha-asistencia"
                        type="date"
                        placeholder="Filtrar por fecha..."
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white"
                    />
                </div>
                <button
                    type="button"
                    id="btn-limpiar-filtro-asistencia"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-zinc-600 transition-colors hover:bg-slate-50 hover:text-zinc-900 cursor-pointer"
                >
                    <span>Ver todas</span>
                </button>
            </div>
        @endslot

        <table id="tabla-asistencia" class="w-full text-sm text-left">
            <thead class="text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-slate-50">
                <tr>
                    <th>Cédula</th>
                    <th>Trabajador</th>
                    <th>Departamento</th>
                    <th class="text-center">Turno</th>
                    <th>Fecha</th>
                    <th class="text-center">Entrada</th>
                    <th class="text-center">Salida</th>
                    <th class="text-center">Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-zinc-700"></tbody>
        </table>
    @endcomponent
</div>

{{-- Modal de detalle de asistencia --}}
@component('components.modal_detalle', [
    'id' => 'modal-detalle-asistencia',
    'titulo' => 'Detalle de jornada laboral',
    'subtitulo' => 'Información registrada en el control de asistencia.',
    'tamano' => 'md',
])
@endcomponent
@endsection

@push('scripts')
    <script type="module" src="/js/registro_asistencia.js"></script>
@endpush
