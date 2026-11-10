@extends('layouts.app')

@section('title', 'login')

@push('styles')
    <link rel="stylesheet" href="./css/login.css">
@endpush

@section('content')

    <section id="vista-login" class="flex min-h-svh w-full flex-col md:flex-row bg-white">
        <!-- Columna visual institucional (Visible solo en desktop) -->
        <div class="relative hidden md:flex md:w-1/2 flex-col justify-end p-8 md:p-14 overflow-hidden">
            <!-- El fallback de min-h-screen se maneja en el padre con min-h-svh, aquí usamos h-full absoluto -->
            <img src="./assets/img/fondo.webp" loading="lazy" alt="Taller de formación técnica"
                class="absolute inset-0 h-full w-full object-cover" />

            <div class="absolute inset-0 login-overlay bg-black/40"></div>
        </div>

        <!-- Formulario (Flexible y adaptado a móviles) -->
        <div class="flex flex-1 w-full md:w-1/2 items-center justify-center p-6 sm:p-8 md:p-12">
            <form id="form-login" class="w-full max-w-sm" novalidate>
                <!-- Cabecera del Formulario -->
                <div class="mb-6 flex flex-col items-center justify-center text-center">
                    <img src="./assets/icons/logo_atlas.png" alt="Logo Atlas" class="mb-4 h-16 w-16 object-contain" />
                    <h2 class="text-2xl font-semibold text-zinc-900 tracking-tight">
                        Inicia sesión en tu cuenta
                    </h2>
                    <p class="mt-2 text-sm text-zinc-600">
                        Introduce tu correo electrónico y contraseña a continuación para iniciar sesión
                    </p>
                </div>

                <!-- Input: Usuario -->
                @component('components.input', [
                    'label' => 'Usuario',
                    'id' => 'user',
                    'name' => 'user',
                    'icon' => 'user',
                    'placeholder' => 'Usuario',
                    'maxlength' => 40,
                    'autocomplete' => 'username',
                    'required' => true,
                ])
                @endcomponent

                <!-- Input: Contraseña con su cabecera accesible -->
                <div class="mt-5 mb-1.5 flex items-center justify-between text-sm">
                    <label for="password" class="font-medium text-zinc-900 cursor-pointer">
                        Contraseña
                    </label>
                    <a href="#"
                        class="text-zinc-600 hover:text-zinc-900 underline underline-offset-4 transition-colors">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                @component('components.input', [
                    'id' => 'password',
                    'name' => 'password',
                    'isPassword' => true,
                    'icon' => 'lock',
                    'placeholder' => '••••••••',
                    'maxlength' => 64,
                    'autocomplete' => 'current-password',
                    'required' => true,
                ])
                @endcomponent

                <!-- Contenedor de Error Dinámico -->
                <p id="login__error" role="alert"
                    class="login__error hidden mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-600">
                </p>

                <!-- Botón de Acción Principal -->
                <button type="submit" id="btn-entrar"
                    class="mt-6 flex w-full items-center justify-center gap-2 rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-zinc-800 focus-visible:outline focus-visible:outline-offset-2 focus-visible:outline-zinc-950 active:scale-[0.98] disabled:opacity-50">
                    <span>Iniciar Sesión</span>
                    <i data-lucide="arrow-right" class="h-4 w-4" aria-hidden="true"></i>
                </button>

                <!-- Registro / Soporte -->
                <div class="mt-6 flex items-center justify-center gap-1 text-sm">
                    <span class="text-zinc-600">¿No tienes cuenta?</span>
                    <a href="#"
                        class="font-medium text-zinc-900 underline underline-offset-4 hover:text-zinc-700 transition-colors">
                        Solicitar
                    </a>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    <script type="module" src="./js/login.js"></script>
@endpush
