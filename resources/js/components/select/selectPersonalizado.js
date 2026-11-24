import { Solicitud } from '/js/class/Solicitud.js';

const ICONO_CHECK_SVG =
  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="custom-select-check w-4 h-4 text-zinc-900 shrink-0 ml-2 %CLASS%"><path d="M20 6 9 17l-5-5"/></svg>';

const normalizarOpciones = (opciones) => {
  if (Array.isArray(opciones)) {
    return opciones.map((item) => {
      if (typeof item === 'object' && item !== null) {
        return {
          valor: String(item.valor ?? item.value ?? item.id ?? ''),
          etiqueta: String(item.etiqueta ?? item.label ?? item.nombre ?? item.name ?? item.texto ?? item.text ?? item.valor ?? item.value ?? ''),
          subetiqueta: String(item.subetiqueta || item.sublabel || ''),
          icono: String(item.icono || item.icon || item.flag || ''),
        };
      }
      return { valor: String(item), etiqueta: String(item), subetiqueta: '', icono: '' };
    });
  }

  if (typeof opciones === 'object' && opciones !== null) {
    return Object.entries(opciones).map(([clave, valor]) => {
      if (typeof valor === 'object' && valor !== null) {
        return {
          valor: String(valor.valor ?? valor.value ?? clave),
          etiqueta: String(valor.etiqueta ?? valor.label ?? valor.nombre ?? valor.name ?? clave),
          subetiqueta: String(valor.subetiqueta || valor.sublabel || ''),
          icono: String(valor.icono || valor.icon || ''),
        };
      }
      return { valor: String(clave), etiqueta: String(valor), subetiqueta: '', icono: '' };
    });
  }

  return [];
};

const construirHtmlOpciones = (normalizadas, valorFinal) => {
  const filas = normalizadas
    .map((opcion) => {
      const esCoincidencia = opcion.valor === valorFinal;
      const metaBusqueda = `${opcion.valor} ${opcion.etiqueta} ${opcion.subetiqueta}`.toLowerCase();
      const checkSvg = ICONO_CHECK_SVG.replace('%CLASS%', esCoincidencia ? '' : 'hidden');

      return `
        <div data-select-item
             data-value="${opcion.valor}"
             data-label="${opcion.etiqueta}"
             data-sublabel="${opcion.subetiqueta}"
             data-icon="${opcion.icono}"
             data-search="${metaBusqueda}"
             class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-zinc-100 cursor-pointer text-xs transition-colors ${esCoincidencia ? 'bg-zinc-100' : ''}">
          <div class="flex items-center gap-2.5 truncate">
            ${opcion.icono ? `<span class="text-base shrink-0">${opcion.icono}</span>` : ''}
            <span class="font-semibold text-zinc-900 truncate">${opcion.etiqueta}</span>
            ${opcion.subetiqueta ? `<span class="text-zinc-500 truncate max-w-35">${opcion.subetiqueta}</span>` : ''}
          </div>
          ${checkSvg}
        </div>
      `;
    })
    .join('');

  return `
    ${filas}
    <div class="custom-select-empty ${normalizadas.length === 0 ? '' : 'hidden'} px-3 py-4 text-center text-xs text-zinc-400">
      No se encontraron registros.
    </div>
  `;
};

const renderDisplaySeleccionado = ({ visualizador, selectNativo, valor, etiqueta, subetiqueta = '', icono = '' }) => {
  if (!valor) {
    const placeholder = selectNativo.getAttribute('data-placeholder') || 'Seleccionar opción...';
    visualizador.innerHTML = `<span class="text-xs text-zinc-400 truncate">${placeholder}</span>`;
    return;
  }

  visualizador.innerHTML = `
    <div class="flex items-center gap-2.5 truncate">
      ${icono ? `<span class="text-base shrink-0">${icono}</span>` : ''}
      <span class="text-xs font-semibold text-zinc-900 truncate">${etiqueta}</span>
      ${subetiqueta ? `<span class="text-xs text-zinc-500 truncate max-w-30">${subetiqueta}</span>` : ''}
    </div>
  `;
};

