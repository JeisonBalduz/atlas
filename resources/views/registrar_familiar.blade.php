@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Registrar Familiar') {{-- TÍTULO DE LA PÁGINA --}}

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 pb-12 pt-6 bg-slate-50') {{-- CSS DE LA ETIQUETA MAIN --}}

@section('menu_lateral'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.menu_lateral'){{-- CARGAR menu_lateral  --}} 
@endsection

@section('header'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.header'){{-- CARGAR HEADER  --}} 
@endsection

@section('contenido') {{-- CARGAR CONTENIDO PRINCIPAL --}}
<form id="formulario-registrar-familiar" enctype="multipart/form-data" data-recaudos-parentesco="{{ json_encode($recaudos_por_parentesco ?? [], JSON_UNESCAPED_UNICODE) }}" class="mx-auto max-w-6xl grid grid-cols-1 md:grid-cols-12 gap-6" novalidate>

    {{-- Cabecera con enlace de retorno --}}
    <header class="col-span-full flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900">Registrar Familiar</h1>
            <p class="mt-1 text-sm text-zinc-500">Asocia una nueva carga familiar a un empleado titular con sus datos de filiación.</p>
        </div>

        <a
            href="/empleado/registros/familiar"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-900 shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
        >
            <i data-lucide="arrow-left" class="h-4 w-4" aria-hidden="true"></i>
            <span>Volver al listado</span>
        </a>
    </header>

    @component('components.alerta_formulario')
    @endcomponent

    {{-- Bloque 1: Selección del empleado titular y tarjeta resumen --}}
    <section class="col-span-full md:col-span-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
        <header>
            <h2 class="text-lg font-semibold text-zinc-900">1. Empleado Titular</h2>
            <p class="text-sm text-zinc-500">Selecciona al trabajador responsable de la carga familiar.</p>
        </header>

        @component('components.select', [
            'label' => 'Empleado titular',
            'id' => 'empleado_id',
            'name' => 'empleado_id',
            'placeholder' => 'Buscar empleado por nombre o cédula...',
            'required' => true,
            'search' => true,
            'remoteUrl' => '/api/select/empleados',
            'options' => $empleados ?? [],
            'selected' => $empleado_seleccionado ?? '',
        ])
        @endcomponent

        {{-- Tarjeta resumen del empleado seleccionado --}}
        <article id="tarjeta-resumen-titular" class="hidden rounded-xl border border-marca-200 bg-marca-50 p-4 space-y-2">
            <div class="flex items-center gap-2 text-sm font-semibold text-marca-800">
                <i data-lucide="badge-check" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
                <span id="titular-nombre"></span>
            </div>
            <dl class="grid grid-cols-2 gap-2 text-xs text-zinc-700">
                <div>
                    <dt class="text-zinc-500">Cédula</dt>
                    <dd id="titular-cedula" class="font-semibold text-zinc-900"></dd>
                </div>
                <div>
                    <dt class="text-zinc-500">Código</dt>
                    <dd id="titular-codigo" class="font-semibold text-zinc-900"></dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-zinc-500">Cargo y Departamento</dt>
                    <dd id="titular-cargo" class="font-medium text-zinc-900"></dd>
                </div>
            </dl>
        </article>
    </section>

    {{-- Bloque 2: Datos personales del familiar y datos de filiación --}}
    <section class="col-span-full md:col-span-7 rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-6">
        <header>
            <h2 class="text-lg font-semibold text-zinc-900">2. Datos del Familiar y Filiación</h2>
            <p class="text-sm text-zinc-500">Campos alineados con los modelos Personas y Familiares.</p>
        </header>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div class="grid grid-cols-[7rem_1fr] gap-3 sm:col-span-2">
                @component('components.select', [
                    'label' => 'Nacionalidad',
                    'id' => 'nacionalidad',
                    'name' => 'nacionalidad',
                    'placeholder' => 'Nac.',
                    'required' => true,
                    'options' => ['V' => 'V', 'E' => 'E'],
                ])
                @endcomponent

                @component('components.input', [
                    'label' => 'Cédula del familiar',
                    'id' => 'cedula',
                    'name' => 'cedula',
                    'placeholder' => 'Ej: 31245678',
                    'maxlength' => 9,
                    'leftIcon' => 'id-card',
                    'required' => true,
                ])
                @endcomponent
            </div>

            @component('components.input', [
                'label' => 'Primer nombre',
                'id' => 'primer_nombre',
                'name' => 'primer_nombre',
                'placeholder' => 'Ej: Sofía',
                'maxlength' => 100,
                'required' => true,
            ])
            @endcomponent

            @component('components.input', [
                'label' => 'Segundo nombre',
                'id' => 'segundo_nombre',
                'name' => 'segundo_nombre',
                'placeholder' => 'Ej: Valentina',
                'maxlength' => 100,
                'required' => true,
            ])
            @endcomponent

            @component('components.input', [
                'label' => 'Primer apellido',
                'id' => 'primer_apellido',
                'name' => 'primer_apellido',
                'placeholder' => 'Ej: Mendoza',
                'maxlength' => 100,
                'required' => true,
            ])
            @endcomponent

            @component('components.input', [
                'label' => 'Segundo apellido',
                'id' => 'segundo_apellido',
                'name' => 'segundo_apellido',
                'placeholder' => 'Ej: Herrera',
                'maxlength' => 100,
                'required' => true,
            ])
            @endcomponent

            @component('components.input', [
                'label' => 'Fecha de nacimiento',
                'id' => 'fecha_nacimiento',
                'name' => 'fecha_nacimiento',
                'type' => 'date',
                'required' => true,
            ])
            @endcomponent

            @component('components.select', [
                'label' => 'Género',
                'id' => 'genero',
                'name' => 'genero',
                'required' => true,
                'options' => $generos ?? ['Masculino' => 'Masculino', 'Femenino' => 'Femenino'],
            ])
            @endcomponent

            @component('components.select', [
                'label' => 'Estado civil',
                'id' => 'estado_civil_id',
                'name' => 'estado_civil_id',
                'required' => true,
                'options' => $estados_civiles ?? [],
            ])
            @endcomponent

            @component('components.select', [
                'label' => 'Parentesco',
                'id' => 'parentesco_id',
                'name' => 'parentesco_id',
                'required' => true,
                'search' => true,
                'options' => $parentescos ?? [],
            ])
            @endcomponent

            @component('components.input', [
                'label' => 'Tomo (Acta)',
                'id' => 'tomo',
                'name' => 'tomo',
                'placeholder' => 'Ej: 4',
                'maxlength' => 6,
                'leftIcon' => 'book-open',
                'required' => true,
            ])
            @endcomponent

            @component('components.input', [
                'label' => 'Folio (Acta)',
                'id' => 'folio',
                'name' => 'folio',
                'placeholder' => 'Ej: 128',
                'maxlength' => 6,
                'leftIcon' => 'file-text',
                'required' => true,
            ])
            @endcomponent
        </div>
    </section>

    {{-- Bloque 3: Documentos del Familiar según Parentesco (Contrato Colectivo) --}}
    <section class="col-span-full rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
        <header>
            <h2 class="text-lg font-semibold text-zinc-900">3. Documentos del Familiar</h2>
            <p class="text-sm text-zinc-500">Selecciona el parentesco para visualizar los recaudos exigidos por el Contrato Colectivo.</p>
        </header>

        <div id="contenedor-recaudos-familiar" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            <p class="col-span-full rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-zinc-500">
                Selecciona un parentesco arriba para cargar los campos de documentos correspondientes.
            </p>
        </div>

        <footer class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
            <button
                type="reset"
                id="btn-limpiar-familiar"
                class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-zinc-900 transition-all hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer"
            >
                Limpiar
            </button>
            <button
                type="submit"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] cursor-pointer"
            >
                <i data-lucide="save" class="h-4 w-4" aria-hidden="true"></i>
                <span>Guardar Familiar</span>
            </button>
        </footer>
    </section>
</form>
@endsection

@push('scripts') {{-- CARGAR SCRIPTS DE JS --}}
    <script type="module" src="/js/registrar_familiar.js"></script>
@endpush
