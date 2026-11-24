@extends('layouts.app')

@section('titulo', 'Roles y Permisos')

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
@php
    $resSeg = $resumen_seguridad ?? [
        'total_usuarios' => 0,
        'activos' => 0,
        'inactivos' => 0,
        'total_roles' => 0,
        'total_permisos' => 0,
    ];
@endphp
<div class="w-full max-w-none mx-auto grid grid-cols-1 md:grid-cols-12 gap-6 atlas-vista-entrada">

    {{-- Card 1 (Bento Hero Oscura): Cabecera de Roles y Permisos --}}
    <header class="col-span-full lg:col-span-6 rounded-3xl border border-zinc-900 bg-zinc-950 p-6 sm:p-7 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between gap-4">
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-300">
                <i data-lucide="shield-check" class="h-3.5 w-3.5" aria-hidden="true"></i>
                <span>Matriz RBAC del Sistema</span>
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Roles y Permisos</h1>
            <p class="text-sm text-zinc-400">Administra los roles institucionales y define qué permisos puede ejecutar cada perfil.</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-t border-white/10 pt-4">
            <a href="/usuarios"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-medium text-white transition-all hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-marca-400 focus:ring-offset-2 focus:ring-offset-zinc-950">
                <i data-lucide="arrow-left" class="h-4 w-4" aria-hidden="true"></i>
                <span>Ir a Usuarios</span>
            </a>

            <button
                type="button"
                id="btn-nuevo-rol"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-500 focus:outline-none focus:ring-2 focus:ring-marca-400 focus:ring-offset-2 focus:ring-offset-zinc-950 cursor-pointer"
            >
                <i data-lucide="plus" class="h-4 w-4" aria-hidden="true"></i>
                <span>Nuevo Rol</span>
            </button>
        </div>
    </header>

    {{-- Card 2 (Bento Marca): Roles configurados --}}
    <article class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl border border-marca bg-marca p-6 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-marca-200">Roles Configurados</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-marca-100">
                <i data-lucide="award" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p id="resumen-total-roles" class="text-4xl font-bold tracking-tight text-white">{{ $resSeg['total_roles'] ?? 0 }}</p>
            <p class="mt-2 text-xs font-medium text-marca-100">{{ $resSeg['total_usuarios'] ?? 0 }} cuentas vinculadas a roles</p>
        </div>
    </article>

    {{-- Card 3 (Bento Blanca): Catálogo total de permisos --}}
    <article class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Catálogo de Permisos</span>
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-green-50 border border-green-200 text-green-800">
                <i data-lucide="lock-keyhole" class="h-5 w-5" aria-hidden="true"></i>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-4xl font-bold tracking-tight text-zinc-900">{{ $resSeg['total_permisos'] ?? 0 }}</p>
            <p class="mt-2 text-xs font-medium text-zinc-500">Acciones granulares registradas en BD</p>
        </div>
    </article>

    {{-- Bloque 1: Tabla de Roles (5 columnas) --}}
    @component('components.contenedor_tabla', [
        'colSpan' => 'col-span-full lg:col-span-5',
        'idTitulo' => 'titulo-tabla-roles',
        'icono' => 'shield',
        'titulo' => 'Roles del Sistema',
        'subtitulo' => 'Selecciona un rol para configurar sus permisos.',
    ])
        <table id="tabla-roles" class="w-full text-sm text-left">
            <thead class="text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-slate-50">
                <tr>
                    <th>Rol</th>
                    <th class="text-center">Usuarios</th>
                    <th class="text-center">Permisos</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-zinc-700"></tbody>
        </table>
    @endcomponent

    {{-- Bloque 2: Matriz de permisos del rol seleccionado (7 columnas) --}}
    <section class="col-span-full lg:col-span-7 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between gap-5">
        <form id="formulario-permisos-rol"
            data-catalogo-permisos="{{ htmlspecialchars(json_encode($catalogo_permisos ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') }}"
            class="space-y-5"
            novalidate>
            <header class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
                <div>
                    <h2 id="titulo-matriz-rol" class="text-lg font-semibold text-zinc-900">Permisos del Rol</h2>
                    <p id="subtitulo-matriz-rol" class="text-sm text-zinc-500">Selecciona un rol en la tabla izquierda para editar sus permisos ejecutables.</p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" id="btn-rol-marcar-todos" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-slate-100 cursor-pointer">
                        Todos
                    </button>
                    <button type="button" id="btn-rol-desmarcar-todos" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-zinc-700 hover:bg-slate-100 cursor-pointer">
                        Ninguno
                    </button>
                </div>
            </header>

            @component('components.alerta_formulario')
            @endcomponent

            <div id="contenedor-modulos-permisos" class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[56vh] overflow-y-auto pr-1">
                @foreach ($catalogo_permisos ?? [] as $grupo)
                    <article class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 space-y-2.5">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-600">{{ $grupo['modulo'] }}</h3>
                        <div class="space-y-2">
                            @foreach ($grupo['permisos'] as $perm)
                                <label class="flex items-start gap-2.5 rounded-xl border border-slate-200 bg-white p-2.5 text-xs hover:bg-slate-50 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="permiso_ids[]"
                                        value="{{ $perm['id'] }}"
                                        class="check-permiso-rol mt-0.5 h-4 w-4 rounded border-slate-300 text-marca focus:ring-marca-500"
                                    />
                                    <span class="min-w-0">
                                        <strong class="block text-zinc-900">{{ $perm['codigo'] }}</strong>
                                        <span class="block text-zinc-500">{{ $perm['descripcion'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>

            <footer class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-t border-slate-200 pt-4">
                <label class="flex items-center gap-2.5 text-xs font-medium text-zinc-700 cursor-pointer">
                    <input type="checkbox" id="sincronizar_usuarios" name="sincronizar_usuarios" checked class="h-4 w-4 rounded border-slate-300 text-marca focus:ring-marca-500" />
                    <span>Sincronizar permisos en las cuentas actuales de este rol</span>
                </label>

                <button
                    type="submit"
                    id="btn-guardar-permisos-rol"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] cursor-pointer"
                >
                    <i data-lucide="save" class="h-4 w-4" aria-hidden="true"></i>
                    <span>Guardar Permisos del Rol</span>
                </button>
            </footer>
        </form>
    </section>
</div>

{{-- Modal para crear o renombrar rol --}}
@component('components.modal', [
    'id' => 'modal-formulario-rol',
    'titulo' => 'Nuevo rol',
    'subtitulo' => 'Define el nombre del rol institucional.',
    'tamano' => 'sm',
    'textoCancelar' => 'Cancelar',
    'textoConfirmar' => 'Guardar rol',
    'varianteConfirmar' => 'marca',
])
@endcomponent
@endsection

@push('scripts')
    <script type="module" src="/js/roles_permisos.js"></script>
@endpush
