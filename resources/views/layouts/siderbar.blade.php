@push('styles')
    <style>
        /* Barra de desplazamiento elegante (Scrollbar personalizada) exclusiva del sidebar */
        #sidebar::-webkit-scrollbar {
            width: 6px;
        }

        #sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        #sidebar::-webkit-scrollbar-thumb {
            background-color: #27272a;
            /* zinc-800 */
            border-radius: 9999px;
        }

        #sidebar::-webkit-scrollbar-thumb:hover {
            background-color: #3f3f46;
            /* zinc-700 */
        }

        /* Animaciones iniciales en cascada */
        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(15px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .cascade-enter {
            opacity: 0;
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Hover proporcionado para sidebar colapsado */
        #sidebar[data-expanded="false"] .menu-btn {
            width: 44px;
            height: 44px;
            margin: 0 auto;
            justify-content: center;
            padding: 0;
        }

        #sidebar[data-expanded="false"] .menu-btn i.chevron {
            display: none;
        }
    </style>
@endpush
<!-- SIDEBAR NEGRO -->
<aside id="sidebar" data-expanded="false"
    class="w-20 data-[expanded=true]:w-64 bg-zinc-900 h-screen fixed left-0 top-0 flex flex-col py-6 z-50 overflow-y-auto overflow-x-hidden border-r border-zinc-800 transition-[width] duration-300 ease-in-out">

    <!-- Logo Header -->
    <div class="flex items-center h-10 px-5 mb-8 shrink-0">
        <div class="flex items-center justify-center w-10 h-10 overflow-hidden text-white rounded-xl">
            <img src="./assets/icons/logo_atlas.png" class="object-cover w-full h-full" alt="Atlas Logo">
        </div>
        <span
            class="hidden ml-4 text-xl font-bold tracking-tight text-white transition-opacity duration-300 opacity-0 whitespace-nowrap sidebar-text">ATLAS</span>
    </div>

    <!-- Navegación Principal -->
    <nav class="flex flex-col flex-1 w-full gap-2 px-3">

        <!-- Nómina -->
        <div class="menu-item-container" data-menu="nomina">
            <button
                class="cursor-pointer menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                <div class="flex items-center gap-4">
                    <i data-lucide="users" class="w-5 h-5 shrink-0"></i>
                    <span
                        class="hidden font-medium transition-opacity duration-300 opacity-0 whitespace-nowrap sidebar-text">Nómina</span>
                </div>
                <i data-lucide="chevron-down"
                    class="hidden w-4 h-4 transition-all duration-300 opacity-0 shrink-0 sidebar-text chevron"></i>
            </button>
            <div
                class="accordion-content grid grid-rows-[0fr] transition-all duration-300 data-[open=true]:grid-rows-[1fr]">
                <div class="overflow-hidden">
                    <div class="relative flex flex-col gap-1 py-2 ml-6">
                        <div class="absolute left-0 border-l-2 top-2 bottom-2 border-zinc-700"></div>
                        <a href="#" class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Registrar
                            empleado</a>
                        <a href="#" class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Registrar
                            familiar</a>
                        <a href="#"
                            class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Empleados</a>
                        <a href="#"
                            class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Familiares</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Beneficios -->
        <div class="menu-item-container" data-menu="beneficios">
            <button
                class="cursor-pointer menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                <div class="flex items-center gap-4">
                    <i data-lucide="heart-handshake" class="w-5 h-5 shrink-0"></i>
                    <span
                        class="hidden font-medium transition-opacity duration-300 opacity-0 whitespace-nowrap sidebar-text">Beneficios</span>
                </div>
                <i data-lucide="chevron-down"
                    class="hidden w-4 h-4 transition-all duration-300 opacity-0 shrink-0 sidebar-text chevron"></i>
            </button>
            <div
                class="accordion-content grid grid-rows-[0fr] transition-all duration-300 data-[open=true]:grid-rows-[1fr]">
                <div class="overflow-hidden">
                    <div class="relative flex flex-col gap-1 py-2 ml-6">
                        <div class="absolute left-0 border-l-2 top-2 bottom-2 border-zinc-700"></div>
                        <a href="#" class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Asignar
                            beneficio</a>
                        <a href="#" class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Hcm</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vacaciones -->
        <div class="menu-item-container" data-menu="vacaciones">
            <button
                class="cursor-pointer menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                <div class="flex items-center gap-4">
                    <i data-lucide="palmtree" class="w-5 h-5 shrink-0"></i>
                    <span
                        class="hidden font-medium transition-opacity duration-300 opacity-0 whitespace-nowrap sidebar-text">Vacaciones</span>
                </div>
                <i data-lucide="chevron-down"
                    class="hidden w-4 h-4 transition-all duration-300 opacity-0 shrink-0 sidebar-text chevron"></i>
            </button>
            <div
                class="accordion-content grid grid-rows-[0fr] transition-all duration-300 data-[open=true]:grid-rows-[1fr]">
                <div class="overflow-hidden">
                    <div class="relative flex flex-col gap-1 py-2 ml-6">
                        <div class="absolute left-0 border-l-2 top-2 bottom-2 border-zinc-700"></div>
                        <a href="#" class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Asignar
                            vacaciones</a>
                        <a href="#" class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Suspender
                            vacaciones</a>
                        <a href="#"
                            class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Vacaciones</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ausencias -->
        <div class="menu-item-container" data-menu="ausencias">
            <button
                class="cursor-pointer menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                <div class="flex items-center gap-4">
                    <i data-lucide="user-minus" class="w-5 h-5 shrink-0"></i>
                    <span
                        class="hidden font-medium transition-opacity duration-300 opacity-0 whitespace-nowrap sidebar-text">Ausencias</span>
                </div>
                <i data-lucide="chevron-down"
                    class="hidden w-4 h-4 transition-all duration-300 opacity-0 shrink-0 sidebar-text chevron"></i>
            </button>
            <div
                class="accordion-content grid grid-rows-[0fr] transition-all duration-300 data-[open=true]:grid-rows-[1fr]">
                <div class="overflow-hidden">
                    <div class="relative flex flex-col gap-1 py-2 ml-6">
                        <div class="absolute left-0 border-l-2 top-2 bottom-2 border-zinc-700"></div>
                        <a href="#" class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Asignar
                            ausencia</a>
                        <a href="#"
                            class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Permisos</a>
                        <a href="#"
                            class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Reposos</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Asistencias -->
        <div class="menu-item-container" data-menu="asistencias">
            <button
                class="cursor-pointer menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                <div class="flex items-center gap-4">
                    <i data-lucide="calendar-check-2" class="w-5 h-5 shrink-0"></i>
                    <span
                        class="hidden font-medium transition-opacity duration-300 opacity-0 whitespace-nowrap sidebar-text">Asistencias</span>
                </div>
            </button>
        </div>

        <!-- Usuarios (Restaurado) -->
        <div class="menu-item-container" data-menu="usuarios">
            <button
                class="cursor-pointer menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                <div class="flex items-center gap-4">
                    <i data-lucide="user-cog" class="w-5 h-5 shrink-0"></i>
                    <span
                        class="hidden font-medium transition-opacity duration-300 opacity-0 whitespace-nowrap sidebar-text">Usuarios</span>
                </div>
                <i data-lucide="chevron-down"
                    class="hidden w-4 h-4 transition-all duration-300 opacity-0 shrink-0 sidebar-text chevron"></i>
            </button>
            <div
                class="accordion-content grid grid-rows-[0fr] transition-all duration-300 data-[open=true]:grid-rows-[1fr]">
                <div class="overflow-hidden">
                    <div class="relative flex flex-col gap-1 py-2 ml-6">
                        <div class="absolute left-0 border-l-2 top-2 bottom-2 border-zinc-700"></div>
                        <a href="#" class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Registrar
                            Usuarios</a>
                        <a href="#" class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Ver
                            usuario</a>
                        <a href="#" class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Roles</a>
                        <a href="#"
                            class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Permisos</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Archivo (Restaurado) -->
        <div class="menu-item-container" data-menu="archivo">
            <button
                class="cursor-pointer menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                <div class="flex items-center gap-4">
                    <i data-lucide="folder-open" class="w-5 h-5 shrink-0"></i>
                    <span
                        class="hidden font-medium transition-opacity duration-300 opacity-0 whitespace-nowrap sidebar-text">Archivo</span>
                </div>
                <i data-lucide="chevron-down"
                    class="hidden w-4 h-4 transition-all duration-300 opacity-0 shrink-0 sidebar-text chevron"></i>
            </button>
            <div
                class="accordion-content grid grid-rows-[0fr] transition-all duration-300 data-[open=true]:grid-rows-[1fr]">
                <div class="overflow-hidden">
                    <div class="relative flex flex-col gap-1 py-2 ml-6">
                        <div class="absolute left-0 border-l-2 top-2 bottom-2 border-zinc-700"></div>
                        <a href="#" class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Ficha
                            técnica</a>
                        <a href="#" class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Cargar
                            archivos</a>
                        <a href="#"
                            class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Archivos</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Administrador (Restaurado) -->
        <div class="menu-item-container" data-menu="administrador">
            <button
                class="cursor-pointer menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                <div class="flex items-center gap-4">
                    <i data-lucide="database" class="w-5 h-5 shrink-0"></i>
                    <span
                        class="hidden font-medium transition-opacity duration-300 opacity-0 whitespace-nowrap sidebar-text">Administrador</span>
                </div>
                <i data-lucide="chevron-down"
                    class="hidden w-4 h-4 transition-all duration-300 opacity-0 shrink-0 sidebar-text chevron"></i>
            </button>
            <div
                class="accordion-content grid grid-rows-[0fr] transition-all duration-300 data-[open=true]:grid-rows-[1fr]">
                <div class="overflow-hidden">
                    <div class="relative flex flex-col gap-1 py-2 ml-6">
                        <div class="absolute left-0 border-l-2 top-2 bottom-2 border-zinc-700"></div>
                        <a href="#" class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Base de
                            datos</a>
                        <a href="#"
                            class="py-2 pl-5 text-sm transition-colors text-zinc-400 hover:text-white">Auditoria</a>
                    </div>
                </div>
            </div>
        </div>

    </nav>

    <!-- Avatar Usuario Abajo Expandible -->
    <div class="w-full px-4 mt-6">
        <button class="flex items-center w-full h-12 text-left rounded-full cursor-pointer group">
            <div
                class="w-12 h-12 overflow-hidden transition-colors border-2 rounded-full shrink-0 border-zinc-800 group-hover:border-zinc-500">
                <img src="./assets/icons/usuarioPrueba.webp"
                    class="object-cover w-full h-full shrink-0" alt="User">
            </div>
            <div
                class="flex-col justify-center flex-1 hidden ml-3 truncate transition-opacity duration-300 opacity-0 sidebar-text">
                <p class="text-sm font-bold leading-tight text-white">Jeison Balduz</p>
                <p class="text-[11px] font-medium text-zinc-500 mt-0.5 uppercase tracking-wider">Desarrollador</p>
            </div>
        </button>
    </div>
</aside>

<!-- BURBUJA DE SUBMENÚ DEL SIDEBAR (Negra, animación scale-y) -->
<div id="sidebar-bubble"
    class="fixed transition-all duration-200 ease-out origin-top transform scale-y-0 opacity-0 pointer-events-none z-100">
    <div
        class="bg-zinc-900 border border-zinc-800 rounded-2xl py-2 min-w-55 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.5)]">
        <ul id="sidebar-bubble-list" class="flex flex-col text-sm font-medium text-zinc-300">
            <!-- Se llena con JS -->
        </ul>
    </div>
</div>


@push('scripts')
    <script type="module" src="./js/siderbar.js"></script>
@endpush