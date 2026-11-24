@extends('layouts.app')

@section('titulo', 'Home')

@push('styles')
    {{-- ApexCharts --}}
    <script defer src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endpush

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-4 sm:px-6 md:px-10 pb-12 pt-6 bg-slate-50')

@section('menu_lateral')
    @include('layouts.menu_lateral')
@endsection

@section('header')
    @include('layouts.header')
@endsection

@section('contenido')

{{-- BENTO GRID 12 COLUMNAS (ATLAS PATTERN) --}}
<div class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-6 mx-auto max-w-400">
    {{-- CABECERA --}}
    <header class="col-span-full flex flex-col sm:flex-row sm:items-start justify-between gap-4 sm:gap-6 mb-2 sm:mb-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900 cascade-enter">Panel Principal</h1>
            <p class="mt-1 text-sm font-medium text-zinc-500 cascade-enter" style="animation-delay: 0.1s;">
                Bienvenido, {{ $usuario->username ?? 'Usuario' }} · Resumen de Gestión Atlas INCES
            </p>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto cascade-enter" style="animation-delay: 0.2s;">
            <a href="/empleado/registrar/familiar"
                class="w-full sm:w-auto px-5 py-2.5 bg-white border border-zinc-200 text-zinc-700 text-sm font-medium rounded-xl hover:bg-zinc-50 hover:text-zinc-900 hover:border-zinc-300 shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all text-center cursor-pointer focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2">
                Asignar Familiar
            </a>
            <a href="/empleado/registrar"
                class="w-full sm:w-auto px-5 py-2.5 bg-marca text-white text-sm font-medium rounded-xl hover:bg-marca-700 shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2">
                <i data-lucide="plus" class="w-4 h-4 shrink-0"></i>
                <span>Registrar Empleado</span>
            </a>
        </div>
    </header>

    {{-- SUB-GRID DE MÉTRICAS REALES DESDE POSTGRESQL (6 COLUMNAS) --}}
    @php
        $m = $metricas ?? [];
        $activosLista = array_values(array_filter($usuarios_activos ?? [], static fn ($item) => !empty($item['activo'])));
    @endphp
    <div class="col-span-full lg:col-span-6 grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)] border border-zinc-100 flex flex-col justify-between hover:-translate-y-1 transition-all duration-300 cascade-enter">
            <div class="flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 mb-3 rounded-full bg-zinc-50 text-zinc-700">
                <i data-lucide="palmtree" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </div>
            <div>
                <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Vacaciones</p>
                <h4 id="metrica-vacaciones" class="text-2xl sm:text-3xl font-bold text-zinc-900">{{ $m['vacaciones'] ?? 0 }}</h4>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)] border border-zinc-100 flex flex-col justify-between hover:-translate-y-1 transition-all duration-300 cascade-enter">
            <div class="flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 mb-3 rounded-full bg-zinc-50 text-zinc-700">
                <i data-lucide="clock" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </div>
            <div>
                <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Permisos</p>
                <h4 id="metrica-permisos" class="text-2xl sm:text-3xl font-bold text-zinc-900">{{ $m['permisos'] ?? 0 }}</h4>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)] border border-zinc-100 flex flex-col justify-between hover:-translate-y-1 transition-all duration-300 cascade-enter">
            <div class="flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 mb-3 rounded-full bg-zinc-50 text-zinc-700">
                <i data-lucide="stethoscope" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </div>
            <div>
                <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Reposos</p>
                <h4 id="metrica-reposos" class="text-2xl sm:text-3xl font-bold text-zinc-900">{{ $m['reposos'] ?? 0 }}</h4>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)] border border-zinc-100 flex flex-col justify-between hover:-translate-y-1 transition-all duration-300 cascade-enter">
            <div class="flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 mb-3 rounded-full bg-zinc-50 text-zinc-700">
                <i data-lucide="gift" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </div>
            <div>
                <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Beneficios</p>
                <h4 id="metrica-beneficios" class="text-2xl sm:text-3xl font-bold text-zinc-900">{{ $m['beneficios'] ?? 0 }}</h4>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)] border border-zinc-100 flex flex-col justify-between hover:-translate-y-1 transition-all duration-300 cascade-enter">
            <div class="flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 mb-3 rounded-full bg-zinc-50 text-zinc-700">
                <i data-lucide="folder-check" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </div>
            <div>
                <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Expedientes</p>
                <h4 id="metrica-expedientes" class="text-2xl sm:text-3xl font-bold text-zinc-900">{{ $m['expedientes'] ?? 0 }}</h4>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)] border border-zinc-100 flex flex-col justify-between hover:-translate-y-1 transition-all duration-300 cascade-enter">
            <div class="flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 mb-3 rounded-full bg-zinc-50 text-zinc-700">
                <i data-lucide="heart-handshake" class="w-4 h-4 sm:w-5 sm:h-5"></i>
            </div>
            <div>
                <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Familiares</p>
                <h4 id="metrica-familiares" class="text-2xl sm:text-3xl font-bold text-zinc-900">{{ $m['familiares'] ?? 0 }}</h4>
            </div>
        </div>
    </div>

    {{-- GRÁFICO DE ARCHIVOS CARGADOS (6 COLUMNAS — HISTÓRICO REAL DE DOCUMENTOS) --}}
    <div class="col-span-full lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] border border-zinc-100 flex flex-col justify-between hover:-translate-y-1 transition-all duration-300 cascade-enter relative overflow-hidden min-h-75">
        <div class="flex items-center justify-between w-full mb-4">
            <h3 class="text-base sm:text-lg font-bold tracking-tight text-zinc-900">Archivos Cargados</h3>
            <span class="text-xs font-medium text-zinc-500">Últimos 6 meses</span>
        </div>
        <div id="chart" data-serie="{{ htmlspecialchars(json_encode($serie_archivos ?? ['etiquetas' => [], 'valores' => []], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') }}" class="w-full z-10 overflow-hidden"></div>
    </div>

    {{-- BIENVENIDA (8 COLUMNAS) --}}
    <div id="welcome-card" style="animation-delay: 0.5s;" class="col-span-full lg:col-span-8 bg-zinc-950 rounded-3xl border border-zinc-900 shadow-[0_15px_30px_rgba(0,0,0,0.05)] relative overflow-hidden flex flex-col justify-center p-6 sm:p-8 lg:p-10 hover:-translate-y-1 transition-all duration-300 cascade-enter min-h-55">
        <div id="welcome-gradient" class="absolute inset-0 z-0 transition-colors duration-500 bg-linear-to-br from-black to-zinc-950"></div>
        <div id="welcome-img"
            class="hidden sm:block absolute right-0 top-0 w-[40%] h-full opacity-[0.15] z-0 pointer-events-none transition-opacity duration-500 bg-cover bg-center"
            style="background-image: url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop'); mask-image: linear-gradient(to left, black, transparent); -webkit-mask-image: linear-gradient(to left, black, transparent);">
        </div>
        <div class="relative z-10 max-w-2xl">
            <h2 id="welcome-title" class="mb-3 text-2xl sm:text-3xl md:text-4xl font-bold tracking-tight text-white transition-colors duration-300">
                Bienvenido al entorno Atlas, {{ $usuario->username ?? 'Usuario' }}
            </h2>
            <p id="welcome-text" class="text-sm md:text-base font-normal leading-relaxed text-zinc-400 transition-colors duration-300">
                Es un excelente día para impulsar, cuidar y acompañar a tu equipo, construyendo un entorno de bienestar
                donde cada persona se sienta valorada y tenga el apoyo necesario para crecer y dar lo mejor de sí.
            </p>
        </div>
    </div>

    {{-- NÓMINA ACTIVA TOTAL (4 COLUMNAS — DATO REAL DE BD) --}}
    <div class="col-span-full sm:col-span-6 lg:col-span-4 rounded-3xl bg-marca text-white p-6 sm:p-8 border border-marca shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300 cascade-enter relative overflow-hidden"
        style="animation-delay: 0.6s;">
        <div class="absolute top-0 right-0 w-32 h-32 -translate-y-1/2 translate-x-1/4 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-center w-12 h-12 mb-6 rounded-2xl bg-white/10 text-marca-200">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-marca-200">Nómina Activa Total</p>
            <h3 id="metrica-nomina-activa" class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-white">{{ $m['nomina_activa'] ?? 0 }}</h3>
            <p class="mt-4 text-xs font-medium text-marca-100">Personal activo registrado</p>
        </div>
    </div>

    {{-- CUENTAS ACTIVAS (3 COLUMNAS — DATO REAL DE BD) --}}
    <div class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl bg-white border border-zinc-100 shadow-[0_15px_30px_rgba(0,0,0,0.05)] p-6 flex flex-col items-center justify-center text-center hover:-translate-y-1 transition-transform duration-300 cascade-enter">
        <p class="mb-2 text-xs font-semibold tracking-wider uppercase text-zinc-500">Cuentas Activas</p>
        <h3 id="metrica-usuarios-activos" class="mb-4 text-4xl sm:text-5xl font-bold tracking-tight text-zinc-900">{{ $m['usuarios_activos'] ?? 0 }}</h3>
        <div id="inicio-avatares-usuarios" class="flex -space-x-2 sm:-space-x-3">
            @foreach (array_slice($activosLista, 0, 3) as $idx => $ua)
                @php
                    $coloresAvatar = ['bg-marca-50 text-marca', 'bg-sky-50 text-sky-800', 'bg-green-50 text-green-800'];
                @endphp
                <span class="inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 text-xs font-bold border-2 border-white rounded-full {{ $coloresAvatar[$idx % 3] }}">
                    {{ strtoupper(substr($ua['nombre_mostrar'] ?? 'U', 0, 2)) }}
                </span>
            @endforeach
            @if (($m['usuarios_activos'] ?? 0) > 3)
                <div class="flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 text-xs font-bold border-2 border-white rounded-full bg-zinc-100 text-zinc-600">
                    +{{ ($m['usuarios_activos'] ?? 0) - 3 }}
                </div>
            @endif
        </div>
    </div>

    {{-- ASISTENCIA DE HOY (3 COLUMNAS — DATO REAL DE BD) --}}
    <div class="col-span-full sm:col-span-6 lg:col-span-3 rounded-3xl bg-marca text-white p-6 sm:p-8 border border-marca shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300 cascade-enter relative overflow-hidden"
        style="animation-delay: 0.8s;">
        <div class="relative z-10">
            <div class="flex items-center justify-center w-12 h-12 mb-6 rounded-2xl bg-white/10 text-marca-200">
                <i data-lucide="calendar-check-2" class="w-6 h-6"></i>
            </div>
            <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-marca-200">Asistencia de Hoy</p>
            <h3 class="text-4xl sm:text-5xl font-bold tracking-tight text-white">
                <span id="metrica-asistencia-porcentaje">{{ $m['porcentaje_asistencia_hoy'] ?? 0 }}</span><span class="text-2xl sm:text-3xl text-marca-200">%</span>
            </h3>
            <div class="pt-4 mt-4 border-t border-white/10">
                <p id="metrica-asistencia-detalle" class="mb-1 text-sm font-medium text-marca-100">{{ $m['presentes_hoy'] ?? 0 }} / {{ $m['nomina_activa'] ?? 0 }} presentes</p>
                <p id="metrica-ausencias-hoy" class="text-xs text-marca-200">{{ $m['ausencias_hoy'] ?? 0 }} ausencias activas hoy</p>
            </div>
        </div>
    </div>

    {{-- ACTIVIDADES RECIENTES (6 COLUMNAS — CONECTADA A TABLAS REALES DE LA BD) --}}
    <div class="col-span-full lg:col-span-6 rounded-3xl bg-white border border-zinc-100 shadow-[0_15px_30px_rgba(0,0,0,0.05)] p-6 sm:p-8 hover:-translate-y-1 transition-transform duration-300 cascade-enter"
        style="animation-delay: 0.9s;">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base sm:text-lg font-bold tracking-tight text-zinc-900">Actividad Reciente</h3>
            <button type="button" id="btn-ver-bitacora" class="text-xs font-semibold transition-colors text-zinc-500 hover:text-zinc-900 cursor-pointer focus:outline-none focus:underline">
                Ver bitácora
            </button>
        </div>
        <div id="inicio-lista-actividad" class="space-y-5">
            @forelse ($actividades_recientes ?? [] as $act)
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center shrink-0 mt-0.5 text-zinc-700">
                        <i data-lucide="{{ $act['icono'] ?? 'activity' }}" class="w-4 h-4"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-zinc-900">{{ $act['titulo'] }}</p>
                        <p class="text-xs text-zinc-500 font-medium mt-0.5">{{ $act['descripcion'] }}</p>
                        <span class="text-[11px] font-semibold text-zinc-400 mt-1 block">{{ $act['fecha'] ?: 'Reciente' }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-zinc-400">Consultando movimientos recientes en la base de datos...</p>
            @endforelse
        </div>
    </div>

    {{-- TABLA GUÍA DE USUARIOS ACTIVOS (12 COLUMNAS — CONECTADA A public.vw_usuarios) --}}
    <div class="col-span-full bg-white rounded-3xl border border-zinc-100 shadow-[0_15px_30px_rgba(0,0,0,0.05)] overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 sm:px-6 py-4 sm:py-5 border-b border-zinc-100">
            <div>
                <h3 class="text-base font-bold text-zinc-900">Guía de Usuarios Activos</h3>
                <p class="text-xs text-zinc-500 mt-0.5">Cuentas consultadas directamente desde la base de datos</p>
            </div>
            <button type="button" id="btn-ver-usuarios" class="self-start sm:self-auto text-xs font-semibold text-marca-600 hover:text-marca-800 transition-colors cursor-pointer focus:outline-none focus:underline">
                Ver todos
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-zinc-50/50">
                    <tr>
                        <th class="px-5 sm:px-6 py-3.5 sm:py-4">Usuario</th>
                        <th class="px-5 sm:px-6 py-3.5 sm:py-4">Rol</th>
                        <th class="px-5 sm:px-6 py-3.5 sm:py-4">Departamento</th>
                        <th class="px-5 sm:px-6 py-3.5 sm:py-4">Estado</th>
                    </tr>
                </thead>
                <tbody id="inicio-tabla-usuarios" class="divide-y divide-zinc-100 text-zinc-500">
                    @forelse ($usuarios_activos ?? [] as $u)
                        <tr class="transition-colors hover:bg-zinc-50/50">
                            <td class="flex items-center gap-3 px-5 sm:px-6 py-3.5 sm:py-4 font-medium text-zinc-900 whitespace-nowrap">
                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-marca-50 text-xs font-bold text-marca border border-marca-100">
                                    {{ strtoupper(substr($u['nombre_mostrar'] ?? 'U', 0, 2)) }}
                                </span>
                                <div>
                                    <span class="block text-sm font-semibold text-zinc-900">{{ $u['nombre_mostrar'] }}</span>
                                    <span class="block text-xs text-zinc-400">{{ '@' . $u['nombre_usuario'] }}</span>
                                </div>
                            </td>
                            <td class="px-5 sm:px-6 py-3.5 sm:py-4 whitespace-nowrap">{{ $u['rol'] }}</td>
                            <td class="px-5 sm:px-6 py-3.5 sm:py-4 whitespace-nowrap text-zinc-600">{{ $u['departamento'] }}</td>
                            <td class="px-5 sm:px-6 py-3.5 sm:py-4 whitespace-nowrap">
                                @if (!empty($u['activo']))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-800 border border-green-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-800 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inactivo
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-zinc-400">Cargando usuarios desde la base de datos...</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@component('components.modal_detalle', [
    'id' => 'modal-inicio-detalle',
    'titulo' => 'Detalle de Inicio',
    'subtitulo' => 'Información en vivo desde PostgreSQL.',
    'tamano' => 'lg',
])
@endcomponent
@endsection

@push('scripts')
    <script type="module" src="/js/inicio.js"></script>
@endpush
