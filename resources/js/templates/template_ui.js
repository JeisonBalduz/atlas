import { escaparHtml } from '/js/utils/dom.js';

/**
 * Genera un badge semántico siguiendo la paleta institucional de Atlas.
 * @param {string} texto
 * @param {'green'|'red'|'yellow'|'sky'|'marca'|'neutro'} [variante='neutro']
 * @returns {string}
 */
export const crearBadge = (texto, variante = 'neutro') => {
  const estilos = {
    green: 'bg-green-400/20  text-green-800',
    red: 'bg-red-400/20  text-red-800',
    yellow: 'bg-yellow-400/20  text-yellow-900',
    sky: 'bg-sky-400/20  text-sky-800',
    marca: 'bg-marca-400/20  text-marca-800',
    neutro: 'bg-slate-400/20  text-zinc-900',
  };

  const clase = estilos[variante] || estilos.neutro;
  return `<span class="inline-flex items-center rounded-md border-none px-2.5 py-0.5 text-xs font-medium whitespace-nowrap ${clase}">${escaparHtml(texto)}</span>`;
};

/**
 * Botón o enlace compacto para acciones de fila.
 * @param {Object} opciones
 * @returns {string}
 */
export const crearBotonAccion = ({
  accion = '',
  etiqueta,
  icono = '',
  variante = 'zinc',
  href = null,
  id = '',
  deshabilitado = false,
  titulo = '',
  contador = null,
}) => {
  const variantes = {
    zinc: 'bg-zinc-900 hover:bg-zinc-800 text-white focus-visible:outline-zinc-950',
    marca: 'bg-marca hover:bg-marca-700 text-white focus-visible:outline-marca',
    red: 'bg-red-600 hover:bg-red-700 text-white focus-visible:outline-red-600',
    green: 'bg-green-600 hover:bg-green-700 text-white focus-visible:outline-green-600',
    yellow: 'bg-yellow-400 hover:bg-yellow-500 text-zinc-950 focus-visible:outline-yellow-500',
  };

  const clases = [
    'inline-flex items-center justify-center gap-1.5 rounded-lg px-2 py-2 text-xs font-semibold shadow-sm transition-all duration-200 focus-visible:outline focus-visible:outline-offset-2 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer whitespace-nowrap',
    variantes[variante] || variantes.zinc,
  ].join(' ');

  const iconoHtml = icono ? `<i data-lucide="${escaparHtml(icono)}" class="h-3.5 w-3.5 shrink-0" aria-hidden="true"></i>` : '';
  const contadorHtml =
    contador !== null && contador !== undefined
      ? `<span class="rounded-md bg-white/20 px-1.5 py-0.5 text-[11px] font-bold leading-none">${escaparHtml(contador)}</span>`
      : '';
  const titleAttr = titulo ? `title="${escaparHtml(titulo)}"` : '';
  const ariaLabel = `aria-label="${escaparHtml(titulo || etiqueta)}"`;

  if (href && !deshabilitado) {
    return `<a href="${escaparHtml(href)}" class="${clases}" ${titleAttr} ${ariaLabel}>${iconoHtml}<span>${escaparHtml(etiqueta)}</span>${contadorHtml}</a>`;
  }

  return `<button type="button" data-accion="${escaparHtml(accion)}" data-id="${escaparHtml(id)}" class="${clases}" ${titleAttr} ${ariaLabel} ${deshabilitado ? 'disabled' : ''}>${iconoHtml}<span>${escaparHtml(etiqueta)}</span>${contadorHtml}</button>`;
};

/**
 * Envuelve un arreglo de botones HTML en el contenedor de acciones de fila.
 * @param {string[]} botonesHtml
 * @returns {string}
 */
export const crearGrupoBotones = (botonesHtml = []) =>
  `<div class="inline-flex items-center justify-end gap-1.5">${botonesHtml.filter(Boolean).join('')}</div>`;

/**
 * Genera una caja de alerta para modales de confirmación.
 * @param {Object} opciones
 * @returns {string}
 */