const posicionarDropdown = (botonDisparador, menuDesplegable) => {
  const rect = botonDisparador.getBoundingClientRect();
  if (rect.width === 0 && rect.height === 0) return false;

  const espacioAbajo = window.innerHeight - rect.bottom;
  const abrirHaciaArriba = espacioAbajo < 260 && rect.top > espacioAbajo;

  menuDesplegable.style.position = 'fixed';
  menuDesplegable.style.margin = '0';
  menuDesplegable.style.right = 'auto';
  menuDesplegable.style.left = `${Math.max(8, rect.left)}px`;
  menuDesplegable.style.width = `${rect.width}px`;
  menuDesplegable.style.zIndex = '9995';

  if (abrirHaciaArriba) {
    menuDesplegable.style.top = 'auto';
    menuDesplegable.style.bottom = `${window.innerHeight - rect.top + 6}px`;
  } else {
    menuDesplegable.style.bottom = 'auto';
    menuDesplegable.style.top = `${rect.bottom + 6}px`;
  }

  return rect.bottom > 0 && rect.top < window.innerHeight;
};

const filtrarElementosLocales = (menuDesplegable, consulta) => {
  const consultaLimpia = consulta.toLowerCase().trim();
  const elementos = menuDesplegable.querySelectorAll('[data-select-item]');
  const mensajeVacio = menuDesplegable.querySelector('.custom-select-empty');
  let visibles = 0;

  elementos.forEach((opcion) => {
    const texto = opcion.dataset.search || opcion.textContent.toLowerCase();
    const coincide = !consultaLimpia || texto.includes(consultaLimpia);
    opcion.classList.toggle('hidden', !coincide);
    if (coincide) visibles++;
  });

  mensajeVacio?.classList.toggle('hidden', visibles > 0);
};

const asegurarOpcionNativa = (selectNativo, { valor, etiqueta, subetiqueta, icono }) => {
  const existente = Array.from(selectNativo.options).find((opt) => opt.value === valor);
  if (existente || valor === '') return;

  const nueva = document.createElement('option');
  nueva.value = valor;
  nueva.textContent = etiqueta;
  if (subetiqueta) nueva.dataset.sublabel = subetiqueta;
  if (icono) nueva.dataset.icon = icono;
  selectNativo.appendChild(nueva);
};

let cerrarAbiertoGlobal = null;

