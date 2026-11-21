{{--
    Componente: Select
    Descripción: Menú desplegable con buscador en tiempo real, posicionamiento inteligente
                 (despliega arriba si no hay espacio abajo), soporte de carga remota desde servidor
                 (`remoteUrl`) y sincronización con select nativo.
--}}
<div class="space-y-1.5" data-custom-select id="{{ $id }}-wrapper" @if (!empty($remoteUrl)) data-remote-url="{{ $remoteUrl }}" @endif>
    @if (!empty($label))
    <label for="{{ $id }}" class="block text-sm font-medium text-zinc-900">
        {{ $label }}
    </label>
    @endif

    <div class="relative">
        {{-- Select nativo (oculto pero interactivo con el DOM/Forms/Validadores) --}}
        <select id="{{ $id }}" name="{{ $name }}" data-placeholder="{{ $placeholder ?? 'Seleccionar opción...' }}"
            class="custom-select-native sr-only" tabindex="-1" aria-hidden="true" {{ $required ?? false ? 'required'
            : '' }} {{ $disabled ?? false ? 'disabled' : '' }}>
            <option value="">{{ $placeholder ?? 'Seleccionar opción...' }}</option>
            @foreach (($options ?? []) as $val => $opt)
            @php
            $optValue = is_array($opt) ? ($opt['value'] ?? $val) : $val;
            $optLabel = is_array($opt) ? ($opt['label'] ?? $opt['name'] ?? $optValue) : $opt;
            $optSub = is_array($opt) ? ($opt['sublabel'] ?? '') : '';
            $optIcon = is_array($opt) ? ($opt['icon'] ?? $opt['flag'] ?? '') : '';
            $isSelected = (string) ($selected ?? '') === (string) $optValue;
            @endphp
            <option value="{{ $optValue }}" data-sublabel="{{ $optSub }}" data-icon="{{ $optIcon }}" {{ $isSelected
                ? 'selected' : '' }}>
                {{ $optLabel }}
            </option>
            @endforeach
        </select>

        {{-- Botón Disparador del Select --}}
        <button type="button"
            class="custom-select-trigger w-full flex items-center justify-between px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg  hover:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:border-black focus:ring-black transition duration-200 cursor-pointer"
            {{ $disabled ?? false ? 'disabled' : '' }}>
            <div class="custom-select-display flex items-center gap-2.5 truncate">
                <span class="text-xs text-zinc-400 truncate">{{ $placeholder ?? 'Seleccionar opción...' }}</span>
            </div>

            <i data-lucide="chevron-down"
                class="w-4 h-4 text-zinc-400 transition-transform duration-200 shrink-0 ml-2"></i>
        </button>

        {{-- Menú Desplegable con Buscador y Filtro --}}
        <div
            class="custom-select-dropdown hidden absolute left-0 right-0 bg-white border border-zinc-200 rounded-xl shadow-lg overflow-hidden z-50 p-1.5 space-y-1">
            {{-- Input Buscador --}}
            @if ($search ?? false)
            <div class="px-2 pt-1 pb-1.5 border-b border-zinc-100">
                <div class="flex items-center gap-2 bg-zinc-100 px-2.5 py-1.5 rounded-lg">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-zinc-400 shrink-0"></i>
                    <input type="text" placeholder="{{ !empty($remoteUrl) ? 'Escribir para buscar en el servidor...' : 'Buscar registro...' }}"
                        class="custom-select-search w-full bg-transparent text-xs text-zinc-900 focus:outline-none placeholder-zinc-400" />
                </div>
            </div>
            @endif

            {{-- Lista de Opciones --}}
            <div class="custom-select-options max-h-52 overflow-y-auto space-y-0.5">
                @foreach (($options ?? []) as $val => $opt)
                @php
                $optValue = is_array($opt) ? ($opt['value'] ?? $val) : $val;
                $optLabel = is_array($opt) ? ($opt['label'] ?? $opt['name'] ?? $optValue) : $opt;
                $optSub = is_array($opt) ? ($opt['sublabel'] ?? '') : '';
                $optIcon = is_array($opt) ? ($opt['icon'] ?? $opt['flag'] ?? '') : '';
                $searchMeta = strtolower($optValue . ' ' . $optLabel . ' ' . $optSub);
                $isSelected = (string) ($selected ?? '') === (string) $optValue;
                @endphp
                <div data-select-item data-value="{{ $optValue }}" data-label="{{ $optLabel }}"
                    data-sublabel="{{ $optSub }}" data-icon="{{ $optIcon }}" data-search="{{ $searchMeta }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-zinc-100 cursor-pointer text-xs transition-colors {{ $isSelected ? 'bg-zinc-100' : '' }}">
                    <div class="flex items-center gap-2.5 truncate">
                        @if (!empty($optIcon))
                        <span class="text-base shrink-0">{{ $optIcon }}</span>
                        @endif
                        <span class="font-semibold text-zinc-900 truncate">{{ $optLabel }}</span>
                        @if (!empty($optSub))
                        <span class="text-zinc-500 truncate max-w-35">{{ $optSub }}</span>
                        @endif
                    </div>

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                        class="custom-select-check w-4 h-4 text-zinc-900 shrink-0 ml-2 {{ $isSelected ? '' : 'hidden' }}">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                </div>
                @endforeach

                {{-- Mensaje si no hay resultados --}}
                <div class="custom-select-empty hidden px-3 py-4 text-center text-xs text-zinc-400">
                    No se encontraron registros.
                </div>
            </div>
        </div>
    </div>

    {{-- Contenedor del mensaje de error dinámico --}}
    <p id="{{ $id }}-error"
        class="input-error-msg hidden text-xs font-medium text-red-600 mt-1 transition-all duration-200" role="alert">
    </p>
</div>