export const crearAlertaConfirmacion = ({ mensajeHtml, variante = 'red', icono = '' }) => {
  const estilos = {
    red: 'border-red-200 bg-red-50 text-red-800',
    green: 'border-green-200 bg-green-50 text-green-800',
  };
  const clase = estilos[variante] || estilos.red;

  if (!icono) {
    return `<div class="rounded-xl border ${clase} p-4 text-sm">${mensajeHtml}</div>`;
  }

  const colorIcono = variante === 'green' ? 'text-green-700' : 'text-red-700';
  return `
    <div class="flex items-start gap-3 rounded-xl border ${clase} p-4 text-sm">
      <i data-lucide="${escaparHtml(icono)}" class="mt-0.5 h-5 w-5 shrink-0 ${colorIcono}" aria-hidden="true"></i>
      <p>${mensajeHtml}</p>
    </div>
  `;
};

/**
 * Genera una grilla <dl> de tarjetas de detalle.
 * @param {Array<Object>} items
 * @param {number} [columnas=2]
 * @returns {string}
 */
export const crearListaDetalle = (items = [], columnas = 2) => {
  const claseCols = columnas === 2 ? 'sm:grid-cols-2' : 'sm:grid-cols-1';
  const tarjetas = items
    .map(({ etiqueta, valorHtml, subvalorHtml = '', anchoCompleto = false }) => `
      <div class="${anchoCompleto ? 'sm:col-span-2 ' : ''}rounded-xl border border-slate-200 bg-slate-50 p-3.5">
        <dt class="text-xs font-medium text-zinc-500">${escaparHtml(etiqueta)}</dt>
        <dd class="mt-1 font-medium text-zinc-900">${valorHtml}</dd>
        ${subvalorHtml ? `<dd class="text-xs text-zinc-500">${subvalorHtml}</dd>` : ''}
      </div>
    `)
    .join('');

  return `<dl class="grid grid-cols-1 gap-3 ${claseCols} text-sm">${tarjetas}</dl>`;
};

/**
 * Construye el HTML de un select con la estructura idéntica a `components/select.blade.php`.
 * @param {Object} opciones
 */
