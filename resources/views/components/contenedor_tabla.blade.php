@php
    $colSpanClase = $colSpan ?? 'col-span-full';
    $iconoCaja = $iconoClase ?? 'rounded-2xl bg-marca-50 border border-marca-200 text-marca-800';
@endphp

<section
    @if (!empty($idSeccion)) id="{{ $idSeccion }}" @endif
    @if (!empty($atributosSeccion)) {!! $atributosSeccion !!} @endif
    aria-labelledby="{{ $idTitulo }}"
    class="{{ $colSpanClase }} rounded-3xl border border-slate-200 bg-white shadow-[0_15px_30px_rgba(0,0,0,0.05)]"
>
    <header class="flex flex-col gap-3 rounded-t-3xl border-b border-slate-200 bg-slate-50/70 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3.5">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center {{ $iconoCaja }}">
                <i data-lucide="{{ $icono ?? 'table' }}" class="h-5 w-5" aria-hidden="true"></i>
            </div>
            <div>
                <h2 id="{{ $idTitulo }}" class="text-base font-bold text-zinc-900">{!! $titulo !!}</h2>
                @if (!empty($subtitulo))
                    <p class="text-xs text-zinc-500">{{ $subtitulo }}</p>
                @endif
            </div>
        </div>

        @if (!empty($filtros) && trim((string) $filtros) !== '')
            {!! $filtros !!}
        @endif
    </header>

    <div @if (!empty($idEnvoltura)) id="{{ $idEnvoltura }}" @endif class="overflow-x-auto px-6 py-5">
        {!! $slot ?? '' !!}
    </div>
</section>
