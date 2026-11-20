{{--
    Componente: Modal Accesible (<dialog> nativo)
    Descripción: Ventana modal semántica con closedby="any", contención de scroll en backdrop y
                 pie configurable para botones de cancelación y confirmación.

    Parámetros:
    - id (string, requerido): Identificador único del <dialog>.
    - titulo (string, opcional): Título inicial del modal.
    - subtitulo (string, opcional): Subtítulo descriptivo inicial.
    - tamano (string, opcional): 'sm' | 'md' | 'lg' | 'xl' (default: 'md').
    - mostrarPie (bool, opcional): Muestra el pie con botones (default: true).
    - textoCancelar (string, opcional): Etiqueta del botón de cierre/cancelación (default: 'Cerrar').
    - textoConfirmar (string, opcional): Si se define, renderiza el botón de acción principal.
    - varianteConfirmar (string, opcional): 'marca' | 'red' | 'green' (default: 'marca').
--}}
@php
    $modalId = $id ?? 'modal-sistema';
    $tamanos = [
        'sm' => 'max-w-md',
        'md' => 'max-w-xl',
        'lg' => 'max-w-3xl',
        'xl' => 'max-w-5xl',
    ];
    $claseTamano = $tamanos[$tamano ?? 'md'] ?? $tamanos['md'];

    $variantesConfirmar = [
        'marca' => 'bg-marca text-white hover:bg-marca-700 focus:ring-marca-500',
        'red' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'green' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    ];
    $claseConfirmar = $variantesConfirmar[$varianteConfirmar ?? 'marca'] ?? $variantesConfirmar['marca'];
    $mostrarPieModal = $mostrarPie ?? true;
@endphp

<dialog
    id="{{ $modalId }}"
    closedby="any"
    aria-labelledby="{{ $modalId }}-titulo"
    class="m-auto w-[calc(100%-2rem)] {{ $claseTamano }} rounded-2xl border border-slate-200 bg-white p-0 text-zinc-900 shadow-[0_15px_30px_rgba(0,0,0,0.05)] overscroll-contain backdrop:bg-zinc-900/50 backdrop:backdrop-blur-xs backdrop:overflow-hidden backdrop:overscroll-contain"
>
    {{-- Cabecera del modal --}}
    <header class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-4">
        <div class="min-w-0">
            <h2 id="{{ $modalId }}-titulo" data-modal-titulo class="text-lg font-semibold text-zinc-900">
                {{ $titulo ?? 'Detalle' }}
            </h2>
            <p id="{{ $modalId }}-subtitulo" data-modal-subtitulo class="mt-0.5 text-sm text-zinc-500 {{ empty($subtitulo) ? 'hidden' : '' }}">
                {{ $subtitulo ?? '' }}
            </p>
        </div>

        <button
            type="button"
            data-modal-cerrar
            aria-label="Cerrar ventana"
            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-transparent text-zinc-500 transition-all hover:bg-slate-100 hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer"
        >
            <i data-lucide="x" class="h-4 w-4" aria-hidden="true"></i>
        </button>
    </header>

    {{-- Cuerpo principal del modal --}}
    <div data-modal-cuerpo class="max-h-[70vh] overflow-y-auto px-6 py-5">
        {!! $slot ?? '' !!}
    </div>

    {{-- Pie de acciones del modal --}}
    @if ($mostrarPieModal)
        <footer data-modal-pie class="flex flex-col-reverse gap-2.5 border-t border-slate-200 bg-slate-50/70 px-6 py-4 sm:flex-row sm:justify-end">
            <button
                type="button"
                data-modal-cerrar
                class="w-full sm:w-auto rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-zinc-900 transition-all hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer"
            >
                {{ $textoCancelar ?? 'Cerrar' }}
            </button>

            @if (!empty($textoConfirmar))
                <button
                    type="button"
                    data-modal-confirmar
                    class="w-full sm:w-auto rounded-lg px-4 py-2 text-sm font-medium shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 cursor-pointer {{ $claseConfirmar }}"
                >
                    {{ $textoConfirmar }}
                </button>
            @endif
        </footer>
    @endif
</dialog>
