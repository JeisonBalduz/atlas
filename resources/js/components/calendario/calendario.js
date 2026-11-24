// import { Calendar } from '/lib/vanilla-calendar-pro/index.mjs';
import { obtenerListaFeriadosInces } from '/js/utils/calcularDiasHabiles.js';

const instanciasPorInput = new WeakMap();

const resolverInput = (selectorOElemento) => {
  if (!selectorOElemento) return null;
  if (typeof selectorOElemento === 'string') return document.querySelector(selectorOElemento);
  return selectorOElemento instanceof HTMLInputElement ? selectorOElemento : null;
};

const prepararInputCalendario = (input, placeholder = 'AAAA-MM-DD') => {
  if (!input) return;
  if (input.type === 'date') {
    input.type = 'text';
  }
  input.readOnly = true;
  input.autocomplete = 'off';
  if (!input.placeholder) {
    input.placeholder = placeholder;
  }
  input.classList.add('cursor-pointer');

  const contenedor = input.parentElement;
  if (contenedor && contenedor.classList.contains('relative') && !contenedor.querySelector('[data-icono-calendario]')) {
    input.classList.add('pr-10');
    const icono = document.createElement('span');
    icono.setAttribute('data-icono-calendario', '');
    icono.className = 'pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400';
    icono.innerHTML =
      '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>';
    contenedor.appendChild(icono);
  }
};

const elevarPopupEnTopLayer = (self) => {
  const popup = self?.context?.mainElement;
  if (!popup) return;
  if (!popup.hasAttribute('popover')) {
    popup.setAttribute('popover', 'manual');
    popup.style.margin = '0';
    popup.style.inset = 'auto';
  }
  try {
    if (!popup.matches(':popover-open')) {
      popup.showPopover();
    }
  } catch {
    // Fallback automático si el navegador no soporta popover manual
  }
};

const ocultarPopupDeTopLayer = (self) => {
  const popup = self?.context?.mainElement;
  if (!popup || !popup.hasAttribute('popover')) return;
  try {
    if (popup.matches(':popover-open')) {
      popup.hidePopover();
    }
  } catch {
    // Ignorar si ya estaba cerrado
  }
};

/**
 * Valida que ambas fechas existan (`YYYY-MM-DD`) y que `fechaFin >= fechaInicio`.
 * @param {string} fechaInicio
 * @param {string} fechaFin
 * @returns {boolean}
 */
export const validarRangoFechas = (fechaInicio, fechaFin) => {
  const inicio = String(fechaInicio || '').trim();
  const fin = String(fechaFin || '').trim();
  if (!/^\d{4}-\d{2}-\d{2}$/.test(inicio) || !/^\d{4}-\d{2}-\d{2}$/.test(fin)) {
    return false;
  }
  return fin >= inicio;
};

/**
 * Inicializa Vanilla Calendar Pro sobre un `<input>` individual manteniendo formato `YYYY-MM-DD`.
 * @param {HTMLInputElement|string} selectorOElemento
 * @param {Object} [opciones]
 * @returns {Calendar|null}
 */
