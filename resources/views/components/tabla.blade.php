{{--
    Componente: Tabla de Datos Interactiva
    Descripción: Tabla HTML5 semántica y responsiva con buscador en tiempo real, slot de filtros,
                 selector de registros por página, ordenamiento por columna, estado vacío y paginación.

    Parámetros:
    - id (string, requerido): Identificador único del contenedor de la tabla.
    - titulo (string, opcional): Título de la sección de tabla.
    - subtitulo (string, opcional): Descripción breve debajo del título.
    - placeholderBuscador (string, opcional): Placeholder del campo de búsqueda.
    - columnas (array, opcional): Lista de columnas [['clave' => '...', 'etiqueta' => '...', 'ordenable' => bool, 'alineacion' => 'left|center|right']].
    - opcionesPorPagina (array, opcional): Cantidades permitidas por página (default: [5, 10, 25, 50]).
    - mensajeVacio (string, opcional): Texto a mostrar cuando no hay registros.
--}}
@php
    $tablaId = $id ?? 'tabla-datos';
    $listaColumnas = $columnas ?? [];
    $cantidades = $opcionesPorPagina ?? [5, 10, 25, 50];
@endphp

<section
    id="{{ $tablaId }}"
    data-tabla-contenedor
    class="rounded-2xl border border-slate-200 bg-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] overflow-hidden"
>
    {{-- Encabezado opcional y barra de herramientas --}}
    <header class="border-b border-slate-200 p-5 sm:p-6 space-y-4">
        @if (!empty($titulo) || !empty($subtitulo))
            <div>
                @if (!empty($titulo))
                    <h2 class="text-lg font-semibold text-zinc-900">{{ $titulo }}</h2>
                @endif
                @if (!empty($subtitulo))
                    <p class="mt-0.5 text-sm text-zinc-500">{{ $subtitulo }}</p>
                @endif
            </div>
        @endif

        {{-- Buscador, filtros dinámicos y selector de cantidad --}}
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center flex-1">
                {{-- Buscador en tiempo real --}}
                <div class="relative w-full sm:max-w-xs">
                    <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-500" aria-hidden="true"></i>
                    <input
                        type="search"
                        data-tabla-buscador
                        placeholder="{{ $placeholderBuscador ?? 'Buscar registros...' }}"
                        aria-label="{{ $placeholderBuscador ?? 'Buscar registros' }}"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 py-2 pl-9 pr-3 text-sm text-zinc-900 placeholder:text-zinc-500 transition-all focus:border-marca-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
                    />
                </div>

                {{-- Slot para filtros específicos de cada vista --}}
                @if (!empty($slot) && trim((string) $slot) !== '')
                    <div class="flex flex-wrap items-center gap-2" data-tabla-filtros>
                        {!! $slot !!}
                    </div>
                @endif
            </div>

            {{-- Selector de registros por página --}}
            <div class="flex items-center justify-between gap-2 sm:justify-end">
                <label for="{{ $tablaId }}-por-pagina" class="text-xs font-medium text-zinc-500">Mostrar</label>
                <select
                    id="{{ $tablaId }}-por-pagina"
                    data-tabla-por-pagina
                    class="rounded-lg border border-slate-300 bg-slate-50 px-2.5 py-2 text-sm font-medium text-zinc-900 transition-all focus:border-marca-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2"
                >
                    @foreach ($cantidades as $cantidad)
                        <option value="{{ $cantidad }}" {{ (int) $cantidad === 10 ? 'selected' : '' }}>{{ $cantidad }} por página</option>
                    @endforeach
                </select>
            </div>
        </div>
    </header>

    {{-- Contenedor responsivo de la tabla HTML5 --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50" data-tabla-encabezado>
                <tr>
                    @foreach ($listaColumnas as $columna)
                        @php
                            $clave = $columna['clave'] ?? '';
                            $etiqueta = $columna['etiqueta'] ?? '';
                            $esOrdenable = (bool) ($columna['ordenable'] ?? false);
                            $alineacion = $columna['alineacion'] ?? 'left';
                            $claseAlineacion = $alineacion === 'right' ? 'text-right' : ($alineacion === 'center' ? 'text-center' : 'text-left');
                        @endphp
                        <th scope="col" class="whitespace-nowrap px-4 py-3.5 text-xs font-semibold text-zinc-500 {{ $claseAlineacion }}">
                            @if ($esOrdenable && $clave !== '')
                                <button
                                    type="button"
                                    data-ordenar-columna="{{ $clave }}"
                                    class="inline-flex items-center gap-1.5 rounded font-semibold text-zinc-500 transition-all hover:text-zinc-900 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 cursor-pointer"
                                >
                                    <span>{{ $etiqueta }}</span>
                                    <i data-lucide="arrow-up-down" class="h-3.5 w-3.5" aria-hidden="true"></i>
                                </button>
                            @else
                                <span>{{ $etiqueta }}</span>
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody data-tabla-cuerpo class="divide-y divide-slate-100 bg-white text-zinc-900">
                {{-- Las filas se inyectan dinámicamente desde TablaInteractiva.js --}}
            </tbody>
        </table>
    </div>

    {{-- Estado vacío cuando no hay coincidencias --}}
    <div data-tabla-vacio class="hidden flex-col items-center justify-center px-6 py-12 text-center">
        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 border border-slate-200 text-zinc-500">
            <i data-lucide="inbox" class="h-6 w-6" aria-hidden="true"></i>
        </div>
        <p class="text-sm font-semibold text-zinc-900">Sin resultados</p>
        <p data-tabla-vacio-texto class="mt-1 max-w-sm text-sm text-zinc-500">
            {{ $mensajeVacio ?? 'No se encontraron registros que coincidan con la búsqueda o los filtros aplicados.' }}
        </p>
    </div>

    {{-- Pie de tabla con resumen y controles de paginación --}}
    <footer class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/70 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <p data-tabla-resumen class="text-xs font-medium text-zinc-500" aria-live="polite">
            Mostrando 0 de 0 registros
        </p>
        <nav aria-label="Paginación de tabla" data-tabla-paginacion class="flex flex-wrap items-center gap-1.5">
            {{-- Botones de paginación generados por TablaInteractiva.js --}}
        </nav>
    </footer>
</section>
