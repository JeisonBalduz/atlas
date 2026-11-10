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
            placeholder="{{ $placeholder }}" 
            maxlength="{{ $maxlength }}"
            autocomplete="{{ $autocomplete }}"
            class="
                w-full rounded-lg border border-slate-300 bg-slate-50 
                py-2.5 text-sm text-black outline-none transition 
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
            <span id="toggle-password"
                class="absolute right-3 top-1/2 -translate-y-1/2  
                text-slate-400 cursor-pointer transition hover:text-slate-700">
                    <i data-lucide="{{ $rightIcon }}" id = "{{ $idIcon ?? '' }}" ></i>
            </span>
            
        @endif
    </div>
</div>
