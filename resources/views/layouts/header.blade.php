{{-- HEADER FLUÍDO  --}}
<header class="mb-10 flex justify-between items-center gap-4 relative z-40">
    <!-- Izquierda: Hamburguesa (Toggle Sidebar) y Logo -->
    <div class="flex items-center gap-3 shrink-0">
        <button id="hamburger-btn"
            class="p-2 text-zinc-400 hover:text-zinc-900 transition-colors rounded-xl hover:bg-zinc-100 focus:outline-none">
            <i data-lucide="menu" class="w-6 h-6 shrink-0"></i>
        </button>
        <img src="./assets/icons/inces.png" title="INCES" alt="Logo INCES" class="h-7 w-auto object-contain hidden sm:block">
    </div>

    <!-- Centro: Buscador -->
    <div class="flex-1 flex justify-center">
        <div class="relative w-full max-w-lg hidden sm:block">
            <i data-lucide="search" class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-zinc-400 shrink-0"></i>
            <input type="text" placeholder="Buscar empleados, documentos, registros..."
                class="pl-11 pr-4 py-2.5 bg-white border border-zinc-200 rounded-full text-sm font-medium w-full focus:outline-none focus:ring-2 focus:ring-black focus:border-black transition-all shadow-none">
        </div>
    </div>

    <!-- Derecha: Acciones -->
    <div class="flex items-center gap-2 sm:gap-4">
        <!-- Modo Oscuro -->
        <button class="p-2.5 text-zinc-400 hover:text-zinc-900 transition-colors rounded-full hover:bg-zinc-100">
            <i data-lucide="moon" class="w-5 h-5 shrink-0"></i>
        </button>

        <!-- Notificaciones -->
        <div class="relative">
            <button id="noti-btn"
                class="p-2.5 text-zinc-400 hover:text-zinc-900 transition-colors rounded-full hover:bg-zinc-100 relative">
                <i data-lucide="bell" class="w-5 h-5 shrink-0"></i>
                <span
                    class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full border-2 border-white box-content"></span>
            </button>
            <!-- Burbuja Blanca Notificaciones -->
            <div id="noti-bubble"
                class="absolute right-0 top-[calc(100%+8px)] w-80 bg-white rounded-3xl shadow-[0_12px_40px_rgba(0,0,0,0.08)] opacity-0 pointer-events-none transform scale-y-0 origin-top transition-all duration-200 ease-out z-110 p-2">
                <div class="p-3 border-b border-zinc-100 flex justify-between items-center">
                    <span class="font-bold text-zinc-900 text-sm">Notificaciones</span>
                </div>
                <ul class="flex flex-col">
                    <li
                        class="px-3 py-3 hover:bg-zinc-50 rounded-xl transition-colors cursor-pointer border-b border-zinc-50 last:border-0 flex items-start gap-3">
                        <div
                            class="w-8 h-8 rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="palmtree" class="w-4 h-4 text-zinc-700"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-zinc-900">Solicitud de vacaciones</p>
                            <p class="text-xs text-zinc-500 mt-1 leading-relaxed">Carlos Mendoza ha solicitado 15 días
                                hábiles a partir del próximo mes. Requiere tu aprobación.</p>
                        </div>
                    </li>
                    <li
                        class="px-3 py-3 hover:bg-zinc-50 rounded-xl transition-colors cursor-pointer flex items-start gap-3">
                        <div
                            class="w-8 h-8 rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="user-plus" class="w-4 h-4 text-zinc-700"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-zinc-900">Nuevo ingreso</p>
                            <p class="text-xs text-zinc-500 mt-1 leading-relaxed">Revisar documentación de Ana López
                                antes de su inducción mañana a las 9:00 AM.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Perfil (Restaurado Desarrollador / Activo) -->
        <div class="relative flex items-center border-l border-zinc-200 pl-4 ml-2">
            <div class="hidden sm:flex flex-col items-end justify-center mr-3">
                <span class="text-sm font-bold text-zinc-900 leading-none">Desarrollador</span>
                <div class="flex items-center gap-1.5 mt-1.5">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span
                        class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider leading-none">Activo</span>
                </div>
            </div>

            <button id="profile-btn"
                class="flex items-center gap-2 p-1 rounded-full hover:bg-zinc-50 transition-colors border border-transparent hover:border-zinc-200">
                <div class="w-9 h-9 rounded-full overflow-hidden border border-zinc-200 shrink-0">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=150&auto=format&fit=crop"
                        class="w-full h-full object-cover shrink-0" alt="User">
                </div>
                <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400 hidden md:block mr-1"></i>
            </button>

            <!-- Burbuja Blanca Perfil -->
            <div id="profile-bubble"
                class="absolute right-0 top-[calc(100%+8px)] w-56 bg-white rounded-3xl shadow-[0_12px_40px_rgba(0,0,0,0.08)] opacity-0 pointer-events-none transform scale-y-0 origin-top transition-all duration-200 ease-out z-110 p-2">
                <ul class="flex flex-col text-sm font-medium">
                    <li
                        class="px-3 py-2.5 hover:bg-zinc-50 text-zinc-700 hover:text-zinc-900 rounded-xl transition-colors cursor-pointer flex items-center gap-3">
                        <i data-lucide="user" class="w-4 h-4 shrink-0 text-zinc-400"></i> Mi Perfil
                    </li>
                    <li
                        class="px-3 py-2.5 hover:bg-zinc-50 text-zinc-700 hover:text-zinc-900 rounded-xl transition-colors cursor-pointer flex items-center gap-3">
                        <i data-lucide="settings" class="w-4 h-4 shrink-0 text-zinc-400"></i> Configuración
                    </li>
                    <div class="h-px bg-zinc-100 my-1 mx-2"></div>
                    <li
                        class="px-3 py-2.5 hover:bg-red-50 text-red-600 hover:text-red-700 rounded-xl transition-colors cursor-pointer flex items-center gap-3">
                        <i data-lucide="log-out" class="w-4 h-4 shrink-0 text-red-500"></i> Cerrar Sesión
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>