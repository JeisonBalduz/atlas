@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Registro de Beneficios') {{-- TÍTULO DE LA PÁGINA --}}

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

    {{-- Cabecera del módulo de beneficios --}}
    <header class="col-span-full">
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900">Registro de Beneficios</h1>
        <p class="mt-1 text-sm text-zinc-500">Asignación, seguimiento y aprobación de beneficios socioeconómicos para empleados y carga familiar.</p>
    </header>

    {{-- Bloque 1: Formulario de asignación de beneficio (DetalleBeneficioModel) --}}
    <section class="col-span-full lg:col-span-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)]">
        <header class="mb-5">
            <h2 class="text-lg font-semibold text-zinc-900">Asignar Beneficio</h2>
            <p class="text-sm text-zinc-500">Campos basados en DetalleBeneficio y TipoBeneficios.</p>
        </header>

        <form id="formulario-beneficios" class="space-y-4" novalidate>
            @component('components.select', [
                'label' => 'Beneficiario (Empleado o Familiar)',
                'id' => 'persona_id',
                'name' => 'persona_id',
                'placeholder' => 'Seleccionar persona...',
                'required' => true,
                'search' => true,
                'options' => $personas ?? [],
            ])
            @endcomponent

            @component('components.select', [
                'label' => 'Tipo de beneficio',
                'id' => 'beneficio_id',
                'name' => 'beneficio_id',
                'placeholder' => 'Seleccionar beneficio...',
                'required' => true,
                'search' => true,
                'options' => $tipos_beneficio ?? [],
            ])
            @endcomponent

            @component('components.input', [
                'label' => 'Fecha de solicitud',
                'id' => 'fecha_solicitud',
                'name' => 'fecha_solicitud',
                'type' => 'date',
                'required' => true,
            ])
            @endcomponent

            @component('components.input', [
                'label' => 'Fecha de aprobación (opcional)',
                'id' => 'fecha_aprobada',
                'name' => 'fecha_aprobada',
                'type' => 'date',
            ])
            @endcomponent

            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] cursor-pointer"
            >
                <i data-lucide="gift" class="h-4 w-4" aria-hidden="true"></i>
                <span>Registrar Beneficio</span>
            </button>
        </form>
    </section>

    {{-- Bloque 2: Tabla de personal y beneficios asignados (DataTables 3.1.5) --}}
    <section aria-labelledby="titulo-tabla-beneficios" class="col-span-full lg:col-span-8 rounded-2xl border border-slate-200 bg-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] overflow-hidden">
        <header class="flex flex-col gap-3 border-b border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 id="titulo-tabla-beneficios" class="text-base font-semibold text-zinc-900">Beneficios Asignados</h2>
                <p class="text-xs text-zinc-500">Consulta beneficiarios, fechas de solicitud y aprobación.</p>
            </div>

            <select
                id="filtro-estatus-beneficio"
                aria-label="Filtrar por estatus del beneficio"
                class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-zinc-900 transition-all focus:border-marca-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
            >
                <option value="todos">Todos los estatus</option>
                <option value="Aprobado">Aprobado</option>
                <option value="Pendiente">Pendiente</option>
            </select>
        </header>

        <div class="overflow-x-auto px-5 py-4">
            <table id="tabla-beneficios" class="w-full text-sm text-left">
                <thead class="text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-slate-50">
                    <tr>
                        <th>Cédula</th>
                        <th>Beneficiario</th>
                        <th>Rol / Parentesco</th>
                        <th>Beneficio</th>
                        <th>Solicitud</th>
                        <th>Aprobación</th>
                        <th class="text-center">Estatus</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-zinc-700"></tbody>
            </table>
        </div>
    </section>
</div>

{{-- Modal de detalle / aprobación del beneficio --}}
@component('components.modal', [
    'id' => 'modal-detalle-beneficio',
    'titulo' => 'Detalle del beneficio asignado',
    'subtitulo' => 'Consulta o aprueba la solicitud seleccionada.',
    'tamano' => 'md',
    'textoCancelar' => 'Cerrar',
    'textoConfirmar' => 'Aprobar solicitud',
    'varianteConfirmar' => 'green',
])
@endcomponent
@endsection

@push('scripts') {{-- CARGAR SCRIPTS DE JS --}}
    <script type="module" src="/js/registro_beneficios.js"></script>
@endpush
