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

@php
    // Datos de demostración para los selects (reemplazar por datos de la BD)
    $anioActual = (int) date('Y');
    $anios = array_combine(range($anioActual - 16, $anioActual - 80), range($anioActual - 16, $anioActual - 80));
    $meses = [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre',
    ];
    $dias = array_combine(range(1, 31), range(1, 31));
@endphp

@section('contenido') {{-- CARGAR CONTENIDO PRINCIPAL --}}
    <form id="form-registro" class="mx-auto max-w-4xl scroll-mt-6 space-y-6" novalidate>

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

                    

                    @component('components.input', [
                        'label' => 'Edad',
                        'id' => 'edad',
                        'name' => 'edad',
                        'placeholder' => 'Ej: 30',
                        'maxlength' => 2,
                        'required' => true,
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Estado civil',
                        'id' => 'civil',
                        'name' => 'civil',
                        'required' => true,
                        'options' => $estado_civil,
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Genero',
                        'id' => 'genero',
                        'name' => 'genero',
                        'required' => true,
                        'options' => ['M' => 'Masculino', 'F' => 'Femenino'],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Tipo De Discapacidad',
                        'id' => 'tpDiscapacidad',
                        'name' => 'tpDiscapacidad',
                        'required' => true,
                        'search'=> true,
                        'options' => [
                            'ninguna' => 'Ninguna',
                            'visual' => 'Visual',
                            'auditiva' => 'Auditiva',
                            'motora' => 'Motora',
                            'intelectual' => 'Intelectual',
                            'psicosocial' => 'Psicosocial',
                        ],
                    ])
                    @endcomponent

                    @component('components.file', [
                        'label' => 'Partida De Discapacidad',
                        'id' => 'achivoDis',
                        'name' => 'docArchivoDis',
                        'hint' => 'PDF, JPG o PNG. Máximo 5 MB.',
                    ])
                    @endcomponent

                    <fieldset class="md:col-span-2">
                        <legend class="mb-3 text-sm font-semibold text-zinc-900">Fecha de nacimiento</legend>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                            @component('components.select', [
                                'label' => 'Año',
                                'id' => 'ano',
                                'name' => 'ano',
                                'required' => true,
                                'search' => true,
                                'options' => $anios,
                            ])
                            @endcomponent

                            @component('components.select', [
                                'label' => 'Mes',
                                'id' => 'meses',
                                'name' => 'meses',
                                'required' => true,
                                'search' => true,
                                'options' => $meses,
                            ])
                            @endcomponent

                            @component('components.select', [
                                'label' => 'Día',
                                'id' => 'dia',
                                'name' => 'dia',
                                'required' => true,
                                'search' => true,
                                'options' => $dias,
                            ])
                            @endcomponent
                        </div>
                    </fieldset>
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
                        'options' => [
                            'dtto-capital' => 'Distrito Capital',
                            'miranda' => 'Miranda',
                            'aragua' => 'Aragua',
                            'carabobo' => 'Carabobo',
                            'lara' => 'Lara',
                            'zulia' => 'Zulia',
                        ],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Municipio',
                        'id' => 'municipio',
                        'name' => 'municipio',
                        'required' => true,
                        'search' => true,
                        'options' => ['libertador' => 'Libertador', 'sucre' => 'Sucre', 'chacao' => 'Chacao', 'baruta' => 'Baruta'],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Parroquia',
                        'id' => 'parroquia',
                        'name' => 'parroquia',
                        'required' => true,
                        'search' => true,
                        'options' => [
                            'catedral' => 'Catedral',
                            'altagracia' => 'Altagracia',
                            'san-juan' => 'San Juan',
                            'el-recreo' => 'El Recreo',
                        ],
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

                    @component('components.input', [
                        'label' => 'Fecha Ingreso',
                        'id' => 'fechaing3',
                        'name' => 'fechaing',
                        'type' => 'date',
                        'required' => true,
                    ])
                    @endcomponent

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
                        'options' => ['activo' => 'Activo', 'reposo' => 'Reposo', 'vacaciones' => 'Vacaciones', 'jubilado' => 'Jubilado'],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Cargo',
                        'id' => 'cargo',
                        'name' => 'cargo',
                        'required' => true,
                        'options' => [
                            'analista' => 'Analista',
                            'asistente' => 'Asistente',
                            'coordinador' => 'Coordinador',
                            'facilitador' => 'Facilitador',
                            'jefe-division' => 'Jefe de División',
                        ],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Departamento',
                        'id' => 'departamento',
                        'name' => 'departamento',
                        'required' => true,
                        'search' => true,
                        'options' => [
                            'talento-humano' => 'Talento Humano',
                            'tecnologia' => 'Tecnología',
                            'administracion' => 'Administración',
                            'formacion' => 'Formación',
                        ],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Dependencia',
                        'id' => 'dependencia',
                        'name' => 'dependencia',
                        'required' => true,
                        'search' => true,
                        'options' => [
                            'sede-central' => 'Sede Central',
                            'la-romana' => 'Dependencias La Romana',
                            'la-victoria' => 'La Victoria',
                        ],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Nivel Academico',
                        'id' => 'academico',
                        'name' => 'nivelAcademico',
                        'required' => true,
                        'options' => [
                            'bachiller' => 'Bachiller',
                            'tsu' => 'TSU',
                            'universitario' => 'Universitario',
                            'especializacion' => 'Especialización',
                            'maestria' => 'Maestría',
                            'doctorado' => 'Doctorado',
                        ],
                    ])
                    @endcomponent

                    @component('components.select', [
                        'label' => 'Carrera',
                        'id' => 'carrera',
                        'name' => 'carrera',
                        'required' => true,
                        'options' => [],
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
