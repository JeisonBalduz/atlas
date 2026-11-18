{{--
    Componente: Input
    Descripción: Campo de entrada de texto, número, fecha o contraseña con soporte para iconos,
                 límite de caracteres, foco estilizado y mensaje de error dinámico.

    Parámetros:
    - label (string, opcional): Texto de la etiqueta superior.
    - id (string, requerido): Identificador único del input.
    - name (string, requerido): Nombre del campo para el formulario.
    - type (string, opcional): Tipo de input ('text', 'password', 'email', 'date', etc. Default: 'text').
    - placeholder (string, opcional): Texto de sugerencia dentro del campo.
    - maxlength (int, opcional): Límite máximo de caracteres permitidos.
    - leftIcon (string, opcional): Nombre del icono Lucide a la izquierda (ej: 'user', 'mail').
    - rightIcon (string, opcional): Nombre del icono Lucide a la derecha (ej: 'eye', 'search').
    - idIcon (string, opcional): ID para el elemento <i> del icono derecho.
    - idRightIcon (string, opcional): ID para el <span> contenedor del icono derecho (ej: 'toggle-password').
    - focus (string, opcional): Clases Tailwind de focus personalizadas.
    - required (bool, opcional): Si es obligatorio (default: false).
    - disabled (bool, opcional): Deshabilita el input (default: false).
    - readonly (bool, opcional): Solo lectura (default: false).
--}}
<div class="space-y-1.5">
    @if (!empty($label))
        <label 
            for="{{ $id }}" 
            class="block text-sm font-medium text-zinc-900">
                {{ $label }}
        </label>
    @endif

    <div class="relative">
        {{-- Icono Izquierdo --}}
        @if (!empty($leftIcon))
            <i
                data-lucide="{{ $leftIcon }}"
                class="
                    pointer-events-none absolute left-3 top-1/2 h-4 
                    w-4 -translate-y-1/2 text-slate-400">
            </i>
        @endif

        @php
            $focusClasses = $focus ?? 'focus:border-black focus:ring-black';
        @endphp

        {{-- El Input --}}
        <input 
            id="{{ $id }}" 
            name="{{ $name }}" 
            type="{{ $type ?? 'text' }}"
            @if(!empty($placeholder)) placeholder="{{ $placeholder }}" @endif
            @if(!empty($maxlength)) maxlength="{{ $maxlength }}" @endif
            autocomplete="{{ $autocomplete ?? 'off' }}"
            class="
                w-full rounded-lg border border-slate-300 bg-slate-50 
                py-2.5 text-sm text-black outline-none transition duration-200
                focus:bg-white 
                focus:ring-2 
                focus:ring-offset-2 
                {{ $focusClasses }}
                {{ !empty($leftIcon) ? 'pl-10' : 'pl-3' }} 
                {{ !empty($rightIcon) ? 'pr-10' : 'pr-3' }}"
            {{ $required ?? false ? 'required' : '' }} 
            {{ $disabled ?? false ? 'disabled' : '' }} 
            {{ $readonly ?? false ? 'readonly' : '' }} 
        />

        {{-- Icono Derecho (Inyecta tu etiqueta <i> directamente) --}}
        @if (!empty($rightIcon))
            <span id="{{ $idRightIcon ?? 'toggle-password' }}"
                class="absolute right-3 top-1/2 -translate-y-1/2  
                text-slate-400 cursor-pointer transition hover:text-slate-700">
                    <i data-lucide="{{ $rightIcon }}" id="{{ $idIcon ?? '' }}"></i>
            </span>
        @endif
    </div>

    {{-- Mensaje de Error / Ayuda debajo del input --}}
    <p id="{{ $id ?? '' }}-error" 
       class="input-error-msg hidden text-xs font-medium text-red-600 mt-1 transition-all duration-200" 
       role="alert">
    </p>
</div>
