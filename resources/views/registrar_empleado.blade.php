@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Registro Empleado') {{-- TÍTULO DE LA PÁGINA --}}

@section('main_class',
    'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 pb-12 pt-6
    bg-slate-50') {{-- CSS DE LA ETIQUETA MAIN --}}

@section('menu_lateral'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.menu_lateral'){{-- CARGAR SIDERBAR --}}
@endsection

@section('header'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.header'){{-- CARGAR HEADER --}}
@endsection

@section('contenido') {{-- CARGAR CONTENIDO PRINCIPAL --}}
    <form id="form-registro" data-tipos-documentos="{{ json_encode($tipos_documentos ?? [], JSON_UNESCAPED_UNICODE) }}" class="mx-auto max-w-4xl scroll-mt-6 space-y-6" novalidate>

        @component('components.alerta_formulario')
        @endcomponent

        {{-- Card 1: Indicador de progreso (se genera desde los data-step) --}}
        <nav aria-label="Progreso del registro"
            class="rounded-xl border border-slate-200 bg-white px-6 pb-10 pt-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] sm:px-8">
            <ol data-step-indicator class="flex items-center"></ol>
        </nav>

        {{-- Pasos: agregar otro paso = agregar otra <section data-step> --}}
        <div data-step-viewport>

            {{-- Datos Personales --}}
            <section data-step data-step-title="Datos Personales" data-step-icon="user-round"
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] sm:p-8">
                <header class="mb-6">
                    <h2 class="text-lg font-semibold text-zinc-900">Datos Personales</h2>
                    <p class="text-sm text-zinc-500">Información de identificación del trabajador.</p>
                </header>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    @component('components.input', [
                        'label' => 'Primer Nombre',
                        'id' => 'primerNombre',
                        'name' => 'primerNombre',
                        'placeholder' => 'Ej: José',
                        'maxlength' => 40,
                        'required' => true,
                    ])
                    @endcomponent

                    @component('components.input', [
                        'label' => 'Segundo Nombre',
                        'id' => 'segundoNombre',
                        'name' => 'segundoNombre',
                        'placeholder' => 'Opcional',
                        'maxlength' => 40,
                        'required' => true,
                    ])
                    @endcomponent

                    @component('components.input', [
                        'label' => 'Primer Apellido',
                        'id' => 'primerApellido',
                        'name' => 'primerApellido',
                        'placeholder' => 'Ej: Pérez',
                        'maxlength' => 40,
                        'required' => true,
                    ])
                    @endcomponent

                    @component('components.input', [
                        'label' => 'Segundo Apellido',
                        'id' => 'segundoApellido',
                        'name' => 'segundoApellido',
                        'placeholder' => 'Opcional',
                        'maxlength' => 40,
                        'required' => true,
                    ])
                    @endcomponent

                    <div class="grid grid-cols-[7.5rem_1fr] gap-3">
                        @component('components.select', [
                            'label' => 'Nacionalidad',
                            'id' => 'nacionalidad',
                            'name' => 'nacionalidad',
                            'placeholder' => 'N',
                            'required' => true,
                            'options' => [
                                'V' => 'V',
                                'E' => 'E',
                            ],
                        ])
                        @endcomponent

                        @component('components.input', [
                            'label' => 'Cédula',
                            'id' => 'cedula',
                            'name' => 'cedula',
                            'placeholder' => 'Ej: 12345678',
                            'maxlength' => 8,
                            'leftIcon' => 'id-card',
                            'required' => true,
                        ])
                        @endcomponent
                    </div>

                    {{-- Fecha de nacimiento (componente date + calendario.js: visible dd-mm-yyyy / envío YYYY-MM-DD) --}}
                    @component('components.input', [
                        'label' => 'Fecha de nacimiento',
                        'id' => 'fecha_nacimiento',
                        'name' => 'fecha_nacimiento',
                        'type' => 'date',
                        'placeholder' => 'dd-mm-yyyy',
                        'required' => true,
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Estado civil',
                        'id' => 'civil',
                        'name' => 'civil',
                        'required' => true,
                        'options' => $estados_civiles ?? [],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Genero',
                        'id' => 'genero',
                        'name' => 'genero',
                        'required' => true,
                        'options' => $generos ?? ['Masculino' => 'Masculino', 'Femenino' => 'Femenino'],
                    ])
                    @endcomponent

                    {{-- Edad --}}
                    <div class="space-y-1.5">
                        <label
                            for="edad"
                            class="block text-sm font-medium text-zinc-900">
                            Edad
                        </label>
                        <div class="relative">
                            <input
                                id="edad"
                                name="edad"
                                type="text"
                                placeholder="Ej: 30"
                                maxlength="2"
                                autocomplete="off"
                                aria-describedby="edad-ayuda"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2.5 pl-3 pr-3 text-sm text-black outline-none transition duration-200 focus:bg-white focus:ring-2 focus:ring-offset-2 focus:border-black focus:ring-black"
                            />
                        </div>
                        <p id="edad-ayuda" class="text-xs text-zinc-500 mt-1">La edad se calcula con la fecha de nacimiento</p>
                        <p id="edad-error"
                           class="input-error-msg hidden text-xs font-medium text-red-600 mt-1 transition-all duration-200"
                           role="alert">
                        </p>
                    </div>
                </div>
            </section>

            {{-- Ubicación --}}
            <section data-step data-step-title="Ubicación" data-step-icon="map-pin"
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] sm:p-8">
                <header class="mb-6">
                    <h2 class="text-lg font-semibold text-zinc-900">Ubicación</h2>
                    <p class="text-sm text-zinc-500">Dirección de habitación del trabajador.</p>
                </header>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    @component('components.select', [
                        'label' => 'Estado',
                        'id' => 'estado',
                        'name' => 'estado',
                        'required' => true,
                        'search' => true,
                        'options' => $estados ?? [],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Municipio',
                        'id' => 'municipio',
                        'name' => 'municipio',
                        'required' => true,
                        'search' => true,
                        'options' => $municipios ?? [],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Parroquia',
                        'id' => 'parroquia',
                        'name' => 'parroquia',
                        'required' => true,
                        'search' => true,
                        'options' => $parroquias ?? [],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Vivienda',
                        'id' => 'vivienda',
                        'name' => 'vivienda',
                        'required' => true,
                        'options' => [
                            'casa' => 'Casa',
                            'apartamento' => 'Apartamento',
                            'quinta' => 'Quinta',
                            'anexo' => 'Anexo',
                            'rancho' => 'Rancho',
                        ],
                    ])
                    @endcomponent

                    <div class="md:col-span-2">
                        @component('components.input', [
                            'label' => 'Calle',
                            'id' => 'calle',
                            'name' => 'calle',
                            'placeholder' => 'Ej: Calle 5 Casa 12',
                            'maxlength' => 120,
                            'leftIcon' => 'signpost',
                            'required' => true,
                        ])
                        @endcomponent
                    </div>
                </div>
            </section>

            {{-- Datos del trabajador --}}
            <section data-step data-step-title="Datos del trabajador" data-step-icon="briefcase-business"
                class="rounded-xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] sm:p-8">
                <header class="mb-6">
                    <h2 class="text-lg font-semibold text-zinc-900">Datos del trabajador</h2>
                    <p class="text-sm text-zinc-500">Información laboral y documentos de soporte.</p>
                </header>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    @component('components.file', [
                        'label' => 'Contrato',
                        'id' => 'contrato',
                        'name' => 'contratoArchivo',
                        'hint' => 'PDF, JPG o PNG. Máximo 5 MB.',
                        'required' => true,
                    ])
                    @endcomponent

                    @component('components.file', [
                        'label' => 'Notificación',
                        'id' => 'notificacion',
                        'name' => 'notacionAchivo',
                        'hint' => 'PDF, JPG o PNG. Máximo 5 MB.',
                        'required' => true,
                    ])
                    @endcomponent

                    <div class="space-y-1.5">
                        <label
                            for="fechaing3"
                            class="block text-sm font-medium text-zinc-900">
                            Fecha Ingreso
                        </label>
                        <div class="relative">
                            <input
                                id="fechaing3"
                                type="text"
                                placeholder="dd-mm-yyyy"
                                autocomplete="off"
                                required
                                class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2.5 pl-3 pr-10 text-sm text-black outline-none transition duration-200 focus:bg-white focus:ring-2 focus:ring-offset-2 focus:border-black focus:ring-black"
                            />
                            <input
                                type="hidden"
                                id="fechaing3_iso"
                                name="fechaing"
                                value=""
                            />
                        </div>
                        <p id="fechaing3-error"
                           class="input-error-msg hidden text-xs font-medium text-red-600 mt-1 transition-all duration-200"
                           role="alert">
                        </p>
                    </div>

                    <div class="grid grid-cols-[7.5rem_1fr] gap-3">
                        @component('components.select', [
                            'label' => 'N.Telefono',
                            'id' => 'linea',
                            'name' => 'linea',
                            'placeholder' => 'Línea',
                            'required' => true,
                            'options' => [
                                '0412' => '0412',
                                '0414' => '0414',
                                '0416' => '0416',
                                '0422' => '0422',
                                '0424' => '0424',
                                '0426' => '0426',
                            ],
                        ])
                        @endcomponent

                        @component('components.input', [
                            'label' => 'Número',
                            'id' => 'telefono',
                            'name' => 'telefono',
                            'placeholder' => '1234567',
                            'maxlength' => 7,
                            'leftIcon' => 'phone',
                            'required' => true,
                        ])
                        @endcomponent
                    </div>

                    @component('components.select', [
                        'label' => 'Estatus',
                        'id' => 'estatus',
                        'name' => 'estatus',
                        'required' => true,
                        'options' => $estatus ?? [],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Cargo',
                        'id' => 'cargo',
                        'name' => 'cargo',
                        'required' => true,
                        'search' => true,
                        'options' => $cargos ?? [],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Departamento',
                        'id' => 'departamento',
                        'name' => 'departamento',
                        'required' => true,
                        'search' => true,
                        'options' => $departamentos ?? [],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Dependencia',
                        'id' => 'dependencia',
                        'name' => 'dependencia',
                        'required' => true,
                        'search' => true,
                        'options' => $dependencias ?? [],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Nivel Academico',
                        'id' => 'academico',
                        'name' => 'nivelAcademico',
                        'required' => true,
                        'options' => $niveles_academicos ?? [],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Carrera',
                        'id' => 'carrera',
                        'name' => 'carrera',
                        'search' => true,
                        'options' => $carreras ?? [],
                    ])
                    @endcomponent
                </div>
            </section>
        </div>

        {{-- Navegación entre pasos --}}
        <footer class="flex flex-col-reverse items-center gap-3 sm:flex-row sm:justify-between">
            <button type="button" data-step-prev
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-900 transition-all hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] sm:w-auto">
                <i data-lucide="arrow-left" class="h-4 w-4" aria-hidden="true"></i>
                <span>Anterior</span>
            </button>

            <span data-step-counter class="text-sm text-zinc-500" aria-live="polite"></span>

            <button type="button" data-step-next
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-marca px-4 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] sm:w-auto">
                <span data-step-next-label>Siguiente</span>
                <i data-lucide="arrow-right" class="h-4 w-4" aria-hidden="true"></i>
            </button>
        </footer>
    </form>
@endsection

@push('scripts')
    {{-- CARGAR SCRIPTS DE JS --}}
    <script type="module" src="/js/registroEmpleado.js"></script>
@endpush