export const crearMarkupSelectPersonalizado = ({
  id,
  nombre,
  etiqueta = '',
  opciones = {},
  valorSeleccionado = '',
  placeholder = 'Seleccionar opción...',
  requerido = false,
  buscador = false,
}) => {
  const entradas = Array.isArray(opciones)
    ? opciones.map((v) => [String(v), String(v)])
    : Object.entries(opciones || {});

  const valorStr = String(valorSeleccionado ?? '');
  const opcionesNativas = entradas
    .map(
      ([val, texto]) =>
        `<option value="${escaparHtml(val)}" ${valorStr === String(val) ? 'selected' : ''}>${escaparHtml(texto)}</option>`
    )
    .join('');

  const itemsMenu = entradas
    .map(([val, texto]) => {
      const seleccionado = valorStr === String(val);
      const meta = `${val} ${texto}`.toLowerCase();
      return `
        <div data-select-item
             data-value="${escaparHtml(val)}"
             data-label="${escaparHtml(texto)}"
             data-sublabel=""
             data-icon=""
             data-search="${escaparHtml(meta)}"
             class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-zinc-100 cursor-pointer text-xs transition-colors ${seleccionado ? 'bg-zinc-100' : ''}">
          <div class="flex items-center gap-2.5 truncate">
            <span class="font-semibold text-zinc-900 truncate">${escaparHtml(texto)}</span>
          </div>
          <i data-lucide="check" class="custom-select-check w-4 h-4 text-zinc-900 shrink-0 ml-2 ${seleccionado ? '' : 'hidden'}"></i>
        </div>
      `;
    })
    .join('');

  const buscadorHtml = buscador
    ? `
      <div class="px-2 pt-1 pb-1.5 border-b border-zinc-100">
        <div class="flex items-center gap-2 bg-zinc-100 px-2.5 py-1.5 rounded-lg">
          <i data-lucide="search" class="w-3.5 h-3.5 text-zinc-400 shrink-0"></i>
          <input type="text" placeholder="Buscar registro..." class="custom-select-search w-full bg-transparent text-xs text-zinc-900 focus:outline-none placeholder-zinc-400" />
        </div>
      </div>
    `
    : '';

  const labelHtml = etiqueta
    ? `<label for="${escaparHtml(id)}" class="block text-sm font-medium text-zinc-900">${escaparHtml(etiqueta)}</label>`
    : '';

  return `
    <div class="space-y-1.5" data-custom-select id="${escaparHtml(id)}-wrapper">
      ${labelHtml}
      <div class="relative">
        <select id="${escaparHtml(id)}" name="${escaparHtml(nombre)}" data-placeholder="${escaparHtml(placeholder)}" class="custom-select-native sr-only" tabindex="-1" aria-hidden="true" ${requerido ? 'required' : ''}>
          <option value="">${escaparHtml(placeholder)}</option>
          ${opcionesNativas}
        </select>
        <button type="button" class="custom-select-trigger w-full flex items-center justify-between px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg hover:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:border-black focus:ring-black transition duration-200 cursor-pointer">
          <div class="custom-select-display flex items-center gap-2.5 truncate">
            <span class="text-xs text-zinc-400 truncate">${escaparHtml(placeholder)}</span>
          </div>
          <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400 transition-transform duration-200 shrink-0 ml-2"></i>
        </button>
        <div class="custom-select-dropdown hidden absolute left-0 right-0 bg-white border border-zinc-200 rounded-xl shadow-lg overflow-hidden z-50 p-1.5 space-y-1">
          ${buscadorHtml}
          <div class="custom-select-options max-h-52 overflow-y-auto space-y-0.5">
            ${itemsMenu}
            <div class="custom-select-empty hidden px-3 py-4 text-center text-xs text-zinc-400">No se encontraron registros.</div>
          </div>
        </div>
      </div>
    </div>
  `;
};

/**
 * Genera el HTML interno para envolver el select de longitud de DataTables.
 * @param {Array<{valor: string, texto: string}>} opciones
 * @param {string} valorActual
 * @returns {string}
 */
export const crearMarkupSelectLongitud = (opciones, valorActual) => {
  const itemsHtml = opciones
    .map((opt) => {
      const esActual = opt.valor === valorActual;
      return `
        <div data-select-item
             data-value="${escaparHtml(opt.valor)}"
             data-label="${escaparHtml(opt.texto)}"
             data-sublabel=""
             data-icon=""
             data-search="${escaparHtml(opt.texto.toLowerCase())}"
             class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-zinc-100 cursor-pointer text-xs transition-colors ${esActual ? 'bg-zinc-100' : ''}">
          <div class="flex items-center gap-2.5 truncate">
            <span class="font-semibold text-zinc-900 truncate">${escaparHtml(opt.texto)}</span>
          </div>
          <i data-lucide="check" class="custom-select-check w-4 h-4 text-zinc-900 shrink-0 ml-2 ${esActual ? '' : 'hidden'}"></i>
        </div>
      `;
    })
    .join('');

  return `
    <button type="button" class="custom-select-trigger w-full flex items-center justify-between px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg hover:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:border-black focus:ring-black transition duration-200 cursor-pointer">
      <div class="custom-select-display flex items-center gap-2 truncate">
        <span class="text-xs font-semibold text-zinc-900 truncate">${escaparHtml(valorActual)}</span>
      </div>
      <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400 transition-transform duration-200 shrink-0 ml-1.5"></i>
    </button>
    <div class="custom-select-dropdown hidden absolute left-0 right-0 bg-white border border-zinc-200 rounded-xl shadow-lg overflow-hidden z-50 p-1.5 space-y-1">
      <div class="custom-select-options max-h-52 overflow-y-auto space-y-0.5">
        ${itemsHtml}
        <div class="custom-select-empty hidden px-3 py-2 text-center text-xs text-zinc-400">Sin opciones</div>
      </div>
    </div>
  `;
};