const vincularSelectIndividual = (contenedor, indice) => {
  if (contenedor.dataset.selectPersonalizadoInicializado) return;
  contenedor.dataset.selectPersonalizadoInicializado = 'true';

  const selectNativo = contenedor.querySelector('.custom-select-native');
  const botonDisparador = contenedor.querySelector('.custom-select-trigger');
  const menuDesplegable = contenedor.querySelector('.custom-select-dropdown');
  const campoBusqueda = contenedor.querySelector('.custom-select-search');
  const listaOpciones = contenedor.querySelector('.custom-select-options');
  const iconoChevron = botonDisparador?.querySelector('[data-lucide="chevron-down"]') || botonDisparador?.querySelector('svg');
  const visualizador = botonDisparador?.querySelector('.custom-select-display');
  const remoteUrl = (contenedor.dataset.remoteUrl || '').trim();

  if (!selectNativo || !botonDisparador || !menuDesplegable || !visualizador) return;

  if (typeof menuDesplegable.showPopover === 'function' && !menuDesplegable.hasAttribute('popover')) {
    menuDesplegable.setAttribute('popover', 'manual');
  }

  const claveRemota = `select:${selectNativo.id || selectNativo.name || indice}`;
  let estaAbierto = false;
  let timerDebounce = null;
  let cacheInicialRemota = null;
  let controladorEventosAbierto = null;

  const actualizarInterfaz = (valor, etiqueta, subetiqueta = '', icono = '') => {
    renderDisplaySeleccionado({ visualizador, selectNativo, valor, etiqueta, subetiqueta, icono });
    menuDesplegable.querySelectorAll('[data-select-item]').forEach((el) => {
      const esCoincidencia = el.dataset.value === valor;
      el.querySelector('.custom-select-check')?.classList.toggle('hidden', !esCoincidencia);
      el.classList.toggle('bg-zinc-100', esCoincidencia);
    });
  };

  const renderizarOpcionesRemotas = (items) => {
    if (!listaOpciones) return;
    listaOpciones.innerHTML = construirHtmlOpciones(normalizarOpciones(items), String(selectNativo.value || ''));
  };

  const cargarOpcionesRemotas = async (consulta) => {
    if (!remoteUrl || !listaOpciones) return;
    const q = String(consulta || '').trim();

    if (q === '' && cacheInicialRemota !== null) {
      renderizarOpcionesRemotas(cacheInicialRemota);
      return;
    }

    listaOpciones.innerHTML = '<div class="px-3 py-4 text-center text-xs text-zinc-400">Buscando registros...</div>';

    const res = await Solicitud.get(remoteUrl, {
      params: { q, limit: 30 },
      claveCancelacion: claveRemota,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });

    if (res.aborted) return;
    if (!res.ok) {
      listaOpciones.innerHTML = '<div class="px-3 py-4 text-center text-xs text-red-500">No se pudieron cargar las opciones.</div>';
      return;
    }

    const items = Array.isArray(res.data?.data) ? res.data.data : [];
    if (q === '') cacheInicialRemota = items;
    renderizarOpcionesRemotas(items);
  };

  const cerrarDesplegable = () => {
    if (!estaAbierto) return;
    estaAbierto = false;
    controladorEventosAbierto?.abort();
    controladorEventosAbierto = null;

    if (typeof menuDesplegable.hidePopover === 'function' && menuDesplegable.matches?.(':popover-open')) {
      menuDesplegable.hidePopover();
    }
    menuDesplegable.classList.add('hidden');
    iconoChevron?.classList.remove('rotate-180');
    if (cerrarAbiertoGlobal === cerrarDesplegable) cerrarAbiertoGlobal = null;
  };

  const abrirDesplegable = () => {
    if (cerrarAbiertoGlobal && cerrarAbiertoGlobal !== cerrarDesplegable) {
      cerrarAbiertoGlobal();
    }
    cerrarAbiertoGlobal = cerrarDesplegable;
    estaAbierto = true;
    menuDesplegable.classList.remove('hidden');

    if (typeof menuDesplegable.showPopover === 'function') {
      try {
        menuDesplegable.showPopover();
      } catch {
        // Respaldo silencioso si ya estaba en top-layer
      }
    }

    posicionarDropdown(botonDisparador, menuDesplegable);
    iconoChevron?.classList.add('rotate-180');

    controladorEventosAbierto = new AbortController();
    const { signal } = controladorEventosAbierto;

    window.addEventListener(
      'scroll',
      (ev) => {
        if (menuDesplegable.contains(ev.target)) return;
        if (!posicionarDropdown(botonDisparador, menuDesplegable)) cerrarDesplegable();
      },
      { capture: true, passive: true, signal }
    );
    window.addEventListener('resize', () => posicionarDropdown(botonDisparador, menuDesplegable), { passive: true, signal });

    if (campoBusqueda) {
      campoBusqueda.value = '';
      remoteUrl ? cargarOpcionesRemotas('') : filtrarElementosLocales(menuDesplegable, '');
      setTimeout(() => campoBusqueda.focus(), 40);
    } else if (remoteUrl) {
      cargarOpcionesRemotas('');
    }
  };

  botonDisparador.addEventListener('click', (evento) => {
    evento.stopPropagation();
    estaAbierto ? cerrarDesplegable() : abrirDesplegable();
  });

  if (campoBusqueda) {
    campoBusqueda.addEventListener('input', (evento) => {
      const consulta = evento.target.value;
      if (!remoteUrl) return filtrarElementosLocales(menuDesplegable, consulta);
      clearTimeout(timerDebounce);
      timerDebounce = setTimeout(() => cargarOpcionesRemotas(consulta), 180);
    });
    campoBusqueda.addEventListener('keydown', (evento) => {
      if (evento.key === 'Enter') evento.preventDefault();
      if (evento.key === 'Escape') cerrarDesplegable();
    });
  }

  menuDesplegable.addEventListener('click', (evento) => {
    const opcion = evento.target.closest('[data-select-item]');
    if (!opcion) return;

    const datos = {
      valor: opcion.dataset.value || '',
      etiqueta: opcion.dataset.label || '',
      subetiqueta: opcion.dataset.sublabel || '',
      icono: opcion.dataset.icon || '',
    };

    asegurarOpcionNativa(selectNativo, datos);
    selectNativo.value = datos.valor;
    selectNativo.dispatchEvent(new Event('change', { bubbles: true }));
    actualizarInterfaz(datos.valor, datos.etiqueta, datos.subetiqueta, datos.icono);
    cerrarDesplegable();
    botonDisparador.focus();
  });

  document.addEventListener('click', (evento) => {
    if (estaAbierto && !contenedor.contains(evento.target) && !menuDesplegable.contains(evento.target)) {
      cerrarDesplegable();
    }
  });

  selectNativo.addEventListener('change', () => {
    const seleccionada = selectNativo.options[selectNativo.selectedIndex];
    if (!seleccionada) return;
    actualizarInterfaz(
      seleccionada.value,
      seleccionada.text,
      seleccionada.dataset.sublabel || '',
      seleccionada.dataset.icon || ''
    );
  });

  selectNativo.form?.addEventListener('reset', () => {
    setTimeout(() => actualizarInterfaz('', ''), 0);
  });

  const inicial = selectNativo.options[selectNativo.selectedIndex];
  if (inicial?.value) {
    actualizarInterfaz(inicial.value, inicial.text, inicial.dataset.sublabel || '', inicial.dataset.icon || '');
  }
};

