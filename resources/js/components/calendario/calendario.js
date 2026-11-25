import { obtenerListaFeriadosInces } from '/js/utils/calcularDiasHabiles.js';

const instanciasPorInput = new WeakMap();
const REGEX_FECHA_ISO = /^\d{4}-\d{2}-\d{2}$/;

// Recibe un selector CSS o elemento input; devuelve el HTMLInputElement encontrado o null.
const resolverInput = (selectorOElemento) => {
  if (!selectorOElemento) return null;
  if (typeof selectorOElemento === 'string') return document.querySelector(selectorOElemento);
  return selectorOElemento instanceof HTMLInputElement ? selectorOElemento : null;
};

// Recibe el input de fecha y placeholder; prepara sus atributos visuales e ícono sin romper el layout.
const prepararInputCalendario = (input, placeholder = 'Seleccionar fecha...') => {
  if (!input) return;
  if (input.type === 'date') {
    input.type = 'text';
  }
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

// Recibe opciones de inicialización; construye la lista de reglas disable de Flatpickr (fines de semana y feriados).
const construirDeshabilitados = (opciones = {}) => {
  const reglas = [];
  const diasBloqueados = opciones.soloHabiles
    ? [0, 6]
    : Array.isArray(opciones.disableWeekdays)
      ? opciones.disableWeekdays
      : [];

  if (diasBloqueados.length > 0) {
    reglas.push((fecha) => diasBloqueados.includes(fecha.getDay()));
  }

  if (opciones.soloHabiles) {
    obtenerListaFeriadosInces().forEach((feriado) => reglas.push(feriado));
  }

  if (Array.isArray(opciones.disableDates) && opciones.disableDates.length > 0) {
    opciones.disableDates.forEach((f) => reglas.push(f));
  }

  return reglas;
};

// Resuelve la configuración regional en español para Flatpickr con semana iniciando el lunes.
const obtenerLocaleEs = () => {
  const localeEs = window.flatpickr?.l10ns?.es;
  if (localeEs && typeof localeEs === 'object') {
    return { ...localeEs, firstDayOfWeek: 1 };
  }
  return { firstDayOfWeek: 1 };
};

// Recibe dos fechas ISO YYYY-MM-DD; valida formato y que fechaFin sea mayor o igual que fechaInicio.
export const validarRangoFechas = (fechaInicio, fechaFin) => {
  const inicio = String(fechaInicio || '').trim();
  const fin = String(fechaFin || '').trim();
  if (!REGEX_FECHA_ISO.test(inicio) || !REGEX_FECHA_ISO.test(fin)) {
    return false;
  }
  return fin >= inicio;
};

// Recibe un selector o input y opciones; inicializa Flatpickr en formato YYYY-MM-DD con soporte para <dialog>.
export const inicializarCalendario = (selectorOElemento, opciones = {}) => {
  const input = resolverInput(selectorOElemento);
  if (!input || typeof window.flatpickr !== 'function') return null;

  const existente = instanciasPorInput.get(input);
  if (existente) {
    try {
      existente.destroy();
    } catch {
      // Ignorar si la instancia previa ya fue destruida
    }
    instanciasPorInput.delete(input);
  }

  prepararInputCalendario(input, opciones.placeholder || 'Seleccionar fecha...');

  const valorInicial = REGEX_FECHA_ISO.test(input.value) ? input.value : '';
  const dialogoPadre = input.closest('dialog');
  const deshabilitados = construirDeshabilitados(opciones);

  const configuracion = {
    locale: obtenerLocaleEs(),
    dateFormat: 'Y-m-d',
    defaultDate: valorInicial || undefined,
    minDate: opciones.minDate || opciones.dateMin || '1930-01-01',
    maxDate: opciones.maxDate || opciones.dateMax || '2099-12-31',
    disableMobile: true,
    allowInput: Boolean(opciones.allowInput ?? false),
    clickOpens: true,
    monthSelectorType: 'dropdown',
    disable: deshabilitados,
    onChange(_fechasSeleccionadas, fechaStr, instancia) {
      if (input.value !== fechaStr) {
        input.value = fechaStr;
      }
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
      if (typeof opciones.alSeleccionar === 'function') {
        opciones.alSeleccionar(fechaStr, instancia);
      }
    },
  };

  if (opciones.appendTo) {
    configuracion.appendTo = opciones.appendTo;
  } else if (dialogoPadre) {
    configuracion.appendTo = dialogoPadre;
  }

  const instancia = window.flatpickr(input, configuracion);
  instanciasPorInput.set(input, instancia);

  if (input.form && !input.dataset.fpResetVinculado) {
    input.dataset.fpResetVinculado = '1';
    input.form.addEventListener('reset', () => {
      setTimeout(() => {
        const instActual = instanciasPorInput.get(input);
        if (!instActual) return;
        if (!input.value) {
          instActual.clear(false);
        } else if (REGEX_FECHA_ISO.test(input.value)) {
          instActual.setDate(input.value, false);
        }
      }, 0);
    });
  }

  return instancia;
};

// Recibe selectores/inputs de inicio y fin más opciones; enlaza ambos Flatpickr garantizando fechaFin >= fechaInicio.
export const inicializarRangoFechas = (selectorInicio, selectorFin, opciones = {}) => {
  const inputInicio = resolverInput(selectorInicio);
  const inputFin = resolverInput(selectorFin);
  if (!inputInicio || !inputFin) {
    return { calInicio: null, calFin: null, inicio: null, fin: null };
  }

  const minBase = opciones.minDate || opciones.dateMin || '1930-01-01';
  let calFin = null;

  // Recibe la fecha inicial seleccionada; ajusta minDate y corrige el campo final si quedó menor.
  const sincronizarMinimoFin = (fechaInicio) => {
    if (!calFin) return;
    const minEfectivo = REGEX_FECHA_ISO.test(fechaInicio) ? fechaInicio : minBase;
    calFin.set('minDate', minEfectivo);

    if (inputFin.value && REGEX_FECHA_ISO.test(fechaInicio) && inputFin.value < fechaInicio) {
      calFin.setDate(fechaInicio, true);
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
    minDate: REGEX_FECHA_ISO.test(inputInicio.value) ? inputInicio.value : minBase,
    alSeleccionar: (fecha) => {
      if (REGEX_FECHA_ISO.test(inputInicio.value) && fecha && fecha < inputInicio.value) {
        calFin?.setDate(inputInicio.value, false);
        inputFin.value = inputInicio.value;
      }
      if (typeof opciones.alCambiarRango === 'function') {
        opciones.alCambiarRango(inputInicio.value, inputFin.value);
      }
    },
  });

  if (inputInicio.form && !inputInicio.dataset.fpRangoResetVinculado) {
    inputInicio.dataset.fpRangoResetVinculado = '1';
    inputInicio.form.addEventListener('reset', () => {
      setTimeout(() => {
        calFin?.set('minDate', minBase);
        calFin?.clear(false);
      }, 0);
    });
  }

  return { calInicio, calFin, inicio: calInicio, fin: calFin };
};

// Recibe un contenedor raíz; inicializa Flatpickr sobre todos los inputs de tipo date no vinculados aún.
export const inicializarCalendariosContenedor = (raiz = document) => {
  if (!raiz || typeof raiz.querySelectorAll !== 'function') return;
  raiz.querySelectorAll('input[type="date"]').forEach((input) => {
    if (!instanciasPorInput.has(input)) {
      inicializarCalendario(input);
    }
  });
};
