@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Registro de Ausencias') {{-- TÍTULO DE LA PÁGINA --}}

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

    {{-- Card 1 (Bento Hero Oscura): Cabecera del módulo de ausencias justificadas --}}
    <header class="col-span-full lg:col-span-6 rounded-3xl border border-zinc-900 bg-zinc-950 p-6 sm:p-7 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between gap-3">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-300">
                <i data-lucide="clipboard-check" class="h-3.5 w-3.5" aria-hidden="true"></i>
                <span>Control de Asistencia</span>
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Registro de Ausencias Justificadas</h1>
            <p class="text-sm text-zinc-400">Gestiona permisos administrativos y reposos médicos con cálculo automático de días y trazabilidad por institución emisora.</p>
        </div>
    </header>

    {{-- Card 2 (Bento Marca): Modalidades de permiso --}}
    <article class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl border border-marca bg-marca p-6 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-marca-200">Tipos de Permiso</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-marca-100">
                <i data-lucide="clock" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-4xl font-bold tracking-tight text-white">{{ count($tipos_permiso ?? []) }}</p>
            <p class="mt-2 text-xs font-medium text-marca-100">Causales administrativas</p>
        </div>
    </article>

    {{-- Card 3 (Bento Blanca con Acento): Reposos e instituciones --}}
    <article class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Reposos Médicos</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-yellow-50 border border-yellow-200 text-yellow-900">
                <i data-lucide="stethoscope" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-4xl font-bold tracking-tight text-zinc-900">{{ count($tipos_reposo ?? []) }}</p>
            <p class="mt-2 text-xs font-medium text-zinc-500">{{ count($tipos_institucion ?? []) }} instituciones emisoras registradas</p>
        </div>
    </article>

    {{-- Bloque 1: Formulario dinámico Permiso / Reposo --}}
    <section class="col-span-full lg:col-span-5 xl:col-span-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)]">
        <header class="mb-5">
            <h2 class="text-lg font-semibold text-zinc-900">Nueva Ausencia Justificada</h2>
            <p class="text-sm text-zinc-500">Los campos se adaptan según selecciones Permiso o Reposo.</p>
        </header>

        <form id="formulario-ausencia" class="space-y-5" novalidate>
            {{-- Selector de tipo de ausencia (1 = Permiso, 2 = Reposo) --}}
            <fieldset class="space-y-2">
                <legend class="text-sm font-medium text-zinc-900">Tipo de ausencia</legend>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-medium text-zinc-900 transition-all hover:bg-slate-100 cursor-pointer">
                        <input type="radio" name="tipo_ausencia" value="1" checked class="text-marca focus:ring-marca-500" />
                        <span>Permiso</span>
                    </label>
                    <label class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-medium text-zinc-900 transition-all hover:bg-slate-100 cursor-pointer">
                        <input type="radio" name="tipo_ausencia" value="2" class="text-marca focus:ring-marca-500" />
                        <span>Reposo</span>
                    </label>
                </div>
            </fieldset>

            @component('components.select', [
                'label' => 'Empleado',
                'id' => 'empleado_id',
                'name' => 'empleado_id',
                'placeholder' => 'Seleccionar empleado...',
                'required' => true,
                'search' => true,
                'options' => $empleados ?? [],
            ])
            @endcomponent

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @component('components.input', [
                    'label' => 'Fecha inicio',
                    'id' => 'fecha_inicio',
                    'name' => 'fecha_inicio',
                    'type' => 'date',
                    'required' => true,
                ])
                @endcomponent

                @component('components.input', [
                    'label' => 'Fecha fin',
                    'id' => 'fecha_fin',
                    'name' => 'fecha_fin',
                    'type' => 'date',
                    'required' => true,
                ])
                @endcomponent
            </div>

            @component('components.input', [
                'label' => 'Días de ausencia (cálculo automático)',
                'id' => 'dias_ausencia',
                'name' => 'dias_ausencia',
                'type' => 'number',
                'placeholder' => '0',
                'readonly' => true,
                'required' => true,
            ])
            @endcomponent

            {{-- Campos específicos de DetallePermiso --}}
            <div id="bloque-campos-permiso" class="space-y-4 rounded-xl border border-sky-200 bg-sky-50/40 p-4">
                <h3 class="text-sm font-semibold text-zinc-900">Detalle del Permiso</h3>

                @component('components.select', [
                    'label' => 'Tipo de permiso',
                    'id' => 'tppermiso_id',
                    'name' => 'tppermiso_id',
                    'placeholder' => 'Seleccionar tipo de permiso...',
                    'options' => $tipos_permiso ?? [],
                ])
                @endcomponent

                @component('components.input', [
                    'label' => 'Motivo del permiso',
                    'id' => 'motivo',
                    'name' => 'motivo',
                    'placeholder' => 'Ej: Trámite legal personal',
                    'maxlength' => 255,
                ])
                @endcomponent

                <label class="flex items-center justify-between rounded-lg border border-slate-200 bg-white px-3.5 py-2.5 cursor-pointer">
                    <span class="text-sm font-medium text-zinc-900">Permiso remunerable</span>
                    <input type="checkbox" id="remunerable" name="remunerable" checked class="h-4 w-4 rounded border-slate-300 text-marca focus:ring-marca-500" />
                </label>
            </div>

            {{-- Campos específicos de DetalleReposo --}}
            <div id="bloque-campos-reposo" class="hidden space-y-4 rounded-xl border border-yellow-200 bg-yellow-50/50 p-4">
                <h3 class="text-sm font-semibold text-zinc-900">Detalle del Reposo Médico</h3>

                @component('components.select', [
                    'label' => 'Tipo de reposo',
                    'id' => 'tpreposo_id',
                    'name' => 'tpreposo_id',
                    'placeholder' => 'Seleccionar tipo de reposo...',
                    'options' => $tipos_reposo ?? [],
                ])
                @endcomponent

                @component('components.select', [
                    'label' => 'Institución emisora',
                    'id' => 'tipo_institucion_emisora_id',
                    'name' => 'tipo_institucion_emisora_id',
                    'placeholder' => 'Seleccionar institución...',
                    'options' => $tipos_institucion ?? [],
                ])
                @endcomponent

                @component('components.input', [
                    'label' => 'Médico tratante',
                    'id' => 'doctor',
                    'name' => 'doctor',
                    'placeholder' => 'Ej: Dra. Patricia Gómez',
                    'maxlength' => 100,
                ])
                @endcomponent

                @component('components.input', [
                    'label' => 'Descripción médica',
                    'id' => 'descripcion',
                    'name' => 'descripcion',
                    'placeholder' => 'Ej: Reposo post-operatorio ambulatorio',
                    'maxlength' => 255,
                ])
                @endcomponent
            </div>

            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] cursor-pointer"
            >
                <i data-lucide="clipboard-check" class="h-4 w-4" aria-hidden="true"></i>
                <span>Registrar Ausencia</span>
            </button>
        </form>
    </section>

    {{-- Bloque 2: Tabla unificada de ausencias justificadas (DataTables 3.1.5) --}}
    <section aria-labelledby="titulo-tabla-ausencias" class="col-span-full lg:col-span-7 xl:col-span-8 rounded-3xl border border-slate-200 bg-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] overflow-hidden">
        <header class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50/70 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-marca-50 border border-marca-200 text-marca-800">
                    <i data-lucide="calendar-clock" class="h-5 w-5" aria-hidden="true"></i>
                </div>
                <div>
                    <h2 id="titulo-tabla-ausencias" class="text-base font-bold text-zinc-900">Ausencias Justificadas</h2>
                    <p class="text-xs text-zinc-500">Filtra rápidamente entre permisos y reposos médicos.</p>
                </div>
            </div>

            {{-- Filtro rápido por tipo de ausencia --}}
            <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1" role="group" aria-label="Filtrar por tipo">
                <button type="button" data-filtro-tipo="todos" class="btn-filtro-ausencia rounded-lg bg-marca px-3 py-1.5 text-xs font-medium text-white transition-all cursor-pointer">Todos</button>
                <button type="button" data-filtro-tipo="Permiso" class="btn-filtro-ausencia rounded-lg px-3 py-1.5 text-xs font-medium text-zinc-500 transition-all hover:text-zinc-900 cursor-pointer">Permiso</button>
                <button type="button" data-filtro-tipo="Reposo" class="btn-filtro-ausencia rounded-lg px-3 py-1.5 text-xs font-medium text-zinc-500 transition-all hover:text-zinc-900 cursor-pointer">Reposo</button>
            </div>
        </header>

        <div class="overflow-x-auto px-6 py-5">
            <table id="tabla-ausencias" class="w-full text-sm text-left">
                <thead class="text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-slate-50">
                    <tr>
                        <th>Cédula</th>
                        <th>Empleado</th>
                        <th class="text-center">Tipo</th>
                        <th>Detalle / Motivo</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th class="text-center">Días</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-zinc-700"></tbody>
            </table>
        </div>
    </section>
</div>

{{-- Modal de detalle de ausencia --}}
@component('components.modal', [
    'id' => 'modal-detalle-ausencia',
    'titulo' => 'Detalle de ausencia justificada',
    'subtitulo' => 'Información registrada del permiso o reposo médico.',
    'tamano' => 'md',
    'textoCancelar' => 'Cerrar',
])
@endcomponent
@endsection

@push('scripts') {{-- CARGAR SCRIPTS DE JS --}}
    <script type="module" src="/js/registro_ausencia.js"></script>
@endpush
