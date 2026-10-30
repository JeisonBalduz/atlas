<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'INCES · Gestión de Talento Humano — Iniciar Sesión' }}</title>

    <!-- Vite Client & Styles para tu entorno local FlightPHP -->
    <script type="module" src="http://localhost:5173/@vite/client"></script>
    <link rel="stylesheet" href="http://localhost:5173/resources/css/input.css">


    <!-- Tipografías: Plus Jakarta Sans, Inter y JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">


    <style>
        :root {
            --font-display: 'Plus Jakarta Sans', sans-serif;
            --font-sans: 'Inter', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        body {
            font-family: var(--font-sans);
            background-color: #ffffff;
            color: #09090b;
            overflow-x: hidden;
        }

        .font-display {
            font-family: var(--font-display);
        }

        .font-mono-tech {
            font-family: var(--font-mono);
        }

        /* Retícula arquitectónica amplia de 96px x 96px sobre fondo blanco */
        .bg-arch-large-grid {
            background-color: #ffffff;
            background-size: 96px 96px;
            background-image: 
                linear-gradient(to right, rgba(9, 9, 11, 0.055) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(9, 9, 11, 0.055) 1px, transparent 1px);
        }

        /* Viñeta blanca suave para integrar el horizonte de la cuadrícula */
        .bg-arch-vignette {
            background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.65) 75%, rgba(255, 255, 255, 0.94) 100%);
        }

    </style>
