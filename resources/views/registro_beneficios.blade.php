@extends('layouts.app')

@section('titulo', 'Registro de Beneficios')

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
<div class="w-full max-w-none mx-auto grid grid-cols-1 md:grid-cols-12 gap-6 atlas-vista-entrada">

    {{-- Card 1 (Bento Hero Oscura): Cabecera del módulo de beneficios --}}
    <header class="col-span-full lg:col-span-6 rounded-3xl border border-zinc-900 bg-zinc-950 p-6 sm:p-7 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between gap-3">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-300">
                <i data-lucide="gift" class="h-3.5 w-3.5" aria-hidden="true"></i>
                <span>Bienestar Social · Convención Colectiva INCES</span>
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Registro de Beneficios</h1>
            <p class="text-sm text-zinc-400">Asignación con validación de periodicidad contractual (entregas múltiples) y control de recaudos obligatorios.</p>
        </div>
    </header>

    {{-- Card 2 (Bento Marca): Catálogo real de beneficios --}}
    <article class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl border border-marca bg-marca p-6 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-marca-200">Catálogo de Beneficios</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-marca-100">
                <i data-lucide="award" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-4xl font-bold tracking-tight text-white">{{ count($tipos_beneficio ?? []) }}</p>
            <p id="resumen-beneficios-otorgados" class="mt-2 text-xs font-medium text-marca-100">Modalidades socioeconómicas INCES</p>
        </div>
    </article>

    {{-- Card 3 (Bento Blanca con Acento Verde): Personas elegibles reales --}}
    <article class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Beneficiarios Elegibles</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-green-50 border border-green-200 text-green-800">
                <i data-lucide="users" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-4xl font-bold tracking-tight text-zinc-900">{{ $total_beneficiarios ?? count($personas ?? []) }}</p>
            <p class="mt-2 text-xs font-medium text-zinc-500">Titulares y familiares registrados en BD</p>
        </div>
    </article>

    {{-- Bloque 1: Formulario de asignación de beneficio (DetalleBeneficioModel + Reglas Contrato Colectivo) --}}
    <section class="col-span-full lg:col-span-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)]">
        <header class="mb-5">
            <h2 id="titulo-form-beneficio" class="text-lg font-semibold text-zinc-900">Asignar Beneficio</h2>
            <p class="text-sm text-zinc-500">Evalúa automáticamente la cláusula, periodicidad y soporte documental.</p>
        </header>

        <form id="formulario-beneficios"
            enctype="multipart/form-data"
            data-reglas="{{ htmlspecialchars(json_encode($reglas_beneficios ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') }}"
            class="space-y-4"
            novalidate>
            <input type="hidden" id="dtbeneficio_id" name="dtbeneficio_id" value="">

            @component('components.alerta_formulario')
            @endcomponent

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

            {{-- Tarjeta informativa dinámica según Contrato Colectivo --}}
            <article id="panel-regla-beneficio" class="hidden rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span id="regla-clausula" class="inline-flex items-center rounded-full bg-marca-50 border border-marca-200 px-2.5 py-0.5 text-xs font-semibold text-marca"></span>
                    <span id="regla-badge-doc"></span>
                </div>
                <p id="regla-periodicidad" class="text-xs font-semibold text-zinc-900"></p>
                <p id="regla-nota" class="text-xs text-zinc-600"></p>

                {{-- Bloque condicional de documento obligatorio --}}
                <div id="bloque-documento-beneficio" class="hidden space-y-2.5 border-t border-slate-200 pt-3">
                    <div id="estado-doc-vigente" class="hidden rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-xs text-green-800"></div>
                    <div id="contenedor-select-tipo-doc"></div>
                    <div>
                        <label for="archivo_soporte" class="block text-xs font-semibold text-zinc-900 mb-1">
                            Soporte documental (PDF/JPG/PNG, máx. 5 MB)
                        </label>
                        <input
                            id="archivo_soporte"
                            name="archivo_soporte"
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png,.webp"
                            class="w-full rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-zinc-700 file:mr-2.5 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-white hover:file:bg-zinc-800 cursor-pointer"
                        />
                    </div>
                </div>
            </article>

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

            <div class="flex flex-col gap-2">
                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] cursor-pointer"
                >
                    <i data-lucide="gift" class="h-4 w-4" aria-hidden="true"></i>
                    <span id="texto-btn-submit-beneficio">Registrar Beneficio</span>
                </button>

                <button
                    type="button"
                    id="btn-cancelar-edicion-beneficio"
                    class="hidden w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2 text-sm font-medium text-zinc-700 transition-all hover:bg-slate-50 cursor-pointer"
                >
                    <i data-lucide="x" class="h-4 w-4" aria-hidden="true"></i>
                    <span>Cancelar edición</span>
                </button>
            </div>
        </form>
    </section>

    {{-- Bloque 2: Tabla de personal y beneficios asignados (DataTables 3.1.5) --}}
    @component('components.contenedor_tabla', [
        'colSpan' => 'col-span-full lg:col-span-8',
        'idTitulo' => 'titulo-tabla-beneficios',
        'icono' => 'list-checks',
        'titulo' => 'Beneficios Asignados',
        'subtitulo' => 'Consulta beneficiarios, cláusula contractual, fechas de solicitud y aprobación.',
    ])
        @slot('filtros')
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
            <button
                type="button"
                id="btn-carga-masiva-beneficio"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-marca px-4 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] cursor-pointer"
            >
                <i data-lucide="users" class="h-4 w-4" aria-hidden="true"></i>
                <span>Carga masiva de beneficio</span>
            </button>
        @endslot

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
    @endcomponent
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

@component('components.modal', [
    'id' => 'modal-carga-masiva-beneficio',
    'titulo' => 'Carga masiva de beneficio',
    'subtitulo' => 'Asigna un beneficio a todos los empleados activos o a sus familiares.',
    'tamano' => 'md',
    'textoCancelar' => 'Cancelar',
    'textoConfirmar' => 'Asignar beneficio',
    'varianteConfirmar' => 'marca',
])
    <form id="form-carga-masiva-beneficio" class="space-y-4" novalidate>
        @component('components.alerta_formulario')
        @endcomponent

        @component('components.select', [
            'label' => 'Tipo de beneficio',
            'id' => 'masivo_beneficio_id',
            'name' => 'beneficio_id',
            'placeholder' => 'Seleccionar beneficio...',
            'required' => true,
            'search' => true,
            'options' => $tipos_beneficio ?? [],
        ])
        @endcomponent

        <fieldset class="space-y-2">
            <legend class="mb-1.5 block text-sm font-medium text-zinc-900">Destinatarios</legend>
            <label class="flex items-center gap-2 text-sm text-zinc-700 cursor-pointer">
                <input type="checkbox" id="masivo_empleados" name="tipo_asignacion" value="empleado" class="h-4 w-4 rounded border-slate-300 text-marca focus:ring-marca-500" />
                <span>Asignar a empleados</span>
            </label>
            <label class="flex items-center gap-2 text-sm text-zinc-700 cursor-pointer">
                <input type="checkbox" id="masivo_familiares" name="tipo_asignacion" value="familiar" class="h-4 w-4 rounded border-slate-300 text-marca focus:ring-marca-500" />
                <span>Asignar a familiar</span>
            </label>
        </fieldset>
    </form>
@endcomponent
@endsection

@push('scripts')
    <script type="module" src="/js/registro_beneficios.js"></script>
@endpush
