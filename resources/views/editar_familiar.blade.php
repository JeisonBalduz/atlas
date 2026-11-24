@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Editar Familiar') {{-- TÍTULO DE LA PÁGINA --}}

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 xl:px-12 2xl:px-16 pb-12 pt-6 bg-slate-50') {{-- CSS DE LA ETIQUETA MAIN --}}

@section('menu_lateral'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.menu_lateral'){{-- CARGAR menu_lateral  --}} 
@endsection

@section('header'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.header'){{-- CARGAR HEADER  --}} 
@endsection

@section('contenido') {{-- CARGAR CONTENIDO PRINCIPAL --}}
<form id="formulario-editar-familiar" data-familia-id="{{ $familiar->familia_id ?? '' }}" enctype="multipart/form-data" class="w-full max-w-none mx-auto grid grid-cols-1 md:grid-cols-12 gap-6 atlas-vista-entrada" novalidate>

    {{-- Card 1 (Bento Hero Oscura): Cabecera con retorno al listado de familiares --}}
    <header class="col-span-full rounded-3xl border border-zinc-900 bg-zinc-950 p-6 sm:p-7 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1.5">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-300">
                <i data-lucide="heart-handshake" class="h-3.5 w-3.5" aria-hidden="true"></i>
                <span>Actualización de Carga Familiar</span>
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Editar Familiar</h1>
            <p class="text-sm text-zinc-400">Modifica los datos personales, parentesco y registro de acta civil del familiar.</p>
        </div>

        <a
            href="/empleado/registros/familiar"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-medium text-white transition-all hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-marca-400 focus:ring-offset-2 focus:ring-offset-zinc-950"
        >
            <i data-lucide="arrow-left" class="h-4 w-4" aria-hidden="true"></i>
            <span>Volver a Familiares</span>
        </a>
    </header>

    @component('components.alerta_formulario')
    @endcomponent

    {{-- Card 2 (Bento Marca): Tarjeta resumen del empleado titular --}}
    <section class="col-span-full md:col-span-4 rounded-3xl border border-marca bg-marca p-6 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between space-y-5">
        <header class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-marca-200">Vínculo Titular</p>
                <h2 class="mt-1 text-lg font-semibold text-white">Empleado Responsable</h2>
                <p class="mt-0.5 text-xs text-marca-100">Trabajador al que pertenece esta carga familiar.</p>
            </div>
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-marca-100">
                <i data-lucide="user-check" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </header>

        <article class="rounded-2xl border border-white/15 bg-white/10 p-4 space-y-2 text-sm">
            <p id="editar-titular-nombre" class="text-base font-bold text-white">{{ $titular->nombre ?? '' }}</p>
            <p id="editar-titular-cedula" class="text-xs font-medium text-marca-100">{{ $titular->cedula ?? '' }}</p>
            <p id="editar-titular-cargo" class="text-xs text-marca-200">{{ $titular->cargo ?? '' }}</p>
        </article>
    </section>

    {{-- Card 3 (Bento Blanca 8 Columnas): Formulario de datos del familiar --}}
    <section class="col-span-full md:col-span-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
        <header>
            <h2 class="text-lg font-semibold text-zinc-900">Datos del Familiar y Acta</h2>
            <p class="text-sm text-zinc-500">Información personal, parentesco y datos de filiación registrados.</p>
        </header>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @component('components.select', ['label' => 'Nacionalidad', 'id' => 'nacionalidad', 'name' => 'nacionalidad', 'required' => true, 'options' => ['V' => 'V', 'E' => 'E'], 'selected' => $persona->nacionalidad ?? '']) @endcomponent
            @component('components.input', ['label' => 'Cédula', 'id' => 'cedula', 'name' => 'cedula', 'value' => $persona->cedula ?? '', 'maxlength' => 9, 'leftIcon' => 'id-card', 'required' => true]) @endcomponent

            @component('components.input', ['label' => 'Primer nombre', 'id' => 'primer_nombre', 'name' => 'primer_nombre', 'value' => $persona->primer_nombre ?? '', 'maxlength' => 100, 'required' => true]) @endcomponent
            @component('components.input', ['label' => 'Segundo nombre', 'id' => 'segundo_nombre', 'name' => 'segundo_nombre', 'value' => $persona->segundo_nombre ?? '', 'maxlength' => 100, 'required' => true]) @endcomponent
            @component('components.input', ['label' => 'Primer apellido', 'id' => 'primer_apellido', 'name' => 'primer_apellido', 'value' => $persona->primer_apellido ?? '', 'maxlength' => 100, 'required' => true]) @endcomponent
            @component('components.input', ['label' => 'Segundo apellido', 'id' => 'segundo_apellido', 'name' => 'segundo_apellido', 'value' => $persona->segundo_apellido ?? '', 'maxlength' => 100, 'required' => true]) @endcomponent

            @component('components.input', ['label' => 'Fecha de nacimiento', 'id' => 'fecha_nacimiento', 'name' => 'fecha_nacimiento', 'value' => isset($persona->fecha_nacimiento) ? $persona->fecha_nacimiento->format('Y-m-d') : '', 'type' => 'date', 'required' => true]) @endcomponent
            @component('components.select', ['label' => 'Género', 'id' => 'genero', 'name' => 'genero', 'required' => true, 'options' => $generos ?? ['Masculino' => 'Masculino', 'Femenino' => 'Femenino'], 'selected' => $genero_actual ?? ($persona->genero ?? '')]) @endcomponent

            @component('components.select', ['label' => 'Parentesco', 'id' => 'parentesco_id', 'name' => 'parentesco_id', 'required' => true, 'search' => true, 'options' => $parentescos ?? [], 'selected' => $familiar->parentesco_id ?? '']) @endcomponent
            <div class="grid grid-cols-2 gap-3">
                @component('components.input', ['label' => 'Tomo', 'id' => 'tomo', 'name' => 'tomo', 'value' => $familiar->tomo ?? '', 'maxlength' => 6, 'required' => true]) @endcomponent
                @component('components.input', ['label' => 'Folio', 'id' => 'folio', 'name' => 'folio', 'value' => $familiar->folio ?? '', 'maxlength' => 6, 'required' => true]) @endcomponent
            </div>
        </div>
    </section>

    {{-- Card 4 (Bento Blanca 12 Columnas): Expediente Documental del Familiar --}}
    <section class="col-span-full rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
        <header class="border-b border-slate-200 pb-4">
            <h2 class="text-lg font-semibold text-zinc-900">Documentos del Familiar</h2>
            <p class="text-sm text-zinc-500">Consulta los documentos cargados para este familiar y adjunta los faltantes según el Contrato Colectivo.</p>
        </header>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach(($recaudos ?? []) as $rec)
                @component('components.tarjeta_recaudo', [
                    'rec' => $rec,
                    'prefijoId' => 'doc-fam-edit',
                    'claseInput' => 'atlas-doc-fam-input',
                ])
                @endcomponent
            @endforeach
        </div>

        <footer class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
            <a
                href="/empleado/registros/familiar"
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
