@extends('layouts.app')

@section('title', 'prueba')

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

@section('siderbar')
    @include('layouts.siderbar')
@endsection

@section('header')
    @include('layouts.header')
@endsection


@section('content')

<!-- MAIN AREA -->

    <!-- BARRA DE ACCIONES SUPERIOR -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-black tracking-tight cascade-enter">Menú principal</h1>
            <p class="text-sm font-medium text-zinc-500 mt-1 cascade-enter" style="animation-delay: 0.1s;">Jueves, 12 de
                Noviembre de 2026</p>
        </div>
        <div class="flex items-center gap-3 cascade-enter" style="animation-delay: 0.2s;">
            <button
                class="px-5 py-2.5 bg-white border border-zinc-200 text-zinc-700 text-sm font-bold rounded-xl hover:bg-zinc-50 hover:text-zinc-900 hover:border-zinc-300 transition-all shadow-[0_4px_16px_rgba(0,0,0,0.04)]">
                Asignar Familiar
            </button>
            <button
                class="px-5 py-2.5 bg-black text-white text-sm font-bold rounded-xl hover:bg-zinc-800 transition-all shadow-[0_4px_16px_rgba(0,0,0,0.1)] flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Registrar Empleado
            </button>
        </div>
    </div>

    <!-- BENTO GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-350 mx-auto">

        <!-- ROW 1: TARJETAS MÉTRICAS SIMPLES -->
        <!-- Tarjeta 1: Vacaciones -->
        <div class="rounded-4xl bg-white border border-zinc-100 shadow-[0_4px_24px_rgba(0,0,0,0.03)] p-6 flex flex-col justify-center hover:-translate-y-1 transition-transform duration-300 cascade-enter"
            style="animation-delay: 0.1s;">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-2xl bg-zinc-50 flex items-center justify-center border border-zinc-100">
                    <i data-lucide="palmtree" class="w-5 h-5 text-zinc-700"></i>
                </div>
            </div>
            <div>
                <p class="text-zinc-500 font-medium text-sm">Vacaciones Activas</p>
                <h3 class="text-3xl font-bold text-zinc-900 tracking-tight mt-1">42</h3>
            </div>
        </div>

        <!-- Tarjeta 2: Permisos -->
        <div class="rounded-4xl bg-white border border-zinc-100 shadow-[0_4px_24px_rgba(0,0,0,0.03)] p-6 flex flex-col justify-center hover:-translate-y-1 transition-transform duration-300 cascade-enter"
            style="animation-delay: 0.2s;">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-2xl bg-zinc-50 flex items-center justify-center border border-zinc-100">
                    <i data-lucide="clock" class="w-5 h-5 text-zinc-700"></i>
                </div>
            </div>
            <div>
                <p class="text-zinc-500 font-medium text-sm">Permisos de Hoy</p>
                <h3 class="text-3xl font-bold text-zinc-900 tracking-tight mt-1">15</h3>
            </div>
        </div>

        <!-- Tarjeta 3: Reposos -->
        <div class="rounded-4xl bg-white border border-zinc-100 shadow-[0_4px_24px_rgba(0,0,0,0.03)] p-6 flex flex-col justify-center hover:-translate-y-1 transition-transform duration-300 cascade-enter"
            style="animation-delay: 0.3s;">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-2xl bg-zinc-50 flex items-center justify-center border border-zinc-100">
                    <i data-lucide="thermometer" class="w-5 h-5 text-zinc-700"></i>
                </div>
            </div>
            <div>
                <p class="text-zinc-500 font-medium text-sm">Reposos Médicos</p>
                <h3 class="text-3xl font-bold text-zinc-900 tracking-tight mt-1">3</h3>
            </div>
        </div>

        <!-- Tarjeta 4: Beneficios -->
        <div class="rounded-4xl bg-white border border-zinc-100 shadow-[0_4px_24px_rgba(0,0,0,0.03)] p-6 flex flex-col justify-center hover:-translate-y-1 transition-transform duration-300 cascade-enter"
            style="animation-delay: 0.4s;">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-2xl bg-zinc-50 flex items-center justify-center border border-zinc-100">
                    <i data-lucide="heart-handshake" class="w-5 h-5 text-zinc-700"></i>
                </div>
            </div>
            <div>
                <p class="text-zinc-500 font-medium text-sm">HCM Asignados</p>
                <h3 class="text-3xl font-bold text-zinc-900 tracking-tight mt-1">128</h3>
            </div>
        </div>

        <!-- ROW 2: BIENVENIDA + NÓMINA -->
        <div id="welcome-card"
            class="md:col-span-2 lg:col-span-3 bg-black rounded-4xl border border-zinc-900 shadow-[0_4px_24px_rgba(0,0,0,0.03)] relative overflow-hidden flex flex-col justify-center p-8 lg:p-10 hover:-translate-y-1 transition-all duration-300 cascade-enter min-h-65"
            style="animation-delay: 0.5s;">

            <div id="welcome-gradient"
                class="absolute inset-0 bg-linear-to-br from-black to-zinc-950 z-0 transition-colors duration-500">
            </div>

            <!-- Imagen de Recursos Humanos / Equipo de Trabajo -->
            <div id="welcome-img"
                class="absolute right-0 top-0 w-[40%] h-full opacity-[0.15] z-0 pointer-events-none transition-opacity duration-500"
                style="background-image: url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=2070&auto=format&fit=crop'); background-size: cover; background-position: center; mask-image: linear-gradient(to left, black, transparent); -webkit-mask-image: linear-gradient(to left, black, transparent);">
            </div>

            <div class="relative z-10 max-w-2xl">
                <h2 id="welcome-title"
                    class="text-3xl md:text-4xl font-bold text-white tracking-tight mb-4 transition-colors duration-300">
                    Bienvenido al entorno Atlas, Jeison.</h2>
                <p id="welcome-text"
                    class="text-zinc-400 text-sm md:text-base leading-relaxed font-medium transition-colors duration-300">
                    Es un excelente día para gestionar el talento de la organización. Tienes 3 solicitudes de vacaciones
                    pendientes por revisar y 12 ingresos nuevos programados para esta semana en el módulo de Nómina.
                </p>
            </div>
        </div>

        <div class="rounded-4xl bg-black text-white p-8 border border-zinc-900 shadow-[0_15px_30px_rgba(0,0,0,0.15)] flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300 cascade-enter relative overflow-hidden"
            style="animation-delay: 0.6s;">
            <div
                class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4">
            </div>
            <div class="relative z-10">
                <div
                    class="w-12 h-12 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center mb-6">
                    <i data-lucide="users" class="w-6 h-6 text-zinc-300"></i>
                </div>
                <p class="text-zinc-400 font-medium text-sm mb-1">Nómina Activa Total</p>
                <h3 class="text-6xl font-bold tracking-tight">842</h3>
            </div>
        </div>

        <!-- ROW 3: ASISTENCIAS, USUARIOS, ACTIVIDAD -->
        <div class="rounded-4xl bg-white border border-zinc-100 shadow-[0_4px_24px_rgba(0,0,0,0.03)] p-8 flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300 cascade-enter"
            style="animation-delay: 0.7s;">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="relative flex h-3 w-3">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <p class="text-zinc-900 font-bold text-sm tracking-wide">Usuarios en línea</p>
                </div>
                <p class="text-zinc-500 text-xs font-medium mb-5 uppercase tracking-wider">Operadores activos</p>

                <h3 class="text-5xl font-bold text-zinc-900 tracking-tight">14</h3>
            </div>
            <!-- Mini avatares -->
            <div class="flex -space-x-2 mt-6">
                <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white"
                    src="https://images.unsplash.com/photo-1491528323818-fdd1faba62cc?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                    alt="" />
                <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white"
                    src="https://images.unsplash.com/photo-1550525811-e5869dd03032?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                    alt="" />
                <div
                    class="h-8 w-8 rounded-full ring-2 ring-white bg-zinc-100 flex items-center justify-center text-xs font-bold text-zinc-600">
                    +12</div>
            </div>
        </div>

        <div class="rounded-4xl bg-black text-white p-8 border border-zinc-900 shadow-[0_15px_30px_rgba(0,0,0,0.15)] flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300 cascade-enter relative overflow-hidden"
            style="animation-delay: 0.8s;">
            <div class="relative z-10">
                <div
                    class="w-12 h-12 rounded-2xl bg-zinc-900 border border-zinc-800 flex items-center justify-center mb-6">
                    <i data-lucide="calendar-check-2" class="w-6 h-6 text-zinc-300"></i>
                </div>
                <p class="text-zinc-400 font-medium text-sm mb-1">Asistencia de Hoy</p>
                <h3 class="text-5xl font-bold tracking-tight">96<span class="text-3xl text-zinc-500">%</span></h3>
                <div class="mt-4 pt-4 border-t border-zinc-900/50">
                    <p class="text-zinc-300 text-sm font-medium mb-1">808 / 842 empleados presentes</p>
                    <p class="text-zinc-500 text-xs font-medium">Solo 3 ausencias reportadas hoy.</p>
                </div>
            </div>
        </div>

        <div class="md:col-span-2 rounded-4xl bg-white border border-zinc-100 shadow-[0_4px_24px_rgba(0,0,0,0.03)] p-8 hover:-translate-y-1 transition-transform duration-300 cascade-enter"
            style="animation-delay: 0.9s;">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-zinc-900 tracking-tight">Actividad Reciente</h3>
                <button class="text-xs font-semibold text-zinc-500 hover:text-black transition-colors">Ver
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

    </div>

