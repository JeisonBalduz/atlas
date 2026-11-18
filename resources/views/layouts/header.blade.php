{{-- HEADER FLUÍDO  --}}

<header class="relative z-40 flex items-center justify-between gap-4 mb-10 bg-white p-2 rounded-2xl shadow-[0_15px_30px_rgba(0,0,0,0.05)] mx-auto max-w-400">
    {{-- Izquierda: Hamburguesa (Toggle Sidebar) y Logo --}}
    <div class="flex items-center gap-4 shrink-0">
        <button id="hamburger-btn"
            type="button"
            aria-label="Alternar menú lateral"
            class=" cursor-pointer p-2 transition-all text-zinc-500 hover:text-zinc-900 rounded-xl hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2">
            <i data-lucide="menu" class="w-6 h-6 shrink-0"></i>
        </button>
        <img src="/assets/icons/inces.png" title="INCES" alt="Logo INCES" class="hidden object-contain w-auto h-7 sm:block">
    </div>

    {{-- Centro: Buscador  --}}
    <div class="flex justify-center flex-1">
        <div class="relative hidden w-full max-w-lg sm:block">
            <label for="header-search" class="sr-only">Buscar empleados, documentos, registros</label>
            <i data-lucide="search" class="absolute w-4 h-4 -translate-y-1/2 left-4 top-1/2 text-zinc-400 shrink-0 pointer-events-none"></i>
            <input id="header-search" type="search" placeholder="Buscar empleados, documentos, registros..."
                class="pl-11 pr-4 py-2.5 bg-white border border-zinc-200 rounded-full text-sm font-medium text-zinc-900 placeholder:text-zinc-500 w-full focus:outline-none focus:ring-2 focus:ring-marca-500 focus:border-marca-500 transition-all shadow-none">
        </div>
    </div>

    {{-- Derecha: Acciones  --}}
    <div class="flex items-center gap-2 sm:gap-4">
        {{-- Modo Oscuro  --}}
        <button type="button"
            aria-label="Alternar modo oscuro"
            class="cursor-pointer p-2.5 text-zinc-500 hover:text-zinc-900 transition-all rounded-full hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2">
            <i data-lucide="moon" class="w-5 h-5 shrink-0"></i>
        </button>

        {{-- Notificaciones  --}}
        <div class="relative">
            <button id="noti-btn"
                type="button"
                aria-label="Ver notificaciones"
                aria-haspopup="true"
                aria-expanded="false"
                class="cursor-pointer p-2.5 text-zinc-500 hover:text-zinc-900 transition-all rounded-full hover:bg-zinc-100 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 relative">
                <i data-lucide="bell" class="w-5 h-5 shrink-0"></i>
                <span
                    class="box-content absolute w-2 h-2 border-2 border-white rounded-full top-2 right-2 bg-red-500"></span>
            </button>
            {{-- Burbuja Blanca Notificaciones --}}
            <div id="noti-bubble"
                class="mt-2 absolute right-0 top-[calc(100%+8px)] w-80 bg-white rounded-3xl border border-zinc-100 shadow-[0_15px_30px_rgba(0,0,0,0.05)] opacity-0 pointer-events-none transform scale-y-0 origin-top transition-all duration-200 ease-out z-110 p-2">
                <div class="flex items-center justify-between p-3 border-b border-zinc-100">
                    <span class="text-sm font-bold text-zinc-900">Notificaciones</span>
                </div>
                <ul class="flex flex-col">
                    <li
                        class="flex items-start gap-3 px-3 py-3 transition-colors border-b cursor-pointer hover:bg-zinc-50 rounded-xl border-zinc-50 last:border-0">
                        <div
                            class="w-8 h-8 rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="palmtree" class="w-4 h-4 text-zinc-700"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-zinc-900">Solicitud de vacaciones</p>
                            <p class="mt-1 text-xs leading-relaxed text-zinc-500">Carlos Mendoza ha solicitado 15 días
                                hábiles a partir del próximo mes. Requiere tu aprobación.</p>
                        </div>
                    </li>
                    <li
                        class="flex items-start gap-3 px-3 py-3 transition-colors cursor-pointer hover:bg-zinc-50 rounded-xl">
                        <div
                            class="w-8 h-8 rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="user-plus" class="w-4 h-4 text-zinc-700"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-zinc-900">Nuevo ingreso</p>
                            <p class="mt-1 text-xs leading-relaxed text-zinc-500">Revisar documentación de Ana López
                                antes de su inducción mañana a las 9:00 AM.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Perfil (Restaurado Desarrollador / Activo)  --}}
        <div class="relative flex items-center pl-4 ml-2 border-l border-zinc-200">
            <div class="flex-col items-end justify-center hidden mr-3 sm:flex">
                <span class="text-sm font-bold leading-none text-zinc-900">{{ $usuario->username }}</span>
                <div class="flex items-center gap-1.5 mt-1.5">
                    <span class="relative flex w-2 h-2">
                        <span
                            class="absolute inline-flex w-full h-full rounded-full opacity-75 animate-ping bg-green-400"></span>
                        <span class="relative inline-flex w-2 h-2 rounded-full bg-green-500"></span>
                    </span>
                    <span
                        class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider leading-none">Activo</span>
                </div>
            </div>

            <button id="profile-btn"
                type="button"
                aria-label="Menú de perfil de usuario"
                aria-haspopup="true"
                aria-expanded="false"
                class="flex items-center gap-2 p-1 transition-all border border-transparent rounded-full hover:bg-zinc-50 hover:border-zinc-200 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2">
                <div class="overflow-hidden border rounded-full w-9 h-9 border-zinc-200 shrink-0">
                    <img src="/assets/icons/usuarioPrueba.webp"
                        class="object-cover w-full h-full shrink-0" alt="Avatar de usuario">
                </div>
                <i data-lucide="chevron-down" class="hidden w-4 h-4 mr-1 text-zinc-500 md:block"></i>
            </button>

            {{--  Burbuja Blanca Perfil --}}
            <div id="profile-bubble"
                class="mt-2 absolute right-0 top-[calc(100%+8px)] w-56 bg-white rounded-3xl border border-zinc-100 shadow-[0_15px_30px_rgba(0,0,0,0.05)] opacity-0 pointer-events-none transform scale-y-0 origin-top transition-all duration-200 ease-out z-110 p-2">
                <ul class="flex flex-col text-sm font-medium" role="menu">
                    <li role="none">
                        <a href="#" role="menuitem"
                            class="px-3 py-2.5 hover:bg-zinc-50 text-zinc-700 hover:text-zinc-900 rounded-xl transition-colors flex items-center gap-3">
                            <i data-lucide="user" class="w-4 h-4 shrink-0 text-zinc-500"></i> Mi Perfil
                        </a>
                    </li>
                    <li role="none">
                        <a href="#" role="menuitem"
                            class="px-3 py-2.5 hover:bg-zinc-50 text-zinc-700 hover:text-zinc-900 rounded-xl transition-colors flex items-center gap-3">
                            <i data-lucide="settings" class="w-4 h-4 shrink-0 text-zinc-500"></i> Configuración
                        </a>
                    </li>
                    <li role="separator" class="h-px mx-2 my-1 bg-zinc-100 list-none"></li>
                    <li role="none">
                        <a href="/cerrar_sesion" role="menuitem"
                            class="px-3 py-2.5 hover:bg-red-50 text-red-700 hover:text-red-800 rounded-xl transition-colors flex items-center gap-3">
                            <i data-lucide="log-out" class="w-4 h-4 text-red-600 shrink-0"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>