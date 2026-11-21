/**
 * configDataTable.js
 * Configuración reutilizable de DataTables 3.1.5 en JavaScript 100% nativo (cero jQuery).
 * Incluye traducción completa al español, formateadores, badges semánticos,
 * botones con estética sólida de autenticacion.blade.php y conectores server-side / client-side.
 */

import { inicializarSelectsPersonalizados } from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';

export const IDIOMA_ES = {
  processing: 'Procesando registros...',
  search: 'Buscar:',
  searchPlaceholder: 'Filtrar registros...',
  lengthMenu: 'Mostrar _MENU_ registros',
  info: 'Mostrando _START_ al _END_ de _TOTAL_ registros',
  infoEmpty: 'Mostrando 0 registros',
  infoFiltered: '(filtrado de _MAX_ registros totales)',
  loadingRecords: 'Cargando datos...',
  zeroRecords: 'No se encontraron registros que coincidan con la búsqueda.',
  emptyTable: 'No hay registros disponibles en esta tabla.',
  paginate: {
    first: 'Primero',
    previous: 'Anterior',
    next: 'Siguiente',
    last: 'Último',
  },
  aria: {
    sortAscending: ': activar para ordenar ascendentemente',
    sortDescending: ': activar para ordenar descendentemente',
  },
};

/**
 * Escapa caracteres HTML para renderizado seguro de celdas y atributos.
 * @param {unknown} valor
 * @returns {string}
 */
