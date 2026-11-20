@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Editar Empleado') {{-- TÍTULO DE LA PÁGINA --}}

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 pb-12 pt-6 bg-slate-50') {{-- CSS DE LA ETIQUETA MAIN --}}

@section('menu_lateral'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.menu_lateral'){{-- CARGAR menu_lateral  --}} 
@endsection

@section('header'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.header'){{-- CARGAR HEADER  --}} 
@endsection

@section('contenido') {{-- CARGAR CONTENIDO PRINCIPAL --}}
<form id="formulario-editar-empleado" class="mx-auto max-w-6xl grid grid-cols-1 md:grid-cols-12 gap-6" novalidate>

    {{-- Cabecera con retorno al listado de empleados --}}
    <header class="col-span-full flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900">Editar Empleado</h1>
            <p id="subtitulo-editar-empleado" class="mt-1 text-sm text-zinc-500">Actualiza la información personal, ubicación, teléfonos y datos laborales.</p>
        </div>

        <a
            href="/registros_empleados"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-900 shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
        >
            <i data-lucide="arrow-left" class="h-4 w-4" aria-hidden="true"></i>
            <span>Volver a Empleados</span>
        </a>
    </header>

    {{-- Sección 1: Datos Personales (PersonasModel) --}}
    <section class="col-span-full md:col-span-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
        <header>
            <h2 class="text-lg font-semibold text-zinc-900">Datos Personales</h2>
            <p class="text-sm text-zinc-500">Identificación civil del trabajador.</p>
        </header>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @component('components.input', ['label' => 'Primer nombre', 'id' => 'primer_nombre', 'name' => 'primer_nombre', 'maxlength' => 100, 'required' => true]) @endcomponent
            @component('components.input', ['label' => 'Segundo nombre', 'id' => 'segundo_nombre', 'name' => 'segundo_nombre', 'maxlength' => 100, 'required' => true]) @endcomponent
            @component('components.input', ['label' => 'Primer apellido', 'id' => 'primer_apellido', 'name' => 'primer_apellido', 'maxlength' => 100, 'required' => true]) @endcomponent
            @component('components.input', ['label' => 'Segundo apellido', 'id' => 'segundo_apellido', 'name' => 'segundo_apellido', 'maxlength' => 100, 'required' => true]) @endcomponent

            @component('components.select', ['label' => 'Nacionalidad', 'id' => 'nacionalidad', 'name' => 'nacionalidad', 'required' => true, 'options' => ['V' => 'V', 'E' => 'E']]) @endcomponent
            @component('components.input', ['label' => 'Cédula', 'id' => 'cedula', 'name' => 'cedula', 'maxlength' => 9, 'leftIcon' => 'id-card', 'required' => true]) @endcomponent

            @component('components.input', ['label' => 'Fecha de nacimiento', 'id' => 'fecha_nacimiento', 'name' => 'fecha_nacimiento', 'type' => 'date', 'required' => true]) @endcomponent
            @component('components.select', ['label' => 'Género', 'id' => 'genero', 'name' => 'genero', 'required' => true, 'options' => ['Masculino' => 'Masculino', 'Femenino' => 'Femenino']]) @endcomponent
            @component('components.select', ['label' => 'Estado civil', 'id' => 'estado_civil_id', 'name' => 'estado_civil_id', 'required' => true, 'options' => []]) @endcomponent
        </div>
    </section>

    {{-- Sección 2: Datos Laborales (EmpleadosModel) --}}
    <section class="col-span-full md:col-span-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
        <header>
            <h2 class="text-lg font-semibold text-zinc-900">Datos Laborales</h2>
            <p class="text-sm text-zinc-500">Ubicación administrativa, cargo y formación académica.</p>
        </header>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @component('components.input', ['label' => 'Código de empleado', 'id' => 'codigo_empleado', 'name' => 'codigo_empleado', 'maxlength' => 10, 'readonly' => true]) @endcomponent
            @component('components.input', ['label' => 'Correo institucional', 'id' => 'correo', 'name' => 'correo', 'type' => 'email', 'leftIcon' => 'mail', 'maxlength' => 255]) @endcomponent

            @component('components.select', ['label' => 'Dependencia', 'id' => 'dependencia_id', 'name' => 'dependencia_id', 'required' => true, 'search' => true, 'options' => []]) @endcomponent
            @component('components.select', ['label' => 'Departamento', 'id' => 'departamento_id', 'name' => 'departamento_id', 'required' => true, 'search' => true, 'options' => []]) @endcomponent
            @component('components.select', ['label' => 'Cargo', 'id' => 'cargo_id', 'name' => 'cargo_id', 'required' => true, 'search' => true, 'options' => []]) @endcomponent
            @component('components.select', ['label' => 'Estatus laboral', 'id' => 'estatus_id', 'name' => 'estatus_id', 'required' => true, 'options' => []]) @endcomponent
            @component('components.select', ['label' => 'Nivel académico', 'id' => 'nivel_academico_id', 'name' => 'nivel_academico_id', 'required' => true, 'options' => []]) @endcomponent
            @component('components.select', ['label' => 'Carrera', 'id' => 'carrera_id', 'name' => 'carrera_id', 'search' => true, 'options' => []]) @endcomponent

            <div class="sm:col-span-2">
                @component('components.select', [
                    'label' => 'Estatus actual en nómina',
                    'id' => 'estatus_actual',
                    'name' => 'estatus_actual',
                    'required' => true,
                    'options' => ['1' => 'Activo (1)', '0' => 'Inactivo (0)'],
                ])
                @endcomponent
            </div>
        </div>
    </section>

    {{-- Sección 3: Dirección de Habitación (DireccionModel) --}}
    <section class="col-span-full md:col-span-7 rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
        <header>
            <h2 class="text-lg font-semibold text-zinc-900">Dirección de Habitación</h2>
            <p class="text-sm text-zinc-500">Campos correspondientes al modelo Direccion.</p>
        </header>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @component('components.select', ['label' => 'Parroquia', 'id' => 'parroquia_id', 'name' => 'parroquia_id', 'required' => true, 'search' => true, 'options' => []]) @endcomponent
            @component('components.input', ['label' => 'Tipo de vivienda', 'id' => 'vivienda', 'name' => 'vivienda', 'maxlength' => 30, 'required' => true]) @endcomponent
            @component('components.input', ['label' => 'Calle / Avenida', 'id' => 'calle', 'name' => 'calle', 'maxlength' => 30, 'required' => true]) @endcomponent
            @component('components.input', ['label' => 'Urbanización / Sector', 'id' => 'nombre_urb', 'name' => 'nombre_urb', 'maxlength' => 100, 'required' => true]) @endcomponent
            @component('components.input', ['label' => 'Piso / Edificio', 'id' => 'piso_edificio', 'name' => 'piso_edificio', 'maxlength' => 10, 'required' => true]) @endcomponent
            @component('components.input', ['label' => 'Número de vivienda', 'id' => 'numero_vivienda', 'name' => 'numero_vivienda', 'maxlength' => 10, 'required' => true]) @endcomponent
            <div class="sm:col-span-2">
                @component('components.input', ['label' => 'Punto de referencia / Descripción', 'id' => 'descripcion', 'name' => 'descripcion', 'maxlength' => 255]) @endcomponent
            </div>
        </div>
    </section>

    {{-- Sección 4: Teléfonos de Contacto (TelefonoPersonasModel) --}}
    <section class="col-span-full md:col-span-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
        <header>
            <h2 class="text-lg font-semibold text-zinc-900">Teléfonos de Contacto</h2>
            <p class="text-sm text-zinc-500">Número personal principal y contacto de emergencia.</p>
        </header>

        <div class="space-y-4">
            @component('components.input', ['label' => 'Teléfono principal', 'id' => 'telefono_principal', 'name' => 'telefono_principal', 'maxlength' => 20, 'leftIcon' => 'phone', 'required' => true]) @endcomponent
            @component('components.input', ['label' => 'Teléfono de emergencia', 'id' => 'telefono_emergencia', 'name' => 'telefono_emergencia', 'maxlength' => 20, 'leftIcon' => 'phone-call']) @endcomponent
        </div>

        <footer class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
            <a
                href="/registros_empleados"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-zinc-900 transition-all hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
            >
                Cancelar
            </a>
            <button
                type="submit"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] cursor-pointer"
            >
                <i data-lucide="save" class="h-4 w-4" aria-hidden="true"></i>
                <span>Actualizar Empleado</span>
            </button>
        </footer>
    </section>
</form>
@endsection

@push('scripts') {{-- CARGAR SCRIPTS DE JS --}}
    <script type="module" src="/js/editar_empleado.js"></script>
@endpush