/**
 * Inicializa todos los selects personalizados dentro del nodo o selector indicado.
 * @param {string|ParentNode} [objetivo='[data-custom-select]']
 */
export const inicializarSelectsPersonalizados = (objetivo = '[data-custom-select]') => {
  const nodos =
    typeof objetivo === 'string'
      ? document.querySelectorAll(objetivo)
      : objetivo?.querySelectorAll?.('[data-custom-select]') || [];

  nodos.forEach((contenedor, indice) => vincularSelectIndividual(contenedor, indice));
};

/**
 * Reemplaza dinámicamente las opciones de un select personalizado manteniendo su estética.
 * @param {string|HTMLElement} selector
 * @param {Array|Object} [opciones=[]]
 * @param {string|number} [valorSeleccionado='']
 */
export const actualizarOpcionesSelect = (selector, opciones = [], valorSeleccionado = '') => {
  const elemento = typeof selector === 'string' ? document.querySelector(selector) : selector;
  const contenedor = elemento?.closest('[data-custom-select]');
  if (!contenedor) return;

  const selectNativo = contenedor.querySelector('.custom-select-native');
  const listaOpciones = contenedor.querySelector('.custom-select-options');
  const visualizador = contenedor.querySelector('.custom-select-trigger .custom-select-display');
  const textoPlaceholder = selectNativo.getAttribute('data-placeholder') || 'Seleccionar opción...';

  const normalizadas = normalizarOpciones(opciones);
  const valorObjetivo = String(valorSeleccionado || selectNativo.value || '');
  const valorFinal = normalizadas.some((op) => op.valor === valorObjetivo) ? valorObjetivo : '';

  selectNativo.innerHTML = `<option value="">${textoPlaceholder}</option>`;
  normalizadas.forEach((opcion) => {
    const opt = document.createElement('option');
    opt.value = opcion.valor;
    opt.textContent = opcion.etiqueta;
    if (opcion.subetiqueta) opt.dataset.sublabel = opcion.subetiqueta;
    if (opcion.icono) opt.dataset.icon = opcion.icono;
    if (opcion.valor === valorFinal) opt.selected = true;
    selectNativo.appendChild(opt);
  });

  if (listaOpciones) listaOpciones.innerHTML = construirHtmlOpciones(normalizadas, valorFinal);
  selectNativo.value = valorFinal;

  if (visualizador) {
    const coincidencia = valorFinal ? normalizadas.find((op) => op.valor === valorFinal) : null;
    renderDisplaySeleccionado({
      visualizador,
      selectNativo,
      valor: valorFinal,
      etiqueta: coincidencia?.etiqueta || valorFinal,
      subetiqueta: coincidencia?.subetiqueta || '',
      icono: coincidencia?.icono || '',
    });
  }

  selectNativo.dispatchEvent(new Event('change', { bubbles: true }));
};

