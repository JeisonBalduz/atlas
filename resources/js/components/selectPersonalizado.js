/**
 * selectPersonalizado.js
 * Componente de Select con Buscador, Posicionamiento Inteligente y carga remota (`data-remote-url`).
 */

import { Solicitud } from '/js/class/Solicitud.js';
import { escaparHtml } from '/js/utils/dom.js';

const ICONO_CHECK_SVG =
  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="custom-select-check w-4 h-4 text-zinc-900 shrink-0 ml-2 %CLASS%"><path d="M20 6 9 17l-5-5"/></svg>';

const construirHtmlOpciones = (normalizadas, valorFinal) => {
  let html = normalizadas
    .map((opcion) => {
      const esCoincidencia = opcion.valor === valorFinal;
      const metaBusqueda = `${opcion.valor} ${opcion.etiqueta} ${opcion.subetiqueta}`.toLowerCase();
      const checkSvg = ICONO_CHECK_SVG.replace('%CLASS%', esCoincidencia ? '' : 'hidden');

      return `
        <div data-select-item
             data-value="${escaparHtml(opcion.valor)}"
             data-label="${escaparHtml(opcion.etiqueta)}"
             data-sublabel="${escaparHtml(opcion.subetiqueta)}"
             data-icon="${escaparHtml(opcion.icono)}"
             data-search="${escaparHtml(metaBusqueda)}"
             class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-zinc-100 cursor-pointer text-xs transition-colors ${esCoincidencia ? 'bg-zinc-100' : ''}">
            <div class="flex items-center gap-2.5 truncate">
                ${opcion.icono ? `<span class="text-base shrink-0">${escaparHtml(opcion.icono)}</span>` : ''}
                <span class="font-semibold text-zinc-900 truncate">${escaparHtml(opcion.etiqueta)}</span>
                ${opcion.subetiqueta ? `<span class="text-zinc-500 truncate max-w-35">${escaparHtml(opcion.subetiqueta)}</span>` : ''}
            </div>
            ${checkSvg}
        </div>
      `;
    })
    .join('');

  html += `
    <div class="custom-select-empty ${normalizadas.length === 0 ? '' : 'hidden'} px-3 py-4 text-center text-xs text-zinc-400">
      No se encontraron registros.
    </div>
  `;

  return html;
};

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

const renderDisplaySeleccionado = ({ visualizador, selectNativo, valor, etiqueta, subetiqueta = '', icono = '' }) => {
  if (!valor) {
    const placeholder = selectNativo.getAttribute('data-placeholder') || 'Seleccionar opción...';
    visualizador.innerHTML = `<span class="text-xs text-zinc-400 truncate">${escaparHtml(placeholder)}</span>`;
    return;
  }

  visualizador.innerHTML = `
    <div class="flex items-center gap-2.5 truncate">
      ${icono ? `<span class="text-base shrink-0">${escaparHtml(icono)}</span>` : ''}
      <span class="text-xs font-semibold text-zinc-900 truncate">${escaparHtml(etiqueta)}</span>
      ${subetiqueta ? `<span class="text-xs text-zinc-500 truncate max-w-30">${escaparHtml(subetiqueta)}</span>` : ''}
    </div>
  `;
};

const posicionarDropdown = (botonDisparador, menuDesplegable) => {
  const rectangulo = botonDisparador.getBoundingClientRect();
  const abrirHaciaArriba = window.innerHeight - rectangulo.bottom < 280;

  menuDesplegable.classList.toggle('bottom-full', abrirHaciaArriba);
  menuDesplegable.classList.toggle('mb-1.5', abrirHaciaArriba);
  menuDesplegable.classList.toggle('origin-bottom', abrirHaciaArriba);
  menuDesplegable.classList.toggle('top-full', !abrirHaciaArriba);
  menuDesplegable.classList.toggle('mt-1.5', !abrirHaciaArriba);
  menuDesplegable.classList.toggle('origin-top', !abrirHaciaArriba);
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

const vincularSelectIndividual = (contenedor, indice) => {
  if (contenedor.dataset.selectPersonalizadoInicializado || contenedor.dataset.customSelectInitialized) return;
  contenedor.dataset.selectPersonalizadoInicializado = 'true';
  contenedor.dataset.customSelectInitialized = 'true';

  const selectNativo = contenedor.querySelector('.custom-select-native');
  const botonDisparador = contenedor.querySelector('.custom-select-trigger');
  const menuDesplegable = contenedor.querySelector('.custom-select-dropdown');
  const campoBusqueda = contenedor.querySelector('.custom-select-search');
  const listaOpciones = contenedor.querySelector('.custom-select-options');
  const iconoChevron = botonDisparador?.querySelector('[data-lucide="chevron-down"]');
  const visualizador = botonDisparador?.querySelector('.custom-select-display');
  const remoteUrl = (contenedor.dataset.remoteUrl || '').trim();

  if (!selectNativo || !botonDisparador || !menuDesplegable || !visualizador) return;

  const claveRemota = `select:${selectNativo.id || selectNativo.name || indice}`;
  let estaAbierto = false;
  let timerDebounce = null;
  let cacheInicialRemota = null;

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
    estaAbierto = false;
    menuDesplegable.classList.add('hidden');
    iconoChevron?.classList.remove('rotate-180');
  };

  const abrirDesplegable = () => {
    estaAbierto = true;
    menuDesplegable.classList.remove('hidden');
    posicionarDropdown(botonDisparador, menuDesplegable);
    iconoChevron?.classList.add('rotate-180');

    if (campoBusqueda) {
      campoBusqueda.value = '';
      remoteUrl ? cargarOpcionesRemotas('') : filtrarElementosLocales(menuDesplegable, '');
      setTimeout(() => campoBusqueda.focus(), 50);
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
    if (estaAbierto && !contenedor.contains(evento.target)) cerrarDesplegable();
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

export const inicializarSelectsPersonalizados = (selector = '[data-custom-select]') => {
  document.querySelectorAll(selector).forEach((contenedor, indice) => {
    vincularSelectIndividual(contenedor, indice);
  });
};

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

  selectNativo.innerHTML = `<option value="">${escaparHtml(textoPlaceholder)}</option>`;
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

export const initCustomSelects = inicializarSelectsPersonalizados;
export const updateSelectOptions = actualizarOpcionesSelect;
