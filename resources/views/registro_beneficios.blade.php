@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Registro de Beneficios') {{-- TÍTULO DE LA PÁGINA --}}

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

    {{-- Card 1 (Bento Hero Oscura): Cabecera del módulo de beneficios --}}
    <header class="col-span-full lg:col-span-6 rounded-3xl border border-zinc-900 bg-zinc-950 p-6 sm:p-7 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between gap-3">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-300">
                <i data-lucide="gift" class="h-3.5 w-3.5" aria-hidden="true"></i>
                <span>Bienestar Social · Contrato Colectivo</span>
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Registro de Beneficios</h1>
            <p class="text-sm text-zinc-400">Asignación, seguimiento y aprobación de beneficios socioeconómicos para trabajadores titulares y su carga familiar.</p>
        </div>
    </header>

    {{-- Card 2 (Bento Marca): Tipos de beneficio --}}
    <article class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl border border-marca bg-marca p-6 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-marca-200">Catálogo de Beneficios</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-marca-100">
                <i data-lucide="award" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-4xl font-bold tracking-tight text-white">{{ count($tipos_beneficio ?? []) }}</p>
            <p class="mt-2 text-xs font-medium text-marca-100">Modalidades socioeconómicas</p>
        </div>
    </article>

    {{-- Card 3 (Bento Blanca con Acento Verde): Personas elegibles --}}
    <article class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Beneficiarios Elegibles</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-green-50 border border-green-200 text-green-800">
                <i data-lucide="users" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-4xl font-bold tracking-tight text-zinc-900">{{ $total_beneficiarios ?? count($personas ?? []) }}</p>
            <p class="mt-2 text-xs font-medium text-zinc-500">Titulares y familiares registrados</p>
        </div>
    </article>

    {{-- Bloque 1: Formulario de asignación de beneficio (DetalleBeneficioModel) --}}
    <section class="col-span-full lg:col-span-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)]">
        <header class="mb-5">
            <h2 class="text-lg font-semibold text-zinc-900">Asignar Beneficio</h2>
            <p class="text-sm text-zinc-500">Campos basados en DetalleBeneficio y TipoBeneficios.</p>
        </header>

        <form id="formulario-beneficios" class="space-y-4" novalidate>
            @component('components.select', [
                'label' => 'Beneficiario (Empleado o Familiar)',
                'id' => 'persona_id',
                'name' => 'persona_id',
                'placeholder' => 'Buscar persona por nombre o cédula...',
                'required' => true,
                'search' => true,
                'remoteUrl' => '/api/select/personas',
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
    <section aria-labelledby="titulo-tabla-beneficios" class="col-span-full lg:col-span-8 rounded-3xl border border-slate-200 bg-white shadow-[0_15px_30px_rgba(0,0,0,0.05)]">
        <header class="flex flex-col gap-3 rounded-t-3xl border-b border-slate-200 bg-slate-50/70 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3.5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-marca-50 border border-marca-200 text-marca-800">
                    <i data-lucide="list-checks" class="h-5 w-5" aria-hidden="true"></i>
                </div>
                <div>
                    <h2 id="titulo-tabla-beneficios" class="text-base font-bold text-zinc-900">Beneficios Asignados</h2>
                    <p class="text-xs text-zinc-500">Consulta beneficiarios, fechas de solicitud y aprobación.</p>
                </div>
            </div>

            <div class="w-full sm:w-52">
                @component('components.select', [
                    'id' => 'filtro-estatus-beneficio',
                    'name' => 'filtro_estatus_beneficio',
                    'placeholder' => 'Todos los estatus',
                    'selected' => 'todos',
                    'options' => [
                        'todos' => 'Todos los estatus',
                        'Aprobado' => 'Aprobado',
                        'Pendiente' => 'Pendiente',
                    ],
                ])
                @endcomponent
            </div>
        </header>

        <div class="overflow-x-auto px-6 py-5">
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