export const inicializarCalendario = (selectorOElemento, opciones = {}) => {
  const input = resolverInput(selectorOElemento);
  if (!input) return null;

  const existente = instanciasPorInput.get(input);
  if (existente) {
    try {
      existente.destroy();
    } catch {
      // Ignorar si ya fue destruida
    }
    instanciasPorInput.delete(input);
  }

  prepararInputCalendario(input, opciones.placeholder || 'Seleccionar fecha...');

  const valorInicial = /^\d{4}-\d{2}-\d{2}$/.test(input.value) ? input.value : '';
  const feriados = opciones.soloHabiles ? obtenerListaFeriadosInces() : [];

  const calendario = new Calendar(input, {
    inputMode: true,
    positionToInput: opciones.positionToInput || 'auto',
    locale: 'es-VE',
    firstWeekday: 1,
    selectedTheme: 'light',
    dateMin: opciones.dateMin || '1930-01-01',
    dateMax: opciones.dateMax || '2099-12-31',
    displayDateMin: opciones.minDate || opciones.dateMin || '1930-01-01',
    displayDateMax: opciones.maxDate || opciones.dateMax || '2099-12-31',
    disableWeekdays: opciones.soloHabiles ? [0, 6] : opciones.disableWeekdays || [],
    disableDates: opciones.soloHabiles ? [...feriados, ...(opciones.disableDates || [])] : opciones.disableDates || [],
    selectedHolidays: feriados,
    selectedDates: valorInicial ? [valorInicial] : [],
    onShow(self) {
      elevarPopupEnTopLayer(self);
    },
    onHide(self) {
      ocultarPopupDeTopLayer(self);
    },
    onChangeToInput(self) {
      const fecha = self.context.selectedDates?.[0] || '';
      if (!fecha) return;
      input.value = fecha;
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
      if (typeof opciones.alSeleccionar === 'function') {
        opciones.alSeleccionar(fecha, self);
      }
      self.hide();
    },
  });

  calendario.init();
  instanciasPorInput.set(input, calendario);

  // Al resetear el formulario limpiamos la selección interna del calendario
  input.form?.addEventListener('reset', () => {
    setTimeout(() => {
      if (!input.value) {
        calendario.set({ selectedDates: [] }, { dates: true });
      }
    }, 0);
  });

  return calendario;
};

/**
 * Vincula dos inputs (`inicio` y `fin`) garantizando en UI y valor que `fecha_fin >= fecha_inicio`.
 * @param {HTMLInputElement|string} selectorInicio
 * @param {HTMLInputElement|string} selectorFin
 * @param {Object} [opciones]
 */
export const inicializarRangoFechas = (selectorInicio, selectorFin, opciones = {}) => {
  const inputInicio = resolverInput(selectorInicio);
  const inputFin = resolverInput(selectorFin);
  if (!inputInicio || !inputFin) return { calInicio: null, calFin: null };

  let calFin = null;

  const sincronizarMinimoFin = (fechaInicio) => {
    if (!calFin) return;
    const min = /^\d{4}-\d{2}-\d{2}$/.test(fechaInicio)
      ? fechaInicio
      : opciones.minDate || opciones.dateMin || '1930-01-01';

    if (inputFin.value && fechaInicio && inputFin.value < fechaInicio) {
      inputFin.value = fechaInicio;
      calFin.set({ displayDateMin: min, selectedDates: [fechaInicio] }, { dates: true });
      inputFin.dispatchEvent(new Event('change', { bubbles: true }));
    } else {
      calFin.set({ displayDateMin: min }, { dates: false });
    }
  };

  const calInicio = inicializarCalendario(inputInicio, {
    ...opciones,
    alSeleccionar: (fecha) => {
      sincronizarMinimoFin(fecha);
      if (typeof opciones.alCambiarRango === 'function') {
        opciones.alCambiarRango(inputInicio.value, inputFin.value);
      }
    },
  });

  calFin = inicializarCalendario(inputFin, {
    ...opciones,
    minDate: inputInicio.value || opciones.minDate,
    alSeleccionar: (fecha) => {
      if (inputInicio.value && fecha < inputInicio.value) {
        inputFin.value = inputInicio.value;
      }
      if (typeof opciones.alCambiarRango === 'function') {
        opciones.alCambiarRango(inputInicio.value, inputFin.value);
      }
    },
  });

  inputInicio.form?.addEventListener('reset', () => {
    setTimeout(() => {
      calFin?.set(
        {
          displayDateMin: opciones.minDate || opciones.dateMin || '1930-01-01',
          selectedDates: [],
        },
        { dates: true }
      );
    }, 0);
  });

  return { calInicio, calFin };
};

/**
 * Inicializa automáticamente todos los `<input type="date">` dentro de un contenedor o documento.
 * @param {ParentNode} [raiz=document]
 */
export const inicializarCalendariosContenedor = (raiz = document) => {
  raiz.querySelectorAll('input[type="date"]').forEach((input) => {
    if (!instanciasPorInput.has(input)) {
      inicializarCalendario(input);
    }
  });
};
