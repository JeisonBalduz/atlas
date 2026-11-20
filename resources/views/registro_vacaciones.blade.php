@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Registro de Vacaciones') {{-- TÍTULO DE LA PÁGINA --}}

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 pb-12 pt-6 bg-slate-50') {{-- CSS DE LA ETIQUETA MAIN --}}

@section('menu_lateral'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.menu_lateral'){{-- CARGAR menu_lateral  --}} 
@endsection

@section('header'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.header'){{-- CARGAR HEADER  --}} 
@endsection

@section('contenido') {{-- CARGAR CONTENIDO PRINCIPAL --}}
<div class="mx-auto max-w-7xl grid grid-cols-1 md:grid-cols-12 gap-6">

    {{-- Cabecera del módulo de vacaciones --}}
    <header class="col-span-full">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900">Registro de Vacaciones</h1>
        <p class="mt-1 text-sm text-zinc-500">Solicitud de días vacacionales, control de saldo acumulado e historial de aprobación.</p>
    </header>

    {{-- Bloque 1: Formulario de nueva solicitud de vacaciones --}}
    <section class="col-span-full lg:col-span-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)]">
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
                'options' => [],
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

    {{-- Bloque 2: Tabla de solicitudes de vacaciones --}}
    <div class="col-span-full lg:col-span-7">
        @component('components.tabla', [
            'id' => 'tabla-vacaciones',
            'titulo' => 'Solicitudes de Vacaciones',
            'subtitulo' => 'Consulta el estatus de aprobación y el desglose de períodos descontados.',
            'placeholderBuscador' => 'Buscar por empleado o cédula...',
            'columnas' => [
                ['clave' => 'empleado', 'etiqueta' => 'Empleado', 'ordenable' => true],
                ['clave' => 'cedula', 'etiqueta' => 'Cédula', 'ordenable' => true],
                ['clave' => 'dias_solicitado', 'etiqueta' => 'Días', 'ordenable' => true, 'alineacion' => 'center'],
                ['clave' => 'fecha_inicio', 'etiqueta' => 'Inicio', 'ordenable' => true],
                ['clave' => 'fecha_final', 'etiqueta' => 'Fin', 'ordenable' => true],
                ['clave' => 'estatus_texto', 'etiqueta' => 'Estatus', 'ordenable' => true, 'alineacion' => 'center'],
                ['clave' => 'acciones', 'etiqueta' => 'Acciones', 'ordenable' => false, 'alineacion' => 'right'],
            ],
        ])
            <select
                id="filtro-estatus-vacaciones"
                aria-label="Filtrar por estatus de solicitud"
                class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-zinc-900 transition-all focus:border-marca-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
            >
                <option value="todos">Todos los estatus</option>
                <option value="Pendiente">Pendiente</option>
                <option value="Aprobada por Talento Humano">Aprobada por Talento Humano</option>
                <option value="Negada por Talento Humano">Negada por Talento Humano</option>
                <option value="Aprobada por Gerencia">Aprobada por Gerencia</option>
                <option value="Reposo">Reposo</option>
                <option value="Terminada">Terminada</option>
            </select>
        @endcomponent
    </div>
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
