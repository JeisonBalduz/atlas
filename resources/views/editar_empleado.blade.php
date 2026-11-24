@extends('layouts.app')

@section('titulo', 'Editar Empleado')

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 xl:px-12 2xl:px-16 pb-12 pt-6 bg-slate-50')

@section('menu_lateral')
    @include('layouts.menu_lateral')
@endsection

@section('header')
    @include('layouts.header')
@endsection

@section('contenido')
    @php
        $resumenDoc = $resumen_recaudos ?? [
            'total' => count($recaudos ?? []),
            'cargados_total' => 0,
            'obligatorios_total' => 0,
            'obligatorios_cargados' => 0,
            'opcionales_total' => 0,
            'opcionales_cargados' => 0,
            'completos' => false,
        ];
        $listaTelefonos = $telefonos ?? [['telefono_id' => null, 'numero_telefono' => '', 'emergencia' => false]];
    @endphp

    <form id="formulario-editar-empleado"
        data-empleado-id="{{ $empleado->empleado_id ?? '' }}"
        enctype="multipart/form-data"
        class="w-full max-w-none mx-auto grid grid-cols-1 md:grid-cols-12 gap-6 atlas-vista-entrada"
        novalidate>

        {{-- Card 1 (Bento Hero Oscura): Cabecera con retorno al listado de empleados --}}
        <header class="col-span-full lg:col-span-8 rounded-3xl border border-zinc-900 bg-zinc-950 p-6 sm:p-7 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div class="space-y-1.5">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-300">
                    <i data-lucide="user-cog" class="h-3.5 w-3.5" aria-hidden="true"></i>
                    <span>Actualización de Expediente</span>
                </span>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Editar Empleado</h1>
                <p id="subtitulo-editar-empleado" class="text-sm text-zinc-400">
                    Actualiza la información personal, ubicación, teléfonos y recaudos vigentes del trabajador.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full sm:w-auto shrink-0">
                <button type="button"
                    id="btn-ver-familiares-empleado"
                    data-empleado-id="{{ $empleado->empleado_id ?? '' }}"
                    data-codigo-empleado="{{ $empleado->codigo_empleado ?? '' }}"
                    data-cedula-empleado="{{ ($persona->nacionalidad ?? 'V') . '-' . ($persona->cedula ?? '') }}"
                    data-nombre-empleado="{{ trim(($persona->primer_nombre ?? '') . ' ' . ($persona->segundo_nombre ?? '') . ' ' . ($persona->primer_apellido ?? '') . ' ' . ($persona->segundo_apellido ?? '')) }}"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-4 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-500 focus:outline-none focus:ring-2 focus:ring-marca-400 focus:ring-offset-2 focus:ring-offset-zinc-950 cursor-pointer">
                    <i data-lucide="heart-handshake" class="h-4 w-4" aria-hidden="true"></i>
                    <span>Familiares</span>
                </button>

                <a href="/empleado/registros"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-medium text-white transition-all hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-marca-400 focus:ring-offset-2 focus:ring-offset-zinc-950">
                    <i data-lucide="arrow-left" class="h-4 w-4" aria-hidden="true"></i>
                    <span>Volver a Empleados</span>
                </a>
            </div>
        </header>

        {{-- Card 2 (Bento Marca): Resumen de identificación del trabajador --}}
        <aside class="col-span-full lg:col-span-4 rounded-3xl border border-marca bg-marca p-6 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-marca-200">Identificación en Nómina</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-marca-100">
                    <i data-lucide="badge-check" class="h-5 w-5" aria-hidden="true"></i>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-end justify-between gap-2">
                <div>
                    <p class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                        {{ $empleado->codigo_empleado ?? 'SIN-COD' }}
                    </p>
                    <p class="mt-1 text-xs font-medium text-marca-100">
                        Cédula: {{ ($persona->nacionalidad ?? 'V') . '-' . ($persona->cedula ?? '') }}
                    </p>
                </div>
                <span id="badge-estatus-nomina-header"
                    class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold text-white">
                    @if (((int) ($empleado->estatus_actual ?? 1)) === 3)
                        Pendiente de Documentos
                    @elseif (((int) ($empleado->estatus_actual ?? 1)) === 1)
                        Activo en Nómina
                    @else
                        Inactivo
                    @endif
                </span>
            </div>
        </aside>

        @component('components.alerta_formulario')
        @endcomponent

        {{-- Sección 1: Datos Personales (PersonasModel) --}}
        <section class="col-span-full md:col-span-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
            <header>
                <h2 class="text-lg font-semibold text-zinc-900">Datos Personales</h2>
                <p class="text-sm text-zinc-500">Identificación civil del trabajador (impacta los documentos requeridos).</p>
            </header>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @component('components.input', [
                    'label' => 'Primer nombre',
                    'id' => 'primer_nombre',
                    'name' => 'primer_nombre',
                    'value' => $persona->primer_nombre ?? '',
                    'maxlength' => 100,
                    'required' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Segundo nombre',
                    'id' => 'segundo_nombre',
                    'name' => 'segundo_nombre',
                    'value' => $persona->segundo_nombre ?? '',
                    'maxlength' => 100,
                    'required' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Primer apellido',
                    'id' => 'primer_apellido',
                    'name' => 'primer_apellido',
                    'value' => $persona->primer_apellido ?? '',
                    'maxlength' => 100,
                    'required' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Segundo apellido',
                    'id' => 'segundo_apellido',
                    'name' => 'segundo_apellido',
                    'value' => $persona->segundo_apellido ?? '',
                    'maxlength' => 100,
                    'required' => true,
                ])
                @endcomponent

                @component('components.select', [
                    'label' => 'Nacionalidad',
                    'id' => 'nacionalidad',
                    'name' => 'nacionalidad',
                    'required' => true,
                    'options' => ['V' => 'V', 'E' => 'E'],
                    'selected' => $persona->nacionalidad ?? '',
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Cédula',
                    'id' => 'cedula',
                    'name' => 'cedula',
                    'value' => $persona->cedula ?? '',
                    'maxlength' => 9,
                    'leftIcon' => 'id-card',
                    'required' => true,
                ])
                @endcomponent

                @component('components.input', [
                    'label' => 'Fecha de nacimiento',
                    'id' => 'fecha_nacimiento',
                    'name' => 'fecha_nacimiento',
                    'value' => isset($persona->fecha_nacimiento) ? $persona->fecha_nacimiento->format('Y-m-d') : '',
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
                    'selected' => $persona->genero ?? '',
                ])
                @endcomponent
                <div class="sm:col-span-2">
                    @component('components.select', [
                        'label' => 'Estado civil',
                        'id' => 'estado_civil_id',
                        'name' => 'estado_civil_id',
                        'required' => true,
                        'options' => $estados_civiles ?? [],
                        'selected' => $persona->estado_civil_id ?? '',
                    ])
                    @endcomponent
                </div>
            </div>
        </section>

        {{-- Sección 2: Datos Laborales (EmpleadosModel — solo catálogos activos) --}}
        <section class="col-span-full md:col-span-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
            <header>
                <h2 class="text-lg font-semibold text-zinc-900">Datos Laborales</h2>
                <p class="text-sm text-zinc-500">Ubicación administrativa, cargo y formación académica activa.</p>
            </header>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @component('components.input', [
                    'label' => 'Código de empleado',
                    'id' => 'codigo_empleado',
                    'name' => 'codigo_empleado',
                    'value' => $empleado->codigo_empleado ?? '',
                    'maxlength' => 10,
                    'readonly' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Correo institucional',
                    'id' => 'correo',
                    'name' => 'correo',
                    'value' => $empleado->correo ?? '',
                    'type' => 'email',
                    'leftIcon' => 'mail',
                    'maxlength' => 255,
                ])
                @endcomponent

                @component('components.select', [
                    'label' => 'Dependencia',
                    'id' => 'dependencia_id',
                    'name' => 'dependencia_id',
                    'required' => true,
                    'search' => true,
                    'options' => $dependencias ?? [],
                    'selected' => $empleado->dependencia_id ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Departamento',
                    'id' => 'departamento_id',
                    'name' => 'departamento_id',
                    'required' => true,
                    'search' => true,
                    'options' => $departamentos ?? [],
                    'selected' => $empleado->departamento_id ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Cargo',
                    'id' => 'cargo_id',
                    'name' => 'cargo_id',
                    'required' => true,
                    'search' => true,
                    'options' => $cargos ?? [],
                    'selected' => $empleado->cargo_id ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Estatus laboral',
                    'id' => 'estatus_id',
                    'name' => 'estatus_id',
                    'required' => true,
                    'options' => $estatus ?? [],
                    'selected' => $empleado->estatus_id ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Nivel académico',
                    'id' => 'nivel_academico_id',
                    'name' => 'nivel_academico_id',
                    'required' => true,
                    'options' => $niveles_academicos ?? [],
                    'selected' => $empleado->nivel_academico_id ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Carrera',
                    'id' => 'carrera_id',
                    'name' => 'carrera_id',
                    'search' => true,
                    'options' => $carreras ?? [],
                    'selected' => $empleado->carrera_id ?? '',
                ])
                @endcomponent

                <div class="sm:col-span-2">
                    @component('components.select', [
                        'label' => 'Estatus actual en nómina',
                        'id' => 'estatus_actual',
                        'name' => 'estatus_actual',
                        'required' => true,
                        'options' => ['1' => 'Activo (1)', '3' => 'Pendiente (3)', '0' => 'Inactivo (0)'],
                        'selected' => (string) ($empleado->estatus_actual ?? '1'),
                    ])
                    @endcomponent
                </div>
            </div>
        </section>

        {{-- Sección 3: Dirección de Habitación (DireccionModel) --}}
        <section class="col-span-full md:col-span-7 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
            <header>
                <h2 class="text-lg font-semibold text-zinc-900">Dirección de Habitación</h2>
                <p class="text-sm text-zinc-500">Ubicación geográfica con estados, municipios y parroquias activos.</p>
            </header>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @component('components.select', [
                    'label' => 'Estado',
                    'id' => 'estado',
                    'name' => 'estado',
                    'search' => true,
                    'options' => $estados ?? [],
                    'selected' => $estado_seleccionado ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Municipio',
                    'id' => 'municipio',
                    'name' => 'municipio',
                    'search' => true,
                    'options' => $municipios ?? [],
                    'selected' => $municipio_seleccionado ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Parroquia',
                    'id' => 'parroquia_id',
                    'name' => 'parroquia_id',
                    'required' => true,
                    'search' => true,
                    'options' => $parroquias ?? [],
                    'selected' => $direccion->parroquia_id ?? '',
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Tipo de vivienda',
                    'id' => 'vivienda',
                    'name' => 'vivienda',
                    'value' => $direccion->vivienda ?? '',
                    'maxlength' => 30,
                    'required' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Calle / Avenida',
                    'id' => 'calle',
                    'name' => 'calle',
                    'value' => $direccion->calle ?? '',
                    'maxlength' => 30,
                    'required' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Urbanización / Sector',
                    'id' => 'nombre_urb',
                    'name' => 'nombre_urb',
                    'value' => $direccion->nombre_urb ?? '',
                    'maxlength' => 100,
                    'required' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Piso / Edificio',
                    'id' => 'piso_edificio',
                    'name' => 'piso_edificio',
                    'value' => $direccion->piso_edificio ?? '',
                    'maxlength' => 10,
                    'required' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Número de vivienda',
                    'id' => 'numero_vivienda',
                    'name' => 'numero_vivienda',
                    'value' => $direccion->numero_vivienda ?? '',
                    'maxlength' => 10,
                    'required' => true,
                ])
                @endcomponent
                <div class="sm:col-span-2">
                    @component('components.input', [
                        'label' => 'Punto de referencia / Descripción',
                        'id' => 'descripcion',
                        'name' => 'descripcion',
                        'value' => $direccion->descripcion ?? '',
                        'maxlength' => 255,
                    ])
                    @endcomponent
                </div>
            </div>
        </section>

        {{-- Sección 4: Múltiples Teléfonos de Contacto + Único Teléfono de Emergencia --}}
        <section class="col-span-full md:col-span-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between gap-5">
            <div class="space-y-4">
                <header class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900">Teléfonos de Contacto</h2>
                        <p class="text-sm text-zinc-500">Agrega uno o varios números; solo uno puede ser de emergencia.</p>
                    </div>
                    <button type="button"
                        id="btn-agregar-telefono"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-marca px-3.5 py-2 text-xs font-semibold text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 cursor-pointer">
                        <i data-lucide="plus" class="h-3.5 w-3.5" aria-hidden="true"></i>
                        <span>Agregar teléfono</span>
                    </button>
                </header>

                <div id="lista-telefonos-empleado" class="space-y-3">
                    @foreach ($listaTelefonos as $idx => $tel)
                        @php
                            $esEmergencia = !empty($tel['emergencia']);
                        @endphp
                        <div data-fila-telefono class="rounded-2xl border {{ $esEmergencia ? 'border-marca-200 bg-marca-50/40' : 'border-slate-200 bg-slate-50/70' }} p-3.5 space-y-2.5 transition-colors">
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <i data-lucide="phone" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text"
                                        name="telefonos_numero[]"
                                        value="{{ $tel['numero_telefono'] ?? '' }}"
                                        maxlength="20"
                                        placeholder="Ej: 0412-1234567"
                                        class="input-telefono-empleado w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm text-zinc-900 outline-none transition focus:border-black focus:ring-2 focus:ring-black focus:ring-offset-1" />
                                </div>
                                <button type="button"
                                    data-eliminar-telefono
                                    title="Eliminar teléfono"
                                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-700 transition-colors hover:bg-red-100 cursor-pointer">
                                    <i data-lucide="trash-2" class="h-4 w-4" aria-hidden="true"></i>
                                </button>
                            </div>

                            <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 cursor-pointer select-none">
                                <span class="text-xs font-medium text-zinc-700">Designar como único contacto de emergencia</span>
                                <input type="hidden" name="telefonos_emergencia[]" value="{{ $esEmergencia ? '1' : '0' }}" data-hidden-emergencia />
                                <input type="checkbox"
                                    data-check-emergencia
                                    {{ $esEmergencia ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-slate-300 text-marca accent-marca focus:ring-2 focus:ring-marca-500 cursor-pointer" />
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <p class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-zinc-500">
                Al marcar un número como contacto de emergencia, cualquier otro teléfono quedará automáticamente como línea personal.
            </p>
        </section>

        {{-- Sección 5: Expediente Documental del Trabajador (Evaluación dinámica + Vigencia) --}}
        <section class="col-span-full rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
            <header class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between border-b border-slate-200 pb-4">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900">Expediente Documental del Trabajador</h2>
                    <p class="text-sm text-zinc-500">
                        Evaluación automática según cargo, nivel académico y estado civil (Contrato Colectivo INCES).
                        Al subir un archivo nuevo de un documento existente, el anterior pasa a <strong>No vigente</strong> y el nuevo queda <strong>Vigente</strong>.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <span id="badge-contador-obligatorios"
                        class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold {{ $resumenDoc['completos'] ? 'bg-green-400/20 text-green-800' : 'bg-yellow-400/20 text-yellow-900' }}">
                        <i data-lucide="{{ $resumenDoc['completos'] ? 'check-circle-2' : 'file-warning' }}" class="h-3.5 w-3.5" aria-hidden="true"></i>
                        <span id="texto-contador-obligatorios">Obligatorios vigentes: {{ $resumenDoc['obligatorios_cargados'] }} / {{ $resumenDoc['obligatorios_total'] }}</span>
                    </span>

                    <span id="badge-contador-totales"
                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3.5 py-1.5 text-xs font-semibold text-zinc-700">
                        <i data-lucide="folder-check" class="h-3.5 w-3.5" aria-hidden="true"></i>
                        <span id="texto-contador-totales">Cargados totales: {{ $resumenDoc['cargados_total'] }} / {{ $resumenDoc['total'] }}</span>
                    </span>
                </div>
            </header>

            <div id="contenedor-recaudos-empleado" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($recaudos ?? [] as $rec)
                    @component('components.tarjeta_recaudo', [
                        'rec' => $rec,
                        'prefijoId' => 'doc-emp',
                        'claseInput' => 'atlas-doc-input',
                        'subidaDirecta' => true,
                    ])
                    @endcomponent
                @endforeach
            </div>

            <footer class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <a href="/empleado/registros"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-zinc-900 transition-all hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2">
                    Cancelar
                </a>
                <button type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] cursor-pointer">
                    <i data-lucide="save" class="h-4 w-4" aria-hidden="true"></i>
                    <span>Actualizar Empleado</span>
                </button>
            </footer>
        </section>
    </form>

    {{-- Modal reutilizado de carga familiar (idéntico a registros_empleados) --}}
    @component('components.modal_detalle', [
        'id' => 'modal-familiares-empleado',
        'titulo' => 'Carga familiar del trabajador',
        'subtitulo' => 'Familiares registrados y acciones rápidas.',
        'tamano' => 'lg',
    ])
    @endcomponent
@endsection

@push('scripts')
    <script type="module" src="/js/editar_empleado.js"></script>
@endpush
