<!-- SIDEBAR NEGRO -->
    <aside id="sidebar" data-expanded="false" class="w-20 data-[expanded=true]:w-64 bg-black h-screen fixed left-0 top-0 flex flex-col py-6 z-50 overflow-y-auto overflow-x-hidden border-r border-zinc-800 transition-[width] duration-300 ease-in-out">
        
        <!-- Logo Header -->
        <div class="flex items-center px-5 mb-8 h-10 shrink-0">
            <div class="w-10 h-10 rounded-xl overflow-hidden flex items-center justify-center text-white">
                <img src="./assets/icons/logo_atlas.png" class="w-full h-full object-cover" alt="Atlas Logo">
            </div>
            <span class="ml-4 font-bold text-white text-xl tracking-tight whitespace-nowrap opacity-0 transition-opacity duration-300 sidebar-text hidden">ATLAS</span>
        </div>

        <!-- Navegación Principal -->
        <nav class="flex flex-col gap-2 px-3 w-full flex-1">
            
            <!-- Nómina -->
            <div class="menu-item-container" data-menu="nomina">
                <button class="menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                    <div class="flex items-center gap-4">
                        <i data-lucide="users" class="w-5 h-5 shrink-0"></i>
                        <span class="font-medium whitespace-nowrap opacity-0 transition-opacity duration-300 sidebar-text hidden">Nómina</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 shrink-0 opacity-0 transition-all duration-300 sidebar-text hidden chevron"></i>
                </button>
                <div class="accordion-content grid grid-rows-[0fr] transition-all duration-300 data-[open=true]:grid-rows-[1fr]">
                    <div class="overflow-hidden">
                        <div class="flex flex-col gap-1 py-2 relative ml-6">
                            <div class="absolute left-0 top-2 bottom-2 border-l-2 border-zinc-700"></div>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Registrar empleado</a>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Registrar familiar</a>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Empleados</a>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Familiares</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Beneficios -->
            <div class="menu-item-container" data-menu="beneficios">
                <button class="menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                    <div class="flex items-center gap-4">
                        <i data-lucide="heart-handshake" class="w-5 h-5 shrink-0"></i>
                        <span class="font-medium whitespace-nowrap opacity-0 transition-opacity duration-300 sidebar-text hidden">Beneficios</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 shrink-0 opacity-0 transition-all duration-300 sidebar-text hidden chevron"></i>
                </button>
                <div class="accordion-content grid grid-rows-[0fr] transition-all duration-300 data-[open=true]:grid-rows-[1fr]">
                    <div class="overflow-hidden">
                        <div class="flex flex-col gap-1 py-2 relative ml-6">
                            <div class="absolute left-0 top-2 bottom-2 border-l-2 border-zinc-700"></div>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Asignar beneficio</a>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Hcm</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vacaciones -->
            <div class="menu-item-container" data-menu="vacaciones">
                <button class="menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                    <div class="flex items-center gap-4">
                        <i data-lucide="palmtree" class="w-5 h-5 shrink-0"></i>
                        <span class="font-medium whitespace-nowrap opacity-0 transition-opacity duration-300 sidebar-text hidden">Vacaciones</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 shrink-0 opacity-0 transition-all duration-300 sidebar-text hidden chevron"></i>
                </button>
                <div class="accordion-content grid grid-rows-[0fr] transition-all duration-300 data-[open=true]:grid-rows-[1fr]">
                    <div class="overflow-hidden">
                        <div class="flex flex-col gap-1 py-2 relative ml-6">
                            <div class="absolute left-0 top-2 bottom-2 border-l-2 border-zinc-700"></div>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Asignar vacaciones</a>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Suspender vacaciones</a>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Vacaciones</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ausencias -->
            <div class="menu-item-container" data-menu="ausencias">
                <button class="menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                    <div class="flex items-center gap-4">
                        <i data-lucide="user-minus" class="w-5 h-5 shrink-0"></i>
                        <span class="font-medium whitespace-nowrap opacity-0 transition-opacity duration-300 sidebar-text hidden">Ausencias</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 shrink-0 opacity-0 transition-all duration-300 sidebar-text hidden chevron"></i>
                </button>
                <div class="accordion-content grid grid-rows-[0fr] transition-all duration-300 data-[open=true]:grid-rows-[1fr]">
                    <div class="overflow-hidden">
                        <div class="flex flex-col gap-1 py-2 relative ml-6">
                            <div class="absolute left-0 top-2 bottom-2 border-l-2 border-zinc-700"></div>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Asignar ausencia</a>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Permisos</a>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Reposos</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Asistencias -->
            <div class="menu-item-container" data-menu="asistencias">
                <button class="menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                    <div class="flex items-center gap-4">
                        <i data-lucide="calendar-check-2" class="w-5 h-5 shrink-0"></i>
                        <span class="font-medium whitespace-nowrap opacity-0 transition-opacity duration-300 sidebar-text hidden">Asistencias</span>
                    </div>
                </button>
            </div>

            <!-- Usuarios (Restaurado) -->
            <div class="menu-item-container" data-menu="usuarios">
                <button class="menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                    <div class="flex items-center gap-4">
                        <i data-lucide="user-cog" class="w-5 h-5 shrink-0"></i>
                        <span class="font-medium whitespace-nowrap opacity-0 transition-opacity duration-300 sidebar-text hidden">Usuarios</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 shrink-0 opacity-0 transition-all duration-300 sidebar-text hidden chevron"></i>
                </button>
                <div class="accordion-content grid grid-rows-[0fr] transition-all duration-300 data-[open=true]:grid-rows-[1fr]">
                    <div class="overflow-hidden">
                        <div class="flex flex-col gap-1 py-2 relative ml-6">
                            <div class="absolute left-0 top-2 bottom-2 border-l-2 border-zinc-700"></div>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Registrar Usuarios</a>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Ver usuario</a>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Roles</a>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Permisos</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Archivo (Restaurado) -->
            <div class="menu-item-container" data-menu="archivo">
                <button class="menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                    <div class="flex items-center gap-4">
                        <i data-lucide="folder-open" class="w-5 h-5 shrink-0"></i>
                        <span class="font-medium whitespace-nowrap opacity-0 transition-opacity duration-300 sidebar-text hidden">Archivo</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 shrink-0 opacity-0 transition-all duration-300 sidebar-text hidden chevron"></i>
                </button>
                <div class="accordion-content grid grid-rows-[0fr] transition-all duration-300 data-[open=true]:grid-rows-[1fr]">
                    <div class="overflow-hidden">
                        <div class="flex flex-col gap-1 py-2 relative ml-6">
                            <div class="absolute left-0 top-2 bottom-2 border-l-2 border-zinc-700"></div>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Ficha técnica</a>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Cargar archivos</a>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Archivos</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Administrador (Restaurado) -->
            <div class="menu-item-container" data-menu="administrador">
                <button class="menu-btn w-full h-12 flex items-center justify-between rounded-2xl text-zinc-400 hover:text-white hover:bg-zinc-900 transition-all px-3.5 group relative">
                    <div class="flex items-center gap-4">
                        <i data-lucide="database" class="w-5 h-5 shrink-0"></i>
                        <span class="font-medium whitespace-nowrap opacity-0 transition-opacity duration-300 sidebar-text hidden">Administrador</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 shrink-0 opacity-0 transition-all duration-300 sidebar-text hidden chevron"></i>
                </button>
                <div class="accordion-content grid grid-rows-[0fr] transition-all duration-300 data-[open=true]:grid-rows-[1fr]">
                    <div class="overflow-hidden">
                        <div class="flex flex-col gap-1 py-2 relative ml-6">
                            <div class="absolute left-0 top-2 bottom-2 border-l-2 border-zinc-700"></div>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Base de datos</a>
                            <a href="#" class="pl-5 py-2 text-sm text-zinc-400 hover:text-white transition-colors">Auditoria</a>
                        </div>
                    </div>
                </div>
            </div>

        </nav>

        <!-- Avatar Usuario Abajo Expandible -->
        <div class="px-4 mt-6 w-full">
            <button class="w-full h-12 flex items-center rounded-full group cursor-pointer text-left">
                <div class="w-12 h-12 shrink-0 rounded-full overflow-hidden border-2 border-zinc-800 group-hover:border-zinc-500 transition-colors">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=150&auto=format&fit=crop" class="w-full h-full object-cover shrink-0" alt="User">
                </div>
                <div class="ml-3 flex-col justify-center opacity-0 transition-opacity duration-300 sidebar-text hidden flex-1 truncate">
                    <p class="text-sm font-bold text-white leading-tight">Jeison Balduz</p>
                    <p class="text-[11px] font-medium text-zinc-500 mt-0.5 uppercase tracking-wider">Desarrollador</p>
                </div>
            </button>
        </div>
    </aside>

    <!-- BURBUJA DE SUBMENÚ DEL SIDEBAR (Negra, animación scale-y) -->
    <div id="sidebar-bubble" class="fixed opacity-0 pointer-events-none transition-all duration-200 ease-out z-100 transform scale-y-0 origin-top">
        <div class="bg-black border border-zinc-800 rounded-2xl py-2 min-w-55 shadow-[0_20px_40px_-10px_rgba(0,0,0,0.5)]">
            <ul id="sidebar-bubble-list" class="flex flex-col text-sm text-zinc-300 font-medium">
                <!-- Se llena con JS -->
            </ul>
        </div>
    </div>