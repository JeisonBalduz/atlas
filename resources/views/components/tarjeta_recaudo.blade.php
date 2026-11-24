@php
    $cargado = !empty($rec['cargado']);
    $obligatorio = !empty($rec['obligatorio']);
    $esTitular = !empty($rec['es_titular']);
    $bordeClase = $cargado
        ? 'border-green-200 bg-green-50/40'
        : ($obligatorio ? 'border-yellow-300 bg-yellow-50/40' : 'border-slate-200 bg-slate-50/40');
    $tipoId = $rec['tipo_documento_id'] ?? '';
    $inputId = ($prefijoId ?? 'doc') . '-' . $tipoId;
    $permitirSubidaDirecta = !empty($subidaDirecta);
@endphp

<article data-recaudo-card data-tipo-documento-id="{{ $tipoId }}" class="rounded-2xl border {{ $bordeClase }} p-4 flex flex-col justify-between gap-3 transition-colors">
    <div class="space-y-1.5">
        <div class="flex items-start justify-between gap-2">
            <h3 class="text-sm font-semibold text-zinc-900">{{ $rec['etiqueta'] }}</h3>
            <div class="flex items-center gap-1.5 shrink-0">
                @if ($cargado)
                    <span class="inline-flex items-center rounded-md bg-green-400/20 px-2 py-0.5 text-[11px] font-semibold text-green-800 whitespace-nowrap">Vigente</span>
                @elseif ($obligatorio)
                    <span class="inline-flex items-center rounded-md bg-yellow-400/20 px-2 py-0.5 text-[11px] font-semibold text-yellow-900 whitespace-nowrap">Obligatorio</span>
                @else
                    <span class="inline-flex items-center rounded-md bg-slate-200/70 px-2 py-0.5 text-[11px] font-semibold text-zinc-700 whitespace-nowrap">Opcional</span>
                @endif
            </div>
        </div>

        @if (!empty($rec['motivo']))
            <p class="text-xs text-zinc-500">{{ $rec['motivo'] }}</p>
        @endif

        @if ($cargado && !empty($rec['url_descarga']))
            <div class="flex items-center justify-between gap-2 pt-1 text-xs">
                <span class="truncate text-zinc-700 font-medium" title="{{ $rec['nombre_original'] ?? '' }}">{{ $rec['nombre_original'] ?? 'Archivo vigente' }}</span>
                <a href="{{ $rec['url_descarga'] }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-1 font-semibold text-marca hover:underline whitespace-nowrap">
                    <i data-lucide="external-link" class="h-3.5 w-3.5" aria-hidden="true"></i>
                    <span>Ver</span>
                </a>
            </div>
        @endif
    </div>

    @if ($esTitular)
        <p class="rounded-lg bg-sky-50 px-3 py-2 text-xs text-sky-800">
            Se verifica en el expediente del trabajador titular.
        </p>
    @else
        <div class="space-y-2">
            <div>
                <label for="{{ $inputId }}" class="block text-[11px] font-medium text-zinc-600 mb-1">
                    {{ $cargado ? 'Actualizar archivo (pasa anterior a No vigente)' : 'Adjuntar archivo (PDF/JPG/PNG)' }}
                </label>
                <input id="{{ $inputId }}"
                    name="documentos[{{ $tipoId }}]"
                    data-tipo-documento="{{ $tipoId }}"
                    type="file"
                    accept=".pdf,.jpg,.jpeg,.png,.webp"
                    class="{{ $claseInput ?? 'atlas-doc-input' }} w-full rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-zinc-700 file:mr-2.5 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-white hover:file:bg-zinc-800 cursor-pointer" />
            </div>

            @if ($permitirSubidaDirecta)
                <button type="button"
                    data-subir-documento-directo="{{ $tipoId }}"
                    class="hidden w-full items-center justify-center gap-1.5 rounded-lg bg-marca px-3 py-1.5 text-xs font-semibold text-white transition-all hover:bg-marca-700 cursor-pointer">
                    <i data-lucide="upload-cloud" class="h-3.5 w-3.5" aria-hidden="true"></i>
                    <span>{{ $cargado ? 'Actualizar vigencia ahora' : 'Cargar documento ahora' }}</span>
                </button>
            @endif
        </div>
    @endif
</article>