/**
 * Construye el HTML de un select con la estructura exacta de `components/select.blade.php`.
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
  const normalizadas = normalizarOpciones(opciones);
  const valorStr = String(valorSeleccionado ?? '');

  const opcionesNativas = normalizadas
    .map((op) => `<option value="${op.valor}" ${valorStr === op.valor ? 'selected' : ''}>${op.etiqueta}</option>`)
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
    ? `<label for="${id}" class="block text-sm font-medium text-zinc-900">${etiqueta}</label>`
    : '';

  return `
    <div class="space-y-1.5" data-custom-select id="${id}-wrapper">
      ${labelHtml}
      <div class="relative">
        <select id="${id}" name="${nombre}" data-placeholder="${placeholder}" class="custom-select-native sr-only" tabindex="-1" aria-hidden="true" ${requerido ? 'required' : ''}>
          <option value="">${placeholder}</option>
          ${opcionesNativas}
        </select>
        <button type="button" class="custom-select-trigger w-full flex items-center justify-between px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg hover:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:border-black focus:ring-black transition duration-200 cursor-pointer">
          <div class="custom-select-display flex items-center gap-2.5 truncate">
            <span class="text-xs text-zinc-400 truncate">${placeholder}</span>
          </div>
          <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400 transition-transform duration-200 shrink-0 ml-2"></i>
        </button>
        <div popover="manual" class="custom-select-dropdown hidden bg-white border border-zinc-200 rounded-xl shadow-[0_15px_30px_rgba(0,0,0,0.08)] overflow-hidden p-1.5 space-y-1">
          ${buscadorHtml}
          <div class="custom-select-options max-h-52 overflow-y-auto space-y-0.5">
            ${construirHtmlOpciones(normalizadas, valorStr)}
          </div>
        </div>
      </div>
      <p id="${id}-error" class="input-error-msg hidden text-xs font-medium text-red-600 mt-1 transition-all duration-200" role="alert"></p>
    </div>
  `;
};

/**
 * Genera el HTML interno para envolver el select de longitud de DataTables.
 */
export const crearMarkupSelectLongitud = (opciones, valorActual) => {
  const normalizadas = opciones.map((opt) => ({
    valor: String(opt.valor),
    etiqueta: String(opt.texto),
    subetiqueta: '',
    icono: '',
  }));

  return `
    <button type="button" class="custom-select-trigger w-full flex items-center justify-between px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg hover:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:border-black focus:ring-black transition duration-200 cursor-pointer">
      <div class="custom-select-display flex items-center gap-2 truncate">
        <span class="text-xs font-semibold text-zinc-900 truncate">${valorActual}</span>
      </div>
      <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400 transition-transform duration-200 shrink-0 ml-1.5"></i>
    </button>
    <div popover="manual" class="custom-select-dropdown hidden bg-white border border-zinc-200 rounded-xl shadow-[0_15px_30px_rgba(0,0,0,0.08)] overflow-hidden p-1.5 space-y-1">
      <div class="custom-select-options max-h-52 overflow-y-auto space-y-0.5">
        ${construirHtmlOpciones(normalizadas, String(valorActual))}
      </div>
    </div>
  `;
};
