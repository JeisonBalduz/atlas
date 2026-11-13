@extends('layouts.app')

@section('title', 'prueba')

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto bg-white px-6 md:px-10 pb-12 pt-6')

@section('siderbar')
    @include('layouts.siderbar')
@endsection

@section('header')
    @include('layouts.header')
@endsection


@section('content')

<!-- BENTO GRID -->
<div class="grid grid-cols-1 gap-4 mx-auto md:grid-cols-2 lg:grid-cols-4 max-w-350">

    <div class="flex items-start justify-between col-span-4 gap-6 mb-8 ">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-marca cascade-enter">Menú principal</h1>
            <p class="mt-1 text-sm font-medium text-zinc-500 cascade-enter" style="animation-delay: 0.1s;">Jueves, 12 de
                Noviembre de 2026</p>
        </div>
        <div class="flex items-center gap-3 cascade-enter" style="animation-delay: 0.2s;">
            <button
                class="px-5 py-2.5 bg-white border border-zinc-200 text-zinc-700 text-sm font-bold rounded-xl hover:bg-zinc-50 hover:text-zinc-900 hover:border-zinc-300 transition-all shadow-[0_4px_16px_rgba(0,0,0,0.04)] cursor-pointer">
                Asignar Familiar
            </button>
            <button
                class="px-5 py-2.5 bg-marca-700 text-white text-sm font-bold rounded-xl hover:bg-marca-800 transition-all shadow-[0_4px_16px_rgba(0,0,0,0.1)] flex items-center gap-2 cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Registrar Empleado
            </button>
        </div>
    </div>

    <!-- ROW 1: TARJETAS MÉTRICAS SIMPLES -->
    <!-- Tarjeta 1: Vacaciones -->
    <div class="grid grid-cols-2 gap-4 lg:col-span-2 sm:col-span-4">
        <div
            class="bg-white rounded-3xl p-5 shadow-[0_4px_24px_rgba(0,0,0,0.03)] border border-zinc-100 flex flex-col justify-center hover:-translate-y-1 transition-transform duration-300 cascade-enter">
            <div class="flex items-center justify-center w-10 h-10 mb-4 rounded-full bg-zinc-50 text-zinc-700"><i
                    data-lucide="palmtree" class="w-5 h-5"></i></div>
            <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Vacaciones</p>
            <h4 class="text-3xl font-bold text-zinc-900">42</h4>
        </div>

        <div
            class="bg-white rounded-3xl p-5 shadow-[0_4px_24px_rgba(0,0,0,0.03)] border border-zinc-100 flex flex-col justify-center hover:-translate-y-1 transition-transform duration-300 cascade-enter">
            <div class="flex items-center justify-center w-10 h-10 mb-4 rounded-full bg-zinc-50 text-zinc-700"><i
                    data-lucide="clock" class="w-5 h-5"></i></div>
            <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Permisos</p>
            <h4 class="text-3xl font-bold text-zinc-900">18</h4>
        </div>

        <div
            class="bg-white rounded-3xl p-5 shadow-[0_4px_24px_rgba(0,0,0,0.03)] border border-zinc-100 flex flex-col justify-center hover:-translate-y-1 transition-transform duration-300 cascade-enter">
            <div class="flex items-center justify-center w-10 h-10 mb-4 rounded-full bg-zinc-50 text-zinc-700"><i
                    data-lucide="stethoscope" class="w-5 h-5"></i></div>
            <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Reposos</p>
            <h4 class="text-3xl font-bold text-zinc-900">7</h4>
        </div>
        <div
            class="bg-white rounded-3xl p-5 shadow-[0_4px_24px_rgba(0,0,0,0.03)] border border-zinc-100 flex flex-col justify-center hover:-translate-y-1 transition-transform duration-300 cascade-enter">
            <div class="flex items-center justify-center w-10 h-10 mb-4 rounded-full bg-zinc-50 text-zinc-700"><i
                    data-lucide="gift" class="w-5 h-5"></i></div>
            <p class="mb-1 text-xs font-semibold tracking-wider uppercase text-zinc-500">Beneficios</p>
            <h4 class="text-3xl font-bold text-zinc-900">156</h4>
        </div>


    </div>

    <div
        class="lg:col-span-2 sm:col-span-4 bg-white rounded-3xl p-6 shadow-[0_4px_24px_rgba(0,0,0,0.03)] border border-zinc-100 flex flex-col items-center hover:-translate-y-1 transition-transform duration-300 cascade-enter relative overflow-hidden">
        <div class="flex items-center justify-between w-full mb-6">
            <h3 class="text-lg font-bold tracking-tight text-zinc-900">Archivos Cargardos</h3>
        </div>
        <div class="w-full ">
            <div id="chart" class="z-10 "></div>
        </div>
    </div>

    <!-- ROW 2: BIENVENIDA + NÓMINA -->
    <div id="welcome-card"
        class=" lg:col-span-3 sm:col-span-4 bg-black rounded-4xl border border-zinc-900 shadow-[0_4px_24px_rgba(0,0,0,0.03)] relative overflow-hidden flex flex-col justify-center p-8 lg:p-10 hover:-translate-y-1 transition-all duration-300 cascade-enter min-h-55"
        style="animation-delay: 0.5s;">

        <div id="welcome-gradient"
            class="absolute inset-0 z-0 transition-colors duration-500 bg-linear-to-br from-black to-zinc-950"></div>

        <!-- Imagen de Recursos Humanos / Equipo de Trabajo -->
        <div id="welcome-img"
            class="absolute right-0 top-0 w-[40%] h-full opacity-[0.15] z-0 pointer-events-none transition-opacity duration-500"
            style="background-image: url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop'); background-size: cover; background-position: center; mask-image: linear-gradient(to left, black, transparent); -webkit-mask-image: linear-gradient(to left, black, transparent);">
        </div>

        <div class="relative z-10 max-w-2xl">
            <h2 id="welcome-title"
                class="mb-4 text-3xl font-bold tracking-tight text-white transition-colors duration-300 md:text-4xl">
                Bienvenido al entorno Atlas, Jeison.</h2>
            <p id="welcome-text"
                class="text-sm font-medium leading-relaxed transition-colors duration-300 text-zinc-400 md:text-base">
                Es un excelente día para impulsar, cuidar y acompañar a tu equipo, construyendo un entorno de bienestar
                donde cada persona se sienta valorada y tenga el apoyo necesario para crecer y dar lo mejor de sí.
            </p>
        </div>
    </div>

    <div class="sm:col-span-1 rounded-4xl bg-marca-900 text-white p-8 border border-marca-900 shadow-[0_15px_30px_rgba(0,0,0,0.15)] flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300 cascade-enter relative overflow-hidden"
        style="animation-delay: 0.6s;">
        <div class="absolute top-0 right-0 w-32 h-32 -translate-y-1/2 rounded-full bg-white/5 blur-3xl translate-x-1/4">
        </div>
        <div class="relative z-10">
            <div class="flex items-center justify-center w-12 h-12 mb-6 rounded-2xl bg-marca-900">
                <i data-lucide="users" class="w-6 h-6 text-marca-300"></i>
            </div>
            <p class="mb-1 text-sm font-medium text-white">Nómina Activa Total</p>
            <h3 class="text-6xl font-bold tracking-tight">842</h3>
        </div>
    </div>

    <!-- ROW 3: ASISTENCIAS, USUARIOS, ACTIVIDAD -->
    <div
        class="lg:col-span-1 sm:col-span-1 rounded-3xl bg-white border border-zinc-100 shadow-[0_4px_24px_rgba(0,0,0,0.03)] p-6 flex flex-col items-center justify-center text-center hover:-translate-y-1 transition-transform duration-300 cascade-enter">
        <p class="mb-2 text-sm font-semibold text-zinc-500">En línea</p>
        <h3 class="mb-4 text-5xl font-bold tracking-tight text-zinc-900">24</h3>
        <div class="flex -space-x-3">
            <img class="w-10 h-10 border-2 border-white rounded-full" src="https://i.pravatar.cc/100?img=1" alt="">
            <img class="w-10 h-10 border-2 border-white rounded-full" src="https://i.pravatar.cc/100?img=2" alt="">
            <img class="w-10 h-10 border-2 border-white rounded-full" src="https://i.pravatar.cc/100?img=3" alt="">
            <div
                class="flex items-center justify-center w-10 h-10 text-xs font-bold border-2 border-white rounded-full bg-zinc-100 text-zinc-600">
                +21</div>
        </div>
    </div>

    <div class="sm:col-span-1 rounded-4xl bg-marca-900 text-white p-8 border border-marca-900 shadow-[0_15px_30px_rgba(0,0,0,0.15)] flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300 cascade-enter relative overflow-hidden"
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

    <div class=" sm:col-span-4  md:col-span-2 rounded-4xl bg-white border border-zinc-100 shadow-[0_4px_24px_rgba(0,0,0,0.03)] p-8 hover:-translate-y-1 transition-transform duration-300 cascade-enter"
        style="animation-delay: 0.9s;">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold tracking-tight text-zinc-900">Actividad Reciente</h3>
            <button class="text-xs font-semibold transition-colors text-zinc-500 hover:text-black">Ver
                bitácora</button>
        </div>
        <div class="space-y-5">
            <div class="flex items-start gap-4">
                <div
                    class="w-10 h-10 rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="user-plus" class="w-4 h-4 text-zinc-700"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-zinc-900">Ingreso de Personal</p>
                    <p class="text-xs text-zinc-500 font-medium mt-0.5">Ana López fue registrada en el departamento
                        de Operaciones.</p>
                    <span class="text-[10px] font-bold text-zinc-400 mt-1 block">Hace 15 min</span>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <div
                    class="w-10 h-10 rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center shrink-0 mt-0.5">
                    <i data-lucide="check-circle" class="w-4 h-4 text-zinc-700"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-zinc-900">Vacaciones Aprobadas</p>
                    <p class="text-xs text-zinc-500 font-medium mt-0.5">Solicitud #402 de Carlos Mendoza fue
                        procesada con éxito.</p>
                    <span class="text-[10px] font-bold text-zinc-400 mt-1 block">Hace 2 horas</span>
                </div>
            </div>
        </div>
    </div>

    <div
        class="lg:col-span-4 bg-white rounded-3xl border border-zinc-100 shadow-[0_4px_24px_rgba(0,0,0,0.03)] overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5 border-b border-zinc-100">
            <h3 class="text-base font-bold text-zinc-900">Guía de Usuarios Activos</h3>
            <button class="text-xs font-semibold text-blue-500 hover:text-blue-600">Ver todos</button>
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
                <tbody class="divide-y divide-zinc-100">
                    <tr class="transition-colors hover:bg-zinc-50/50">
                        <td class="flex items-center gap-3 px-6 py-4 font-medium text-zinc-900">
                            <img class="w-8 h-8 rounded-full" src="https://i.pravatar.cc/100?img=4" alt="">
                            Roberto Medina
                        </td>
                        <td class="px-6 py-4 text-zinc-500">Analista</td>
                        <td class="px-6 py-4 text-zinc-500">Operaciones</td>
                        <td class="px-6 py-4">
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600 border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Conectado
                            </span>
                        </td>
                    </tr>
                    <tr class="transition-colors hover:bg-zinc-50/50">
                        <td class="flex items-center gap-3 px-6 py-4 font-medium text-zinc-900">
                            <img class="w-8 h-8 rounded-full" src="https://i.pravatar.cc/100?img=5" alt="">
                            Elena Torres
                        </td>
                        <td class="px-6 py-4 text-zinc-500">Coordinadora</td>
                        <td class="px-6 py-4 text-zinc-500">Recursos Humanos</td>
                        <td class="px-6 py-4">
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600 border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Conectado
                            </span>
                        </td>
                    </tr>
                    <tr class="transition-colors hover:bg-zinc-50/50">
                        <td class="flex items-center gap-3 px-6 py-4 font-medium text-zinc-900">
                            <img class="w-8 h-8 rounded-full" src="https://i.pravatar.cc/100?img=1" alt="">
                            Marcos Silva
                        </td>
                        <td class="px-6 py-4 text-zinc-500">Soporte IT</td>
                        <td class="px-6 py-4 text-zinc-500">Tecnología</td>
                        <td class="px-6 py-4">
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-600 border border-amber-100">
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

@push('scripts')
    <script type="module" src="./js/home.js"></script>
@endpush