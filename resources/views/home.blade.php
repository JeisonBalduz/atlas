 @extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('title', 'Home') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 pb-12 pt-6 bg-slate-50') {{-- CSS DE LA ETIQUETA MAIN --}}

@section('siderbar'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.siderbar'){{-- CARGAR SIDERBAR  --}} 
@endsection

@section('header'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.header'){{-- CARGAR HEADER  --}} 
@endsection

@section('content') {{-- CARGAR CONTENDIO PRINCIPAL  --}} 

{{-- BENTO GRID --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mx-auto max-w-400">

    {{-- CABECERA --}}
    <div class="col-span-full flex flex-col sm:flex-row sm:items-start justify-between gap-6 mb-4 md:mb-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-marca cascade-enter">{{ $usuario->id }}</h1>
            <p class="mt-1 text-sm font-medium text-zinc-500 cascade-enter" style="animation-delay: 0.1s;">
                Jueves, 12 de Noviembre de 2026
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3 cascade-enter" style="animation-delay: 0.2s;">
            <button type="button"
                class="shadow-xs px-5 py-2.5 bg-white border border-zinc-200 text-zinc-700 text-sm font-bold rounded-xl hover:bg-zinc-50 hover:text-zinc-900 hover:border-zinc-300 transition-all cursor-pointer">
                Asignar Familiar
            </button>
            <button type="button"
                class="shadow-lg shadow-marca-500/20 px-5 py-2.5 hover:-translate-y-1 duration-300  bg-marca-700 text-white text-sm font-bold rounded-xl hover:bg-marca-800 transition-all flex items-center gap-2 cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Registrar Empleado
            </button>
        </div>
    </div>

    {{-- TARJETAS MÉTRICAS SIMPLES--}}
    <div class="col-span-full lg:col-span-2 grid grid-cols-2 sm:grid-cols-3 gap-4">
        {{-- Tarjeta vacaciones --}}
        <div class="bg-white rounded-3xl p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)] border border-zinc-100 flex flex-col justify-center hover:-translate-y-1 transition-transform duration-300 cascade-enter">
            <div class="flex items-center justify-center w-10 h-10 mb-4 rounded-full bg-zinc-50 text-zinc-700">
                <i data-lucide="palmtree" class="w-5 h-5"></i>
            </div>
            <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Vacaciones</p>
            <h4 class="text-3xl font-bold text-zinc-900">42</h4>
        </div>

        {{-- Tarjeta permisos --}}
        <div class="bg-white rounded-3xl p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)] border border-zinc-100 flex flex-col justify-center hover:-translate-y-1 transition-transform duration-300 cascade-enter">
            <div class="flex items-center justify-center w-10 h-10 mb-4 rounded-full bg-zinc-50 text-zinc-700">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Permisos</p>
            <h4 class="text-3xl font-bold text-zinc-900">18</h4>
        </div>

        {{-- Tarjeta reposos --}}
        <div class="bg-white rounded-3xl p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)] border border-zinc-100 flex flex-col justify-center hover:-translate-y-1 transition-transform duration-300 cascade-enter">
            <div class="flex items-center justify-center w-10 h-10 mb-4 rounded-full bg-zinc-50 text-zinc-700">
                <i data-lucide="stethoscope" class="w-5 h-5"></i>
            </div>
            <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Reposos</p>
            <h4 class="text-3xl font-bold text-zinc-900">7</h4>
        </div>

        {{-- Tarjeta beneficios --}}
        <div class="bg-white rounded-3xl p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)] border border-zinc-100 flex flex-col justify-center hover:-translate-y-1 transition-transform duration-300 cascade-enter">
            <div class="flex items-center justify-center w-10 h-10 mb-4 rounded-full bg-zinc-50 text-zinc-700">
                <i data-lucide="gift" class="w-5 h-5"></i>
            </div>
            <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Beneficios</p>
            <h4 class="text-3xl font-bold text-zinc-900">156</h4>
        </div>

        {{-- Tarjeta expedientes --}}
        <div class="bg-white rounded-3xl p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)] border border-zinc-100 flex flex-col justify-center hover:-translate-y-1 transition-transform duration-300 cascade-enter">
            <div class="flex items-center justify-center w-10 h-10 mb-4 rounded-full bg-zinc-50 text-zinc-700">
                <i data-lucide="folder-check" class="w-5 h-5"></i>
            </div>
            <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Expedientes</p>
            <h4 class="text-3xl font-bold text-zinc-900">815</h4>
        </div>

        {{-- Tarjeta carga familiar --}}
        <div class="bg-white rounded-3xl p-5 shadow-[0_15px_30px_rgba(0,0,0,0.05)] border border-zinc-100 flex flex-col justify-center hover:-translate-y-1 transition-transform duration-300 cascade-enter">
            <div class="flex items-center justify-center w-10 h-10 mb-4 rounded-full bg-zinc-50 text-zinc-700">
                <i data-lucide="heart-handshake" class="w-5 h-5"></i>
            </div>
            <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Familiares</p>
            <h4 class="text-3xl font-bold text-zinc-900">312</h4>
        </div>
    </div>

    {{-- GRÁFICO DE ARCHIVOS CARGADOS --}}
    <div class="col-span-full lg:col-span-2 bg-white rounded-3xl p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] border border-zinc-100 flex flex-col hover:-translate-y-1 transition-transform duration-300 cascade-enter relative overflow-hidden">
        <div class="flex items-center justify-between w-full mb-6">
            <h3 class="text-lg font-bold tracking-tight text-zinc-900">Archivos Cargardos</h3>
        </div>
        <div id="chart" class="w-full z-10"></div>
    </div>

    {{-- BIENVENIDA --}}
    <div id="welcome-card" style="animation-delay: 0.5s;" class="col-span-full lg:col-span-3 bg-black rounded-4xl border border-zinc-900 shadow-[0_15px_30px_rgba(0,0,0,0.05)] relative overflow-hidden flex flex-col justify-center p-8 lg:p-10 hover:-translate-y-1 transition-all duration-300 cascade-enter min-h-55">

        {{-- Gragiente negro --}}
        <div id="welcome-gradient"
            class="absolute inset-0 z-0 transition-colors duration-500 bg-linear-to-br from-black to-zinc-950">
        </div>

        {{-- Imagen de Recursos Humanos --}}
        <div id="welcome-img"
            class="absolute right-0 top-0 w-[40%] h-full opacity-[0.15] z-0 pointer-events-none transition-opacity duration-500 bg-cover bg-center"
            style="background-image: url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop'); mask-image: linear-gradient(to left, black, transparent); -webkit-mask-image: linear-gradient(to left, black, transparent);">
        </div>

        {{-- Texto de bienvenida  --}}
        <div class="relative z-10 max-w-2xl">
            <h2 id="welcome-title"
                class="mb-4 text-3xl md:text-4xl font-bold tracking-tight text-white transition-colors duration-300">
                Bienvenido al entorno Atlas,{{' ' . $usuario->username}}
            </h2>
            <p id="welcome-text"
                class="text-sm md:text-base font-medium leading-relaxed text-zinc-400 transition-colors duration-300">
                Es un excelente día para impulsar, cuidar y acompañar a tu equipo, construyendo un entorno de bienestar
                donde cada persona se sienta valorada y tenga el apoyo necesario para crecer y dar lo mejor de sí
            </p>
        </div>
    </div>

    {{-- NÓMINA ACTIVA TOTAL --}}
    <div class="col-span-full sm:col-span-1 rounded-4xl bg-marca-900 text-white p-8 border border-marca-900 shadow-[0_15px_30px_rgba(0,0,0,0.15)] flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300 cascade-enter relative overflow-hidden"
        style="animation-delay: 0.6s;">
        <div class="absolute top-0 right-0 w-32 h-32 -translate-y-1/2 translate-x-1/4 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-center w-12 h-12 mb-6 rounded-2xl bg-marca-900">
                <i data-lucide="users" class="w-6 h-6 text-marca-300"></i>
            </div>
            <p class="mb-1 text-sm font-medium text-white">Nómina Activa Total</p>
            <h3 class="text-6xl font-bold tracking-tight">842</h3>
        </div>
    </div>

    {{-- USUARIOS ACTIVOS --}}
    <div class="col-span-full sm:col-span-1 rounded-3xl bg-white border border-zinc-100 shadow-[0_15px_30px_rgba(0,0,0,0.05)] p-6 flex flex-col items-center justify-center text-center hover:-translate-y-1 transition-transform duration-300 cascade-enter">
        <p class="mb-2 text-sm font-semibold text-zinc-500">En línea</p>
        <h3 class="mb-4 text-5xl font-bold tracking-tight text-zinc-900">24</h3>
        <div class="flex -space-x-3">
            <img class="w-10 h-10 border-2 border-white rounded-full" src="https://i.pravatar.cc/100?img=1" alt="Usuario 1">
            <img class="w-10 h-10 border-2 border-white rounded-full" src="https://i.pravatar.cc/100?img=2" alt="Usuario 2">
            <img class="w-10 h-10 border-2 border-white rounded-full" src="https://i.pravatar.cc/100?img=3" alt="Usuario 3">
            <div class="flex items-center justify-center w-10 h-10 text-xs font-bold border-2 border-white rounded-full bg-zinc-100 text-zinc-600">
                +21
            </div>
        </div>
    </div>

    {{-- ASISTENCIA --}}
    <div class="col-span-full sm:col-span-1 rounded-4xl bg-marca-900 text-white p-8 border border-marca-900 shadow-[0_15px_30px_rgba(0,0,0,0.15)] flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300 cascade-enter relative overflow-hidden"
        style="animation-delay: 0.8s;">
        <div class="relative z-10">
            <div class="flex items-center justify-center w-12 h-12 mb-6 rounded-2xl bg-marca-900">
                <i data-lucide="calendar-check-2" class="w-6 h-6 text-zinc-300"></i>
            </div>
            <p class="mb-1 text-sm font-medium text-zinc-400">Asistencia de Hoy</p>
            <h3 class="text-5xl font-bold tracking-tight">96<span class="text-3xl text-zinc-500">%</span></h3>
            <div class="pt-4 mt-4">
                <p class="mb-1 text-sm font-medium text-zinc-300">808 / 842 empleados presentes</p>
                <p class="text-xs font-medium text-zinc-500">Solo 3 ausencias reportadas hoy.</p>
            </div>
        </div>
    </div>

    {{-- ACTIVIDADES RECIENTES --}}
    <div class="col-span-full lg:col-span-2 rounded-4xl bg-white border border-zinc-100 shadow-[0_15px_30px_rgba(0,0,0,0.05)] p-8 hover:-translate-y-1 transition-transform duration-300 cascade-enter"
        style="animation-delay: 0.9s;">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold tracking-tight text-zinc-900">Actividad Reciente</h3>
            <button type="button" class="text-xs font-semibold transition-colors text-zinc-500 hover:text-black cursor-pointer">
                Ver bitácora
            </button>
        </div>
        <div class="space-y-5">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center shrink-0 mt-0.5 text-zinc-700">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-zinc-900">Ingreso de Personal</p>
                    <p class="text-xs text-zinc-500 font-medium mt-0.5">
                        Ana López fue registrada en el departamento de Operaciones.
                    </p>
                    <span class="text-[10px] font-bold text-zinc-400 mt-1 block">Hace 15 min</span>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center shrink-0 mt-0.5 text-zinc-700">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-zinc-900">Vacaciones Aprobadas</p>
                    <p class="text-xs text-zinc-500 font-medium mt-0.5">
                        Solicitud #402 de Carlos Mendoza fue procesada con éxito.
                    </p>
                    <span class="text-[10px] font-bold text-zinc-400 mt-1 block">Hace 2 horas</span>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLA GUÍA DE USUARIOS ACTIVOS --}}
    <div class="col-span-full bg-white rounded-3xl border border-zinc-100 shadow-[0_15px_30px_rgba(0,0,0,0.05)] overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-zinc-100">
            <h3 class="text-base font-bold text-zinc-900">Guía de Usuarios Activos</h3>
            <button type="button" class="text-xs font-semibold text-blue-500 hover:text-blue-600 cursor-pointer">
                Ver todos
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs font-semibold uppercase text-zinc-500 bg-zinc-50/50">
                    <tr>
                        <th class="px-6 py-4">Usuario</th>
                        <th class="px-6 py-4">Rol</th>
                        <th class="px-6 py-4">Departamento</th>
                        <th class="px-6 py-4">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 text-zinc-500">
                    <tr class="transition-colors hover:bg-zinc-50/50">
                        <td class="flex items-center gap-3 px-6 py-4 font-medium text-zinc-900 whitespace-nowrap">
                            <img class="w-8 h-8 rounded-full shrink-0" src="https://i.pravatar.cc/100?img=4" alt="Roberto Medina">
                            Roberto Medina
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">Analista</td>
                        <td class="px-6 py-4 whitespace-nowrap">Operaciones</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600 border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Conectado
                            </span>
                        </td>
                    </tr>
                    <tr class="transition-colors hover:bg-zinc-50/50">
                        <td class="flex items-center gap-3 px-6 py-4 font-medium text-zinc-900 whitespace-nowrap">
                            <img class="w-8 h-8 rounded-full shrink-0" src="https://i.pravatar.cc/100?img=5" alt="Elena Torres">
                            Elena Torres
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">Coordinadora</td>
                        <td class="px-6 py-4 whitespace-nowrap">Recursos Humanos</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600 border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Conectado
                            </span>
                        </td>
                    </tr>
                    <tr class="transition-colors hover:bg-zinc-50/50">
                        <td class="flex items-center gap-3 px-6 py-4 font-medium text-zinc-900 whitespace-nowrap">
                            <img class="w-8 h-8 rounded-full shrink-0" src="https://i.pravatar.cc/100?img=1" alt="Marcos Silva">
                            Marcos Silva
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">Soporte IT</td>
                        <td class="px-6 py-4 whitespace-nowrap">Tecnología</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-600 border border-amber-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Ausente
                            </span>
                        </td>
                    </tr>
                    <tr class="transition-colors hover:bg-zinc-50/50">
                        <td class="flex items-center gap-3 px-6 py-4 font-medium text-zinc-900 whitespace-nowrap">
                            <img class="w-8 h-8 rounded-full shrink-0" src="https://i.pravatar.cc/100?img=5" alt="Elena Torres">
                            Elena Torres
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">Coordinadora</td>
                        <td class="px-6 py-4 whitespace-nowrap">Recursos Humanos</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600 border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Conectado
                            </span>
                        </td>
                    </tr>
                    <tr class="transition-colors hover:bg-zinc-50/50">
                        <td class="flex items-center gap-3 px-6 py-4 font-medium text-zinc-900 whitespace-nowrap">
                            <img class="w-8 h-8 rounded-full shrink-0" src="https://i.pravatar.cc/100?img=1" alt="Marcos Silva">
                            Marcos Silva
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">Soporte IT</td>
                        <td class="px-6 py-4 whitespace-nowrap">Tecnología</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-600 border border-amber-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Ausente
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

@push('scripts') {{-- CARGAR SCRIPTS DE JS  --}} 
    <script type="module" src="./js/home.js"></script>
@endpush