</head>
<body class="min-h-screen w-full relative selection:bg-zinc-900 selection:text-white flex items-center justify-center p-4 sm:p-6">

    <!-- CAPA 4: Formulario de Inicio de Sesión Estático y Centrado (Estilo Corporativo Serio, border-zinc-200, shadow-none) -->
    <main class="relative z-20 w-full max-w-100 my-auto">
        <div class="bg-white/95 backdrop-blur-md  rounded-2xl p-7 sm:p-9 relative overflow-hidden">
            
            <!-- Línea superior institucional sutil -->
            {{-- <div class="absolute top-0 left-0 right-0 h-1 bg-zinc-900 flex">
                <div class="w-20 h-full bg-red-600"></div>
            </div> --}}

            <!-- Identidad Institucional -->
            <div class="mb-6 pt-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="inline-flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-zinc-900 text-white flex items-center justify-center font-display font-bold text-xs tracking-wider relative">
                            TH
                            <span class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-red-600 border border-white"></span>
                        </div>
                        <div>
                            <span class="font-display font-bold text-xs tracking-tight text-zinc-900 block leading-none">INCES</span>
                            <span class="font-mono-tech text-[10px] text-zinc-500 uppercase tracking-widest">Talento Humano</span>
                        </div>
                    </div>

                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-zinc-50 border border-zinc-200 font-mono-tech text-[10px] uppercase tracking-wider text-zinc-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                        Sede Central
                    </span>
                </div>

                <h1 class="font-display text-2xl sm:text-[26px] font-extrabold tracking-tight text-zinc-950 leading-snug">
                    Iniciar Sesión
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 mt-1 leading-relaxed">
                    Ingrese sus credenciales institucionales para acceder al sistema de gestión de personal.
                </p>
            </div>

            <!-- Alerta de Validación / Estado -->
            <div id="login-alert" class="hidden mb-5 p-3.5 rounded-xl border text-xs font-medium items-center gap-2.5 transition-all">
                <span id="login-alert-icon" class="shrink-0"></span>
                <span id="login-alert-text"></span>
            </div>

            <!-- Formulario de Acceso -->
            <form id="login-form" onsubmit="handleLoginSubmit(event)" class="space-y-4" novalidate>
                
                <!-- Campo: Usuario / Cédula / Correo -->
                <div>
                    <label for="username" class="block text-[11px] font-mono-tech uppercase tracking-wider text-zinc-700 font-semibold mb-1.5">
                        Usuario o Correo Institucional
                    </label>
                    <div class="relative">
                        <div class=" inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 absolute">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required
                            autocomplete="username"
                            placeholder="ej. v12345678 o nombre@inces.gob.ve"
                            class="w-full pl-10 pr-4 py-2.5 bg-white border border-zinc-200 focus:border-zinc-900 rounded-xl text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-900/5 transition-all"
                        >
                    </div>
                </div>

                <!-- Campo: Contraseña -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-[11px] font-mono-tech uppercase tracking-wider text-zinc-700 font-semibold">
                            Contraseña
                        </label>
                        <button type="button" onclick="showRecoveryNotice()" class="text-[11px] font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
                            ¿Olvidó su clave?
                        </button>
                    </div>
                    <div class="relative">
                        <div class="inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 absolute">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            autocomplete="current-password"
                            placeholder="••••••••••••"
                            class="w-full pl-10 pr-10 py-2.5 bg-white border border-zinc-200 focus:border-zinc-900 rounded-xl text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-900/5 transition-all"
                        >
                        <button 
                            type="button" 
                            onclick="togglePasswordVisibility()" 
                            class="inset-y-0 right-0 pr-3.5 flex items-center text-zinc-400 hover:text-zinc-700 transition-colors absolute"
                            title="Mostrar u ocultar contraseña"
                        >
                            <svg id="eye-icon-open" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eye-icon-closed" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Recordar sesión y Credenciales de prueba -->
                <div class="flex items-center justify-between pt-0.5">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" id="remember" class="w-3.5 h-3.5 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                        <span class="text-xs text-zinc-600">Mantener sesión activa</span>
                    </label>

                    <button 
                        type="button" 
                        onclick="fillDemoCredentials()" 
                        class="font-mono-tech text-[10px] uppercase tracking-wider text-zinc-500 hover:text-zinc-900 underline underline-offset-4 transition-colors"
                    >
                        Credenciales Demo
                    </button>
                </div>

                <!-- Botón de Acceso Corporativo Limpio -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        id="submit-btn"
                        class="w-full py-3 px-5 rounded-xl bg-zinc-900 hover:bg-zinc-800 active:bg-zinc-950 text-white font-semibold text-sm flex items-center justify-center gap-2.5 transition-colors cursor-pointer"
                    >
                        <span id="submit-btn-text">Ingresar al Portal</span>
                        <svg id="submit-btn-icon" class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Pie de tarjeta minimalista -->
            <div class="mt-6 pt-4 border-t border-zinc-100 flex items-center justify-between text-[11px] font-mono-tech text-zinc-400">
                <span>INCES · RRHH v2.4</span>
                <span>CONEXIÓN SEGURA</span>
            </div>
        </div>
    </main>

    <!-- Lógica del Formulario y Complejo Arquitectónico 3D Vivo en Three.js -->
    <script>
        // ============================================================================
        // 1. INTERACCIÓN DEL FORMULARIO DE LOGIN
        // ============================================================================
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-icon-open');
            const eyeClosed = document.getElementById('eye-icon-closed');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }

        function showAlert(message, type = 'info') {
            const alertBox = document.getElementById('login-alert');
            const alertText = document.getElementById('login-alert-text');
            const alertIcon = document.getElementById('login-alert-icon');

            alertBox.className = 'mb-5 p-3.5 rounded-xl border text-xs font-medium flex items-center gap-2.5 transition-all';

            if (type === 'success') {
                alertBox.classList.add('bg-emerald-50/90', 'border-emerald-200', 'text-emerald-900');
                alertIcon.innerHTML = '<svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
            } else if (type === 'error') {
                alertBox.classList.add('bg-red-50/90', 'border-red-200', 'text-red-900');
                alertIcon.innerHTML = '<svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            } else {
                alertBox.classList.add('bg-zinc-50', 'border-zinc-200', 'text-zinc-800');
                alertIcon.innerHTML = '<svg class="w-4 h-4 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            }

            alertText.textContent = message;
        }

        function fillDemoCredentials() {
            const userField = document.getElementById('username');
            const passField = document.getElementById('password');
            userField.value = 'analista.rrhh@inces.gob.ve';
            passField.value = 'TalentoInces2025*';
            showAlert('Credenciales institucionales de prueba cargadas.', 'info');
            pulseArchitectureTowers();
        }

        function showRecoveryNotice() {
            showAlert('Comuníquese con la Coordinación de Tecnología y Sistemas para restablecer su ficha.', 'info');
        }

        function handleLoginSubmit(event) {
            event.preventDefault();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const submitBtnText = document.getElementById('submit-btn-text');

            if (!username || !password) {
                showAlert('Por favor complete su usuario institucional y contraseña.', 'error');
                return;
            }

            submitBtnText.textContent = 'Verificando credenciales...';
            pulseArchitectureTowers();

            setTimeout(() => {
                showAlert('Acceso verificado correctamente. Conectando con el módulo de Talento Humano...', 'success');
                submitBtnText.textContent = 'Sesión Iniciada';
            }, 650);
        }

    </script>
</body>
</html>