<div class="space-y-1.5">
    @if(!empty($label))
        <label for="{{ $id ?? '' }}" class="block text-sm font-medium text-zinc-900">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        {{-- Icono izquierdo opcional --}}
        @if(!empty($icon))
            <i data-lucide="{{ $icon }}"
                class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
        @endif

        @php
            // Recibimos la clase completa de Tailwind (ej: 'focus:border-blue-600 focus:ring-blue-600')
            // Si no mandas nada, por defecto usa las de color negro nativo.
            $focusClasses = $focus ?? 'focus:border-black focus:ring-black';
        @endphp

        <input 
            id="{{ $id ?? '' }}" 
            name="{{ $name ?? '' }}" 
            type="{{ ($isPassword ?? false) ? 'password' : ($type ?? 'text') }}"
            placeholder="{{ $placeholder ?? '' }}"
            maxlength="{{ $maxlength ?? '' }}"
            autocomplete="{{ $autocomplete ?? '' }}"
            class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2.5 text-sm text-black outline-none transition 
                   focus:bg-white 
                   focus:ring-2 
                   focus:ring-offset-2 
                   {{ $focusClasses }}
                   {{ !empty($icon) ? 'pl-10 pr-3' : 'px-3' }} 
                   {{ ($isPassword ?? false) ? ' pr-10' : '' }}"
            {{ ($required ?? false) ? 'required' : '' }}
        />

        {{-- Ojo interactivo --}}
        @if($isPassword ?? false)
            <i data-lucide="eye" 
               id="toggle-{{ $id ?? 'pass' }}"
               data-toggle-password="{{ $id ?? '' }}"
               class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 cursor-pointer transition hover:text-slate-700"></i>
        @endif
    </div>

    @if (!empty($slot))
        <div class="mt-1">
            {{ $slot }}
        </div>
    @endif
</div>