export const escaparHtml = (valor) =>
  String(valor ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');

/**
 * Formatea fechas ISO (YYYY-MM-DD o timestamp) a DD/MM/YYYY.
 * @param {string|null|undefined} fechaIso
 * @returns {string}
 */
export const formatearFecha = (fechaIso) => {
  if (!fechaIso || typeof fechaIso !== 'string') return '—';
  const parteFecha = fechaIso.trim().slice(0, 10);
  const partes = parteFecha.split('-');
  if (partes.length !== 3) return escaparHtml(fechaIso);
  const [anio, mes, dia] = partes;
  if (!anio || !mes || !dia) return escaparHtml(fechaIso);
  return `${dia}/${mes}/${anio}`;
};

/**
 * Genera un badge semántico siguiendo la regla 50 / 200 / 800-900 de Atlas.
 * @param {string} texto
 * @param {'green'|'red'|'yellow'|'sky'|'marca'|'neutro'} [variante='neutro']
 * @returns {string}
 */
export const crearBadge = (texto, variante = 'neutro') => {
  const estilos = {
    green: 'bg-green-50 border-green-200 text-green-800',
    red: 'bg-red-50 border-red-200 text-red-800',
    yellow: 'bg-yellow-50 border-yellow-200 text-yellow-900',
    sky: 'bg-sky-50 border-sky-200 text-sky-800',
    marca: 'bg-marca-50 border-marca-200 text-marca-800',
    neutro: 'bg-slate-50 border-slate-200 text-zinc-900',
  };

  const clase = estilos[variante] || estilos.neutro;
  return `<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap ${clase}">${escaparHtml(texto)}</span>`;
};

/**
 * Botón o enlace compacto para acciones de fila siguiendo la estética sólida de #btn-entrar (autenticacion.blade.php).
 * @param {Object} opciones
 * @param {string} [opciones.accion='']
 * @param {string} opciones.etiqueta
 * @param {string} [opciones.icono='']
 * @param {'zinc'|'marca'|'red'} [opciones.variante='zinc']
 * @param {string|null} [opciones.href=null]
 * @param {string|number} [opciones.id='']
 * @param {boolean} [opciones.deshabilitado=false]
 * @param {string} [opciones.titulo='']
 * @param {number|string|null} [opciones.contador=null]
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
    zinc: 'bg-zinc-900 hover:bg-zinc-800 focus-visible:outline-zinc-950',
    marca: 'bg-marca hover:bg-marca-700 focus-visible:outline-marca',
    red: 'bg-red-600 hover:bg-red-700 focus-visible:outline-red-600',
  };

  const clases = [
    'inline-flex items-center justify-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-all duration-200 focus-visible:outline focus-visible:outline-offset-2 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer whitespace-nowrap',
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
 * Envuelve el `<select class="dt-input">` nativo de DataTables (`.dt-length`)
 * dentro del componente `data-custom-select` conservando su nodo DOM y listener de DataTables.
 * @param {HTMLElement} tablaEl
 */
const transformarSelectLongitudDataTable = (tablaEl) => {
  const contenedorDt = tablaEl.closest('.dt-container');
  const selectLongitud = contenedorDt?.querySelector('.dt-length select');
  if (!selectLongitud || selectLongitud.closest('[data-custom-select]')) return;

  const valorActual = String(selectLongitud.value || '10');
  const opciones = [...selectLongitud.options].map((opt) => ({
    valor: String(opt.value),
    texto: String(opt.textContent || opt.value).trim(),
  }));

  const envoltura = document.createElement('div');
  envoltura.className = 'inline-block w-24 align-middle mx-1';
  envoltura.setAttribute('data-custom-select', '');

  const relativo = document.createElement('div');
  relativo.className = 'relative';
  envoltura.appendChild(relativo);

  selectLongitud.parentNode?.insertBefore(envoltura, selectLongitud);
  selectLongitud.className = 'custom-select-native sr-only';
  selectLongitud.setAttribute('data-placeholder', valorActual);
  selectLongitud.setAttribute('tabindex', '-1');
  selectLongitud.setAttribute('aria-hidden', 'true');
  relativo.appendChild(selectLongitud);

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

  relativo.insertAdjacentHTML(
    'beforeend',
    `
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
    `
  );

  inicializarSelectsPersonalizados();
  window.lucide?.createIcons();
};

/**
 * Inicializa una instancia DataTable 3.1.5 conectada al backend por JSON POST + Fetch nativo.
 * @param {string} selector
 * @param {Object} opciones
 */
export const crearTablaServidor = (
  selector,
  {
    url,
    columnas,
    ordenInicial = [[0, 'asc']],
    obtenerFiltros = null,
    longitudPagina = 10,
    alCargar = null,
  }
) => {
  const tablaEl = document.querySelector(selector);
  if (!tablaEl) return null;

  if (typeof window.DataTable !== 'function') {
    mostrarToast('error', 'No se pudo cargar la librería DataTables 3.1.5.');
    return null;
  }

  if (window.DataTable.ext) {
    window.DataTable.ext.errMode = 'none';
  }

  let controladorActivo = null;

  const instancia = new window.DataTable(tablaEl, {
    serverSide: true,
    processing: true,
    autoWidth: false,
    pageLength: longitudPagina,
    lengthMenu: [
      [10, 25, 50, 100],
      [10, 25, 50, 100],
    ],
    language: IDIOMA_ES,
    order: ordenInicial,
    columns: columnas,
    ajax: async (datosSolicitud, callback) => {
      if (controladorActivo) {
        controladorActivo.abort();
      }
      controladorActivo = new AbortController();

      const filtros = typeof obtenerFiltros === 'function' ? obtenerFiltros() : {};

      try {
        const respuesta = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
          },
          body: JSON.stringify({ ...datosSolicitud, ...filtros }),
          signal: controladorActivo.signal,
        });

        if (respuesta.status === 401) {
          window.location.href = '/autenticacion';
          return;
        }

        const json = await respuesta.json().catch(() => null);

        if (!respuesta.ok || !json || json.error) {
          mostrarToast('error', json?.error || json?.message || 'Error al cargar los registros.');
          callback({
            draw: datosSolicitud.draw,
            recordsTotal: 0,
            recordsFiltered: 0,
            data: [],
          });
          return;
        }

        if (typeof alCargar === 'function') {
          alCargar(json);
        }

        callback(json);
      } catch (error) {
        if (error?.name === 'AbortError') {
          return;
        }
        mostrarToast('error', 'Error de conexión al consultar los registros.');
        callback({
          draw: datosSolicitud.draw,
          recordsTotal: 0,
          recordsFiltered: 0,
          data: [],
        });
      }
    },
  });

  transformarSelectLongitudDataTable(tablaEl);

  instancia.on('draw', () => {
    window.lucide?.createIcons();
  });

  return instancia;
};

/**
 * Inicializa una instancia DataTable 3.1.5 en modo cliente (cero jQuery).
 * @param {string} selector
 * @param {Object} opciones
 */
export const crearTablaCliente = (
  selector,
  { datos = [], columnas, ordenInicial = [[0, 'asc']], longitudPagina = 10 }
) => {
  const tablaEl = document.querySelector(selector);
  if (!tablaEl) return null;

  if (typeof window.DataTable !== 'function') {
    mostrarToast('error', 'No se pudo cargar la librería DataTables 3.1.5.');
    return null;
  }

  if (window.DataTable.ext) {
    window.DataTable.ext.errMode = 'none';
  }

  const instancia = new window.DataTable(tablaEl, {
    data: datos,
    columns: columnas,
    order: ordenInicial,
    pageLength: longitudPagina,
    lengthMenu: [
      [10, 25, 50, 100],
      [10, 25, 50, 100],
    ],
    autoWidth: false,
    language: IDIOMA_ES,
  });

  transformarSelectLongitudDataTable(tablaEl);

  instancia.on('draw', () => {
    window.lucide?.createIcons();
  });

  return instancia;
};
