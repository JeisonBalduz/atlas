@extends('layouts.app')

@section('title', 'login')

@push('styles')
    <link rel="stylesheet" href="./css/login.css">
@endpush

@section('content')

    <section id="vista-login" class="flex min-h-screen w-full flex-col md:flex-row">
        <!-- Columna visual institucional -->
        <div class="relative md:flex flex-col justify-end overflow-hidden hidden p-8 md:w-1/2 md:min-h-screen md:p-14">
            <img src="./assets/img/fondo.webp " loading="lazy" alt="Taller de formación técnica"
                class="absolute inset-0 h-full w-full object-cover hidden md:block" />

            <div class="absolute inset-0 login-overlay"></div>
        </div>

        <!-- Formulario -->
        <div class="flex w-full flex-1 items-center justify-center bg-white  md:px-6 md:py-12 md:w-1/2">
            <form id="form-login" class="w-full max-w-sm" novalidate>
                <div class="mb-2 flex flex-col items-center justify-center">
                    <img src="./assets/icons/logo_atlas.png" alt="" class="mb-3 w-16 h-16" alt="Foto">
                    <h2 class="font-medium text-zinc-800 text-2xl">
                        Inicia sesión en tu cuenta
                    </h2>
                    <p class="mt-3 text-center text-sm text-gray-600">
                        Introduce tu correo electrónico y contraseña a continuación para iniciar sesión
                    </p>
                </div>


                @component('components.input', [
                    'label' => 'Usuario',
                    'id' => 'user',
                    'name' => 'usuario',
                    'icon' => 'user',
                    'placeholder' => 'Usuario',
                    'maxlength' => 40,
                    'autocomplete' => 'username',
                    'required' => true,
                ])
                @endcomponent

                <div class="flex items-center justify-between mb-1.5 mt-5 text-sm font-medium">
                    <!-- Lado Izquierdo: Etiqueta del campo -->
                    <label for="password" class="text-zinc-900 cursor-pointer">
                        Contraseña
                    </label>

                    <!-- Lado Derecho: Enlaces y pregunta -->
                    <div class="flex items-center space-x-1 text-sm text-black font-normal">
                        <a href="#" class=" underline underline-offset-6">
                            <span>¿Olvidaste tu contraseña?</span>
                        </a>
                    </div>
                </div>

                @component('components.input', [
                    'id' => 'password',
                    'name' => 'clave',
                    'isPassword' => true,
                    'icon' => 'lock',
                    'placeholder' => '••••••••',
                    'maxlength' => 64,
                    'autocomplete' => 'current-password',
                    'required' => true,
                ])
                @endcomponent




                <p id="login-error" role="alert"
                    class="oculto mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-600">
                </p>

                <button type="submit" id="btn-entrar"
                    class="mt-6 flex w-full items-center justify-center gap-2 rounded-lg bg-black px-4 py-2.5 text-sm 
                    font-semibold text-white shadow-sm transition-all duration-200 hover:bg-bg-zinc-900  hover:shadow-md 
                    focus:outline-none focus:ring-4 focus:ring-zinc-200 active:scale-[0.99]">
                    Iniciar Sesión
                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </button>

                <div class="flex items-center justify-center space-x-1 text-sm mt-4">
                    <span class="text-zinc-600">¿No tienes cuenta?</span>
                    <a href="#" class="font-medium underline text-black underline-offset-4">
                        Solicitar
                    </a>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="./js/login.js"></script>
@endpush
