{{--
    Componente: Botón de Acción
    Descripción: Botón o enlace estilizado y accesible para acciones de fila o cabecera
                 (mostrar, editar, eliminar o personalizada) con delegación vía data-accion y data-id.

    Parámetros:
    - accion (string, opcional): Nombre de la acción ('mostrar', 'editar', 'eliminar', etc.).
    - id (string|int, opcional): Identificador del registro asociado.
    - etiqueta (string, opcional): Texto visible o accesible del botón.
    - icono (string, opcional): Nombre del icono Lucide (ej: 'eye', 'pencil', 'trash-2').
    - variante (string, opcional): 'marca' | 'sky' | 'yellow' | 'red' | 'green' | 'neutro'.
    - href (string, opcional): Si se envía, renderiza un enlace <a> en lugar de <button>.
    - soloIcono (bool, opcional): Oculta el texto visible manteniendo aria-label y title.
--}}
@php
    $accionValor = $accion ?? 'accion';
    $etiquetaTexto = $etiqueta ?? 'Acción';
    $varianteClave = $variante ?? 'neutro';
    $soloIconoActivo = (bool) ($soloIcono ?? false);

    $clasesVariante = [
        'marca' => 'bg-marca-50 border-marca-200 text-marca-800 hover:bg-marca-100 focus:ring-marca-500',
        'sky' => 'bg-sky-50 border-sky-200 text-sky-800 hover:bg-sky-100 focus:ring-sky-500',
        'yellow' => 'bg-yellow-50 border-yellow-200 text-yellow-900 hover:bg-yellow-100 focus:ring-yellow-500',
        'red' => 'bg-red-50 border-red-200 text-red-800 hover:bg-red-100 focus:ring-red-500',
        'green' => 'bg-green-50 border-green-200 text-green-800 hover:bg-green-100 focus:ring-green-500',
        'neutro' => 'bg-white border-slate-200 text-zinc-900 hover:bg-slate-50 focus:ring-marca-500',
    ];

    $estiloVariante = $clasesVariante[$varianteClave] ?? $clasesVariante['neutro'];
    $clasesBase = ($soloIconoActivo ? 'p-2' : 'w-full sm:w-auto px-3 py-1.5')
        . ' inline-flex items-center justify-center gap-1.5 rounded-lg border text-sm font-medium transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 active:scale-[0.98] '
        . $estiloVariante;
@endphp

@if (!empty($href))
    <a
        href="{{ $href }}"
        data-accion="{{ $accionValor }}"
        @if (isset($id) && $id !== '') data-id="{{ $id }}" @endif
        aria-label="{{ $etiquetaTexto }}"
        title="{{ $etiquetaTexto }}"
        class="{{ $clasesBase }}"
    >
        @if (!empty($icono))
            <i data-lucide="{{ $icono }}" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
        @endif
        @if (!$soloIconoActivo)
            <span>{{ $etiquetaTexto }}</span>
        @endif
    </a>
@else
    <button
        type="button"
        data-accion="{{ $accionValor }}"
        @if (isset($id) && $id !== '') data-id="{{ $id }}" @endif
        aria-label="{{ $etiquetaTexto }}"
        title="{{ $etiquetaTexto }}"
        class="{{ $clasesBase }}"
    >
        @if (!empty($icono))
            <i data-lucide="{{ $icono }}" class="h-4 w-4 shrink-0" aria-hidden="true"></i>
        @endif
        @if (!$soloIconoActivo)
            <span>{{ $etiquetaTexto }}</span>
        @endif
    </button>
@endif