@endsection

@push('scripts')

<script>
    // Inicializar Lucide Icons
        lucide.createIcons();

        // Datos RESTAURADOS completos para los submenús del Sidebar (modo colapsado)
        const menusData = {
            'nomina': [
                { label: 'Registrar empleado', url: '#' },
                { label: 'Registrar familiar', url: '#' },
                { label: 'Empleados', url: '#' },
                { label: 'Familiares', url: '#' }
            ],
            'beneficios': [
                { label: 'Asignar beneficio', url: '#' },
                { label: 'Hcm', url: '#' }
            ],
            'vacaciones': [
                { label: 'Asignar vacaciones', url: '#' },
                { label: 'Suspender vacaciones', url: '#' },
                { label: 'Vacaciones', url: '#' }
            ],
            'ausencias': [
                { label: 'Asignar ausencia', url: '#' },
                { label: 'Permisos', url: '#' },
                { label: 'Reposos', url: '#' }
            ],
            'asistencias': null,
            'usuarios': [
                { label: 'Registrar Usuarios', url: '#' },
                { label: 'Ver usuario', url: '#' },
                { label: 'Roles', url: '#' },
                { label: 'Permisos', url: '#' }
            ],
            'archivo': [
                { label: 'Ficha técnica', url: '#' },
                { label: 'Cargar archivos', url: '#' },
                { label: 'Archivos', url: '#' }
            ],
            'administrador': [
                { label: 'Base de datos', url: '#' },
                { label: 'Auditoria', url: '#' }
            ]
        };

        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const sidebarBubble = document.getElementById('sidebar-bubble');
        const sidebarBubbleList = document.getElementById('sidebar-bubble-list');
        const menuBtnContainers = document.querySelectorAll('.menu-item-container');
        
        const notiBtn = document.getElementById('noti-btn');
        const notiBubble = document.getElementById('noti-bubble');
        const profileBtn = document.getElementById('profile-btn');
        const profileBubble = document.getElementById('profile-bubble');

        let activeMenuKey = null;

        // Toggle Sidebar (Expandir / Colapsar)
        hamburgerBtn.addEventListener('click', () => {
            const isExpanded = sidebar.getAttribute('data-expanded') === 'true';
            closeAllBubbles();

            if (isExpanded) {
                sidebar.setAttribute('data-expanded', 'false');
                mainContent.classList.remove('ml-64');
                mainContent.classList.add('ml-20');
                
                document.querySelectorAll('.sidebar-text').forEach(el => {
                    el.classList.remove('opacity-100');
                    el.classList.add('opacity-0');
                    setTimeout(() => el.classList.add('hidden'), 300);
                });
                
                document.querySelectorAll('.accordion-content').forEach(acc => {
                    acc.setAttribute('data-open', 'false');
                    acc.classList.add('hidden');
                });
            } else {
                sidebar.setAttribute('data-expanded', 'true');
                mainContent.classList.remove('ml-20');
                mainContent.classList.add('ml-64');
                
                document.querySelectorAll('.sidebar-text').forEach(el => {
                    el.classList.remove('hidden');
                    setTimeout(() => {
                        el.classList.remove('opacity-0');
                        el.classList.add('opacity-100');
                    }, 50);
                });
                
                document.querySelectorAll('.accordion-content').forEach(acc => {
                    acc.classList.remove('hidden');
                });
            }
        });

        // Lógica de Menús del Sidebar
        menuBtnContainers.forEach(container => {
            const btn = container.querySelector('.menu-btn');
            const menuKey = container.getAttribute('data-menu');
            const accordion = container.querySelector('.accordion-content');
            const chevron = container.querySelector('.chevron');

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                closeHeaderBubbles();

                const isSidebarExpanded = sidebar.getAttribute('data-expanded') === 'true';

                if (isSidebarExpanded) {
                    if (!accordion) return;
                    const isOpen = accordion.getAttribute('data-open') === 'true';
                    
                    document.querySelectorAll('.accordion-content').forEach(acc => acc.setAttribute('data-open', 'false'));
                    document.querySelectorAll('.chevron').forEach(chev => chev.style.transform = 'rotate(0deg)');

                    if (!isOpen) {
                        accordion.setAttribute('data-open', 'true');
                        if (chevron) chevron.style.transform = 'rotate(180deg)';
                    }
                } else {
                    const items = menusData[menuKey];
                    if (!items) {
                        closeSidebarBubble();
                        return;
                    }

                    if (activeMenuKey === menuKey && sidebarBubble.classList.contains('scale-y-100')) {
                        closeSidebarBubble();
                        return;
                    }

                    sidebarBubbleList.innerHTML = '';
                    items.forEach(item => {
                        const li = document.createElement('li');
                        li.innerHTML = `<a href="${item.url}" class="block px-4 py-2.5 mx-1 rounded-xl transition-all text-zinc-300 hover:text-white hover:bg-zinc-800/50">${item.label}</a>`;
                        sidebarBubbleList.appendChild(li);
                    });

                    const rect = btn.getBoundingClientRect();
                    sidebarBubble.style.top = `${rect.top}px`;
                    sidebarBubble.style.left = `${rect.right + 24}px`; // Ajustado de 16px a 24px para mayor separación
                    
                    sidebarBubble.classList.remove('opacity-0', 'scale-y-0', 'pointer-events-none');
                    sidebarBubble.classList.add('opacity-100', 'scale-y-100');
                    
                    // Sistema anticolisión para que no se corte abajo
                    setTimeout(() => {
                        const bubbleRect = sidebarBubble.getBoundingClientRect();
                        if (bubbleRect.bottom > window.innerHeight) {
                            sidebarBubble.style.top = `${window.innerHeight - bubbleRect.height - 20}px`;
                        }
                    }, 50);

                    activeMenuKey = menuKey;
                }
            });
        });

        function toggleHeaderBubble(bubbleToToggle, e) {
            e.stopPropagation();
            closeSidebarBubble();
            const isTargetOpen = bubbleToToggle.classList.contains('scale-y-100');
            closeHeaderBubbles();
            if (!isTargetOpen) {
                bubbleToToggle.classList.remove('opacity-0', 'scale-y-0', 'pointer-events-none');
                bubbleToToggle.classList.add('opacity-100', 'scale-y-100');
            }
        }

        notiBtn.addEventListener('click', (e) => toggleHeaderBubble(notiBubble, e));
        profileBtn.addEventListener('click', (e) => toggleHeaderBubble(profileBubble, e));

        function closeSidebarBubble() {
            sidebarBubble.classList.remove('opacity-100', 'scale-y-100');
            sidebarBubble.classList.add('opacity-0', 'scale-y-0', 'pointer-events-none');
            activeMenuKey = null;
        }

        function closeHeaderBubbles() {
            notiBubble.classList.remove('opacity-100', 'scale-y-100');
            notiBubble.classList.add('opacity-0', 'scale-y-0', 'pointer-events-none');
            profileBubble.classList.remove('opacity-100', 'scale-y-100');
            profileBubble.classList.add('opacity-0', 'scale-y-0', 'pointer-events-none');
        }

        function closeAllBubbles() {
            closeSidebarBubble();
            closeHeaderBubbles();
        }

        document.addEventListener('click', (e) => {
            if (!sidebarBubble.contains(e.target)) closeSidebarBubble();
            if (!notiBubble.contains(e.target) && !profileBubble.contains(e.target)) closeHeaderBubbles();
        });

        mainContent.addEventListener('scroll', closeAllBubbles);
        sidebar.addEventListener('scroll', closeSidebarBubble);

        sidebarBubble.addEventListener('click', (e) => e.stopPropagation());
        notiBubble.addEventListener('click', (e) => e.stopPropagation());
        profileBubble.addEventListener('click', (e) => e.stopPropagation());
</script>
@endpush