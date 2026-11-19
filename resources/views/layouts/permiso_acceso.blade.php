@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('title', 'Permissionaccess') {{-- TÍTULO DE LA PÁGINA --}}

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 pb-12 pt-6 bg-slate-50') {{-- CSS DE LA ETIQUETA MAIN --}}

@section('siderbar'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.siderbar'){{-- CARGAR SIDERBAR  --}} 
@endsection

@section('header'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.header'){{-- CARGAR HEADER  --}} 
@endsection

@section('content') {{-- CARGAR CONTENIDO PRINCIPAL  --}}
{{-- CONTENEDOR PRINCIPAL CENTRADO --}}
<div class=" flex  justify-center bg-zinc-50/50 p-4 sm:p-6 md:p-8">

    {{-- TARJETA DE ACCESO RESTRINGIDO --}}
    <div class="w-full max-w-2xl bg-white rounded-3xl border border-zinc-100 shadow-[0_15px_30px_rgba(0,0,0,0.05)] overflow-hidden">
        
        <!-- Cabecera de la Tarjeta -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-zinc-100 bg-zinc-50/30">
            <!-- Ícono de bloqueo usando un tono de advertencia suave (Rose/Red) -->
            <div class="w-8 h-8 rounded-full bg-rose-50 flex items-center justify-center border border-rose-100 shrink-0">
                <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-zinc-900">Acceso Restringido</h3>
        </div>

        <!-- Cuerpo del Mensaje -->
        <div class="p-6 md:p-8 flex flex-col">
            <h4 class="text-lg font-semibold text-zinc-900 mb-2">Acción no autorizada</h4>
            <p class="text-sm text-zinc-500 mb-8 leading-relaxed">
                El sistema ha bloqueado esta petición porque tu perfil actual no cuenta con los privilegios necesarios.
                Si consideras que deberías tener acceso, contacta al administrador del sistema.
            </p>

            <!-- Bloque de Contexto -->
            <div class="w-full bg-zinc-50/50 rounded-2xl border border-zinc-100 p-1 mb-8">
                <div class="px-4 sm:px-5 py-4 bg-white rounded-[14px]">
                    
                    <!-- Usuario Autenticado -->
                    <div class="flex items-center justify-between py-3 border-b border-zinc-50">
                        <span class="text-sm font-medium text-zinc-500">Usuario autenticado</span>
                        <div class="flex items-center gap-2 sm:gap-3 text-right">
                            <img class="w-6 h-6 rounded-full shrink-0 hidden sm:block" src="./photo/{{$cedula}}.webp" alt="Avatar de usuario">
                            <span class="text-sm font-medium text-zinc-900">{{ $user->username ?? 'Roberto Medina' }}</span>
                        </div>
                    </div>

                    <!-- Rol Actual -->
                    <div class="flex items-center justify-between py-3 border-b border-zinc-50">
                        <span class="text-sm font-medium text-zinc-500">Rol actual</span>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-zinc-100 text-zinc-600 border border-zinc-200">
                            {{ $user->role }}
                        </span>
                    </div>

                    <!-- Permiso Requerido (Técnico) -->
                    <div class="flex items-center justify-between py-3">
                        <span class="text-sm font-medium text-zinc-500">Permiso requerido</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-mono font-medium bg-rose-50 text-rose-600 border border-rose-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span> 
                            <span class="truncate max-w-30 sm:max-w-none">{{ $permissionRequire ?? 'sin asignar' }}</span>
                        </span>
                    </div>
                    
                </div>
            </div>

            <!-- Botones de Acción Directa -->
            <div class="flex flex-col-reverse sm:flex-row items-center gap-3 w-full mt-auto">
                {{-- <a href="/home" class="w-full sm:w-auto px-5 py-2.5 text-sm font-semibold text-zinc-600 hover:text-zinc-900 hover:bg-zinc-50 border border-transparent hover:border-zinc-200 rounded-xl transition-all text-center">
                    Ir al Dashboard
                </a> --}}
                <button onclick="window.history.back()" 
                class="cursor-pointer w-full sm:w-auto sm:ml-auto px-5 py-2.5 text-sm font-semibold text-white bg-zinc-900 hover:bg-zinc-800 rounded-xl transition-colors shadow-sm text-center">
                    Regresar
                </button>
            </div>
        </div>
        
    </div>

</div>
@endsection
