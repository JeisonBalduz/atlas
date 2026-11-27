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
<form id="formulario-registrar-familiar"
    enctype="multipart/form-data"
    data-recaudos-parentesco="{{ json_encode($recaudos_por_parentesco ?? [], JSON_UNESCAPED_UNICODE) }}"
    data-recaudo-discapacidad="{{ htmlspecialchars(json_encode($recaudo_discapacidad ?? null, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') }}"
    class="mx-auto max-w-6xl grid grid-cols-1 md:grid-cols-12 gap-6" novalidate>

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

    {{-- Columna izquierda en escritorio (md+): Empleado Titular + 3. Documentos del Familiar; en móvil usa contents para respetar orden 1 -> 2 -> 3 --}}
    <div class="contents md:col-span-5 md:flex md:flex-col md:gap-6 md:self-start">
        {{-- Bloque 1: Selección del empleado titular y tarjeta resumen --}}
        <section class="col-span-full order-1 md:order-0 h-fit rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
            <header>
                <h2 class="text-lg font-semibold text-zinc-900">Empleado Titular</h2>
                <p class="text-sm text-zinc-500">Selecciona al trabajador responsable de la carga familiar.</p>
            </header>

            @component('components.select', [
                'label' => 'Empleado titular',
                'id' => 'empleado_id',
                'name' => 'empleado_id',
                'placeholder' => 'Buscar empleado por nombre o cédula...',
                'required' => true,
                'search' => true,
                'remoteUrl' => '/api/select/empleados?incluir_pendientes=1',
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

        {{-- Bloque 3: Documentos del Familiar según Parentesco (Contrato Colectivo) --}}
        <section class="col-span-full order-3 md:order-0 h-fit rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
            <header>
                <h2 class="text-lg font-semibold text-zinc-900">Documentos del Familiar</h2>
                <p class="text-sm text-zinc-500">Selecciona el parentesco para visualizar los recaudos exigidos por el Contrato Colectivo.</p>
            </header>

            <div id="contenedor-recaudos-familiar" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-1">
                <p class="col-span-full rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-zinc-500">
                    Selecciona un parentesco arriba para cargar los campos de documentos correspondientes.
                </p>
            </div>
        </section>
    </div>

    {{-- Bloque 2: Datos personales del familiar y datos de filiación --}}
    <section class="col-span-full md:col-span-7 order-2 md:order-0 self-start h-fit rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-6">
        <header>
            <h2 class="text-lg font-semibold text-zinc-900">Datos del Familiar y Filiación</h2>
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

            <p id="aviso-cedula-menor" class="hidden sm:col-span-2 rounded-xl border border-sky-200 bg-sky-50 px-3.5 py-2 text-xs font-medium text-sky-800">
                Menor de 9 años: la cédula no es obligatoria. Si la dejas vacía, se generará automáticamente una cédula temporal única usando Tomo y Folio.
            </p>

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

            <div class="space-y-1.5">
                <label
                    for="fecha_nacimiento"
                    class="block text-sm font-medium text-zinc-900">
                    Fecha de nacimiento
                </label>
                <div class="relative">
                    <input
                        id="fecha_nacimiento"
                        type="text"
                        placeholder="dd-mm-yyyy"
                        autocomplete="off"
                        required
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2.5 pl-3 pr-10 text-sm text-black outline-none transition duration-200 focus:bg-white focus:ring-2 focus:ring-offset-2 focus:border-black focus:ring-black"
                    />
                    <input
                        type="hidden"
                        id="fecha_nacimiento_iso"
                        name="fecha_nacimiento"
                        value=""
                    />
                </div>
                <p id="fecha_nacimiento-error"
                   class="input-error-msg hidden text-xs font-medium text-red-600 mt-1 transition-all duration-200"
                   role="alert">
                </p>
            </div>

            <div class="space-y-1.5">
                <label
                    for="edad_familiar"
                    class="block text-sm font-medium text-zinc-900">
                    Edad
                </label>
                <input
                    id="edad_familiar"
                    type="text"
                    readonly
                    placeholder="Calculada automáticamente"
                    aria-describedby="edad_familiar_ayuda"
                    class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2.5 text-sm font-medium text-zinc-700 outline-none cursor-not-allowed"
                />
                <p id="edad_familiar_ayuda" class="text-xs text-zinc-500 mt-1">La edad se calcula con la fecha de nacimiento</p>
            </div>

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

            <div data-bloque-tomo-folio>
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
            </div>

            <div data-bloque-tomo-folio>
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
        </div>

        {{-- Apartado condicional de Discapacidad --}}
        <div class="border-t border-slate-200 pt-5 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-zinc-900">Discapacidad (Opcional)</h3>
                    <p class="text-xs text-zinc-500">Activa este apartado solo si el familiar posee una discapacidad certificada.</p>
                </div>
                <button type="button"
                    id="btn-toggle-discapacidad-familiar"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-4 py-2 text-xs font-semibold text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer">
                    <i data-lucide="accessibility" class="h-4 w-4" aria-hidden="true"></i>
                    <span data-texto-toggle-disc>Asignar discapacidad</span>
                </button>
            </div>

            <input type="hidden" id="tiene_discapacidad" name="tiene_discapacidad" value="0" />

            <div id="panel-discapacidad-familiar" class="hidden rounded-2xl border border-slate-200 bg-slate-50/70 p-4 grid-cols-1 gap-4 sm:grid-cols-2">
                @component('components.select', [
                    'label' => 'Tipo de discapacidad',
                    'id' => 'tipo_discapacidad',
                    'name' => 'tipo_discapacidad',
                    'placeholder' => 'Seleccionar tipo...',
                    'options' => $tipos_discapacidad ?? [],
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Grado de discapacidad',
                    'id' => 'grado_discapacidad_id',
                    'name' => 'grado_discapacidad_id',
                    'placeholder' => 'Seleccionar grado...',
                    'options' => $grados_discapacidad ?? [],
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'N.° de Carnet CONAPDIS',
                    'id' => 'numero_carnet',
                    'name' => 'numero_carnet',
                    'placeholder' => 'Ej. CONAPDIS-123456',
                    'maxlength' => 30,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Entidad emisora',
                    'id' => 'entidad_emisora',
                    'name' => 'entidad_emisora',
                    'value' => 'CONAPDIS',
                    'maxlength' => 100,
                ])
                @endcomponent
                <div class="space-y-1.5">
                    <label
                        for="fecha_emision_carnet"
                        class="block text-sm font-medium text-zinc-900">
                        Fecha de emisión
                    </label>
                    <div class="relative">
                        <input
                            id="fecha_emision_carnet"
                            type="text"
                            placeholder="dd-mm-yyyy"
                            autocomplete="off"
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2.5 pl-3 pr-10 text-sm text-black outline-none transition duration-200 focus:bg-white focus:ring-2 focus:ring-offset-2 focus:border-black focus:ring-black"
                        />
                        <input
                            type="hidden"
                            id="fecha_emision_carnet_iso"
                            name="fecha_emision_carnet"
                            value=""
                        />
                    </div>
                    <p id="fecha_emision_carnet-error"
                       class="input-error-msg hidden text-xs font-medium text-red-600 mt-1 transition-all duration-200"
                       role="alert">
                    </p>
                </div>
                <div class="space-y-1.5">
                    <label
                        for="fecha_vencimiento_carnet"
                        class="block text-sm font-medium text-zinc-900">
                        Fecha de vencimiento
                    </label>
                    <div class="relative">
                        <input
                            id="fecha_vencimiento_carnet"
                            type="text"
                            placeholder="dd-mm-yyyy"
                            autocomplete="off"
                            class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2.5 pl-3 pr-10 text-sm text-black outline-none transition duration-200 focus:bg-white focus:ring-2 focus:ring-offset-2 focus:border-black focus:ring-black"
                        />
                        <input
                            type="hidden"
                            id="fecha_vencimiento_carnet_iso"
                            name="fecha_vencimiento_carnet"
                            value=""
                        />
                    </div>
                    <p id="fecha_vencimiento_carnet-error"
                       class="input-error-msg hidden text-xs font-medium text-red-600 mt-1 transition-all duration-200"
                       role="alert">
                    </p>
                </div>
                <div class="sm:col-span-2 space-y-1.5">
                    <label for="descripcion_discapacidad" class="block text-sm font-medium text-zinc-900">
                        Descripción / Observaciones
                    </label>
                    <textarea id="descripcion_discapacidad"
                        name="descripcion_discapacidad"
                        rows="2"
                        placeholder="Detalles médicos o indicaciones relevantes..."
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none transition duration-200 focus:border-black focus:ring-2 focus:ring-black focus:ring-offset-2"></textarea>
                </div>
            </div>
        </div>

        <footer class="flex justify-end border-t border-slate-200 pt-5">
            <button
                    type="reset"
                    id="btn-limpiar-familiar"
                    class="mr-4 w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-zinc-900 transition-all hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer"
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
