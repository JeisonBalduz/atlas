@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Editar Familiar') {{-- TÍTULO DE LA PÁGINA --}}

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 pb-12 pt-6 bg-slate-50') {{-- CSS DE LA ETIQUETA MAIN --}}

@section('menu_lateral'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.menu_lateral'){{-- CARGAR menu_lateral  --}} 
@endsection

@section('header'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.header'){{-- CARGAR HEADER  --}} 
@endsection

@section('contenido') {{-- CARGAR CONTENIDO PRINCIPAL --}}
<form id="formulario-editar-familiar" class="mx-auto max-w-5xl grid grid-cols-1 md:grid-cols-12 gap-6" novalidate>

    {{-- Cabecera con retorno al listado de familiares --}}
    <header class="col-span-full flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900">Editar Familiar</h1>
            <p class="mt-1 text-sm text-zinc-500">Modifica los datos personales, parentesco y registro de acta del familiar.</p>
        </div>

        <a
            href="/registros_familiares"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-900 shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
        >
            <i data-lucide="arrow-left" class="h-4 w-4" aria-hidden="true"></i>
            <span>Volver a Familiares</span>
        </a>
    </header>

    {{-- Tarjeta resumen del empleado titular --}}
    <section class="col-span-full md:col-span-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-4">
        <header>
            <h2 class="text-lg font-semibold text-zinc-900">Empleado Titular</h2>
            <p class="text-sm text-zinc-500">Trabajador al que pertenece esta carga familiar.</p>
        </header>

        <article class="rounded-xl border border-marca-200 bg-marca-50 p-4 space-y-2 text-sm">
            <p id="editar-titular-nombre" class="font-semibold text-marca-800"></p>
            <p id="editar-titular-cedula" class="text-xs text-zinc-700"></p>
            <p id="editar-titular-cargo" class="text-xs text-zinc-500"></p>
        </article>
    </section>

    {{-- Formulario de datos del familiar (FamiliaresModel + PersonasModel) --}}
    <section class="col-span-full md:col-span-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
        <header>
            <h2 class="text-lg font-semibold text-zinc-900">Datos del Familiar y Acta</h2>
            <p class="text-sm text-zinc-500">Campos precargados con información de prueba editable.</p>
        </header>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @component('components.select', ['label' => 'Nacionalidad', 'id' => 'nacionalidad', 'name' => 'nacionalidad', 'required' => true, 'options' => ['V' => 'V', 'E' => 'E']]) @endcomponent
            @component('components.input', ['label' => 'Cédula', 'id' => 'cedula', 'name' => 'cedula', 'maxlength' => 9, 'leftIcon' => 'id-card', 'required' => true]) @endcomponent

            @component('components.input', ['label' => 'Primer nombre', 'id' => 'primer_nombre', 'name' => 'primer_nombre', 'maxlength' => 100, 'required' => true]) @endcomponent
            @component('components.input', ['label' => 'Segundo nombre', 'id' => 'segundo_nombre', 'name' => 'segundo_nombre', 'maxlength' => 100, 'required' => true]) @endcomponent
            @component('components.input', ['label' => 'Primer apellido', 'id' => 'primer_apellido', 'name' => 'primer_apellido', 'maxlength' => 100, 'required' => true]) @endcomponent
            @component('components.input', ['label' => 'Segundo apellido', 'id' => 'segundo_apellido', 'name' => 'segundo_apellido', 'maxlength' => 100, 'required' => true]) @endcomponent

            @component('components.input', ['label' => 'Fecha de nacimiento', 'id' => 'fecha_nacimiento', 'name' => 'fecha_nacimiento', 'type' => 'date', 'required' => true]) @endcomponent
            @component('components.select', ['label' => 'Género', 'id' => 'genero', 'name' => 'genero', 'required' => true, 'options' => ['Masculino' => 'Masculino', 'Femenino' => 'Femenino']]) @endcomponent

            @component('components.select', ['label' => 'Parentesco', 'id' => 'parentesco_id', 'name' => 'parentesco_id', 'required' => true, 'search' => true, 'options' => []]) @endcomponent
            <div class="grid grid-cols-2 gap-3">
                @component('components.input', ['label' => 'Tomo', 'id' => 'tomo', 'name' => 'tomo', 'maxlength' => 6, 'required' => true]) @endcomponent
                @component('components.input', ['label' => 'Folio', 'id' => 'folio', 'name' => 'folio', 'maxlength' => 6, 'required' => true]) @endcomponent
            </div>
        </div>

        <footer class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
            <a
                href="/registros_familiares"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-zinc-900 transition-all hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
            >
                Cancelar
            </a>
            <button
                type="submit"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] cursor-pointer"
            >
                <i data-lucide="save" class="h-4 w-4" aria-hidden="true"></i>
                <span>Actualizar Familiar</span>
            </button>
        </footer>
    </section>
</form>
@endsection

@push('scripts') {{-- CARGAR SCRIPTS DE JS --}}
    <script type="module" src="/js/editar_familiar.js"></script>
@endpush
