{{--
    Componente: Alerta Inline de Formulario (Atlas)
    Diseño: Banner rectangular ligeramente redondeado, ancho completo, alto contraste y borde definido.
    Se muestra únicamente ante errores (junto con el toast) y permanece oculto cuando todo sale bien.
--}}
<div
    @if (!empty($id)) id="{{ $id }}" @endif
    data-alerta-formulario
    role="alert"
    aria-live="assertive"
    class="col-span-full hidden w-full rounded-xl border-2 border-red-700 bg-red-600 px-4 py-3.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all"
>
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-start gap-2.5 min-w-0 flex-1">
            <i data-lucide="alert-circle" class="mt-0.5 h-5 w-5 shrink-0 text-white" aria-hidden="true"></i>
            <p data-alerta-texto class="leading-relaxed wrap-break-word"></p>
        </div>
        <button
            type="button"
            data-alerta-cerrar
            aria-label="Cerrar alerta"
            class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-white/85 transition-colors hover:bg-red-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-white cursor-pointer"
        >
            <i data-lucide="x" class="h-4 w-4" aria-hidden="true"></i>
        </button>
    </div>
</div>
