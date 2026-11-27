import { inicializarCalendario } from '/js/components/calendario/calendario.js';

/**
 * Formatea fechas ISO (YYYY-MM-DD o timestamp) o DD-MM-YYYY a DD/MM/YYYY.
 * @param {string|null|undefined} fechaTexto
 * @returns {string}
 */
export const formatearFecha = (fechaTexto) => {
  if (!fechaTexto || typeof fechaTexto !== 'string') return '—';
  const limpia = fechaTexto.trim();
  const parteFecha = limpia.slice(0, 10);
  const partes = parteFecha.split('-');

  if (partes.length === 3) {
    if (partes[0].length === 4) {
      return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }
    if (partes[2].length === 4) {
      return `${partes[0]}/${partes[1]}/${partes[2]}`;
    }
  }

  return limpia;
};

/**
 * Parsea numéricamente una fecha en formato `dd-mm-yyyy` o `YYYY-MM-DD` sin usar `new Date("YYYY-MM-DD")`.
 * @param {string|Date|null|undefined} valor
 * @returns {{ anio: number, mes: number, dia: number, date: Date, visual: string, iso: string } | null}
 */
export const parsearFechaFlexible = (valor) => {
  if (valor instanceof Date && !Number.isNaN(valor.getTime())) {
    const anio = valor.getFullYear();
    const mes = valor.getMonth() + 1;
    const dia = valor.getDate();
    const dd = String(dia).padStart(2, '0');
    const mm = String(mes).padStart(2, '0');
    const yyyy = String(anio).padStart(4, '0');
    return {
      anio,
      mes,
      dia,
      date: new Date(anio, mes - 1, dia),
      visual: `${dd}-${mm}-${yyyy}`,
      iso: `${yyyy}-${mm}-${dd}`,
    };
  }

  const texto = String(valor || '').trim().slice(0, 10);
  if (!texto) return null;

  let anio = 0;
  let mes = 0;
  let dia = 0;

  const matchVisual = /^(\d{2})-(\d{2})-(\d{4})$/.exec(texto);
  const matchIso = /^(\d{4})-(\d{2})-(\d{2})$/.exec(texto);

  if (matchVisual) {
    dia = Number(matchVisual[1]);
    mes = Number(matchVisual[2]);
    anio = Number(matchVisual[3]);
  } else if (matchIso) {
    anio = Number(matchIso[1]);
    mes = Number(matchIso[2]);
    dia = Number(matchIso[3]);
  } else {
    return null;
  }

  if (mes < 1 || mes > 12 || dia < 1 || dia > 31 || anio < 1900) {
    return null;
  }

  const fecha = new Date(anio, mes - 1, dia);
  if (fecha.getFullYear() !== anio || fecha.getMonth() !== mes - 1 || fecha.getDate() !== dia) {
    return null;
  }

  const dd = String(dia).padStart(2, '0');
  const mm = String(mes).padStart(2, '0');
  const yyyy = String(anio).padStart(4, '0');

  return {
    anio,
    mes,
    dia,
    date: fecha,
    visual: `${dd}-${mm}-${yyyy}`,
    iso: `${yyyy}-${mm}-${dd}`,
  };
};

export const fechaIsoAVisual = (valorIso) => parsearFechaFlexible(valorIso)?.visual || '';

export const fechaVisualAIso = (valorVisual) => parsearFechaFlexible(valorVisual)?.iso || '';

export const obtenerFechaHoyLocalIso = () => {
  const hoy = new Date();
  const yyyy = String(hoy.getFullYear()).padStart(4, '0');
  const mm = String(hoy.getMonth() + 1).padStart(2, '0');
  const dd = String(hoy.getDate()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd}`;
};

const resolverElementoInput = (selectorOInput) => {
  if (!selectorOInput) return null;
  if (typeof selectorOInput === 'string') return document.querySelector(selectorOInput);
  return selectorOInput instanceof HTMLInputElement ? selectorOInput : null;
};

/**
 * Inicializa un campo con `calendario.js` mostrando `dd-mm-yyyy` y manteniendo `YYYY-MM-DD` sincronizado.
 * @param {string|HTMLInputElement|null} selectorOInput
 * @param {object} [opciones={}]
 */
export const inicializarCampoFechaDobleFormato = (selectorOInput, opciones = {}) => {
  const input = resolverElementoInput(selectorOInput);
  if (!input) return null;

  const inputIso =
    opciones.inputIso ||
    input.parentElement?.querySelector('input[type="hidden"]') ||
    null;

  const inicial = parsearFechaFlexible(inputIso?.value || input.value);
  const opcionesCalendario = { ...opciones };
  delete opcionesCalendario.inputIso;
  delete opcionesCalendario.alReset;

  if (opciones.minDate) {
    const parsedMin = parsearFechaFlexible(opciones.minDate);
    if (parsedMin) opcionesCalendario.minDate = parsedMin.date;
  }
  if (opciones.maxDate) {
    const parsedMax = parsearFechaFlexible(opciones.maxDate);
    if (parsedMax) opcionesCalendario.maxDate = parsedMax.date;
  }

  const sincronizarDesdeInput = () => {
    const parsed = parsearFechaFlexible(input.value);
    if (parsed) {
      if (input.value !== parsed.visual) input.value = parsed.visual;
      if (inputIso) inputIso.value = parsed.iso;
      return parsed;
    }
    if (inputIso) inputIso.value = '';
    return null;
  };

  if (inicial) {
    input.value = inicial.iso;
    if (inputIso) inputIso.value = inicial.iso;
  }

  const cal = inicializarCalendario(input, {
    ...opcionesCalendario,
    placeholder: opciones.placeholder || 'dd-mm-yyyy',
    alSeleccionar: (fechaStr, inst) => {
      const parsed = sincronizarDesdeInput();
      if (typeof opciones.alSeleccionar === 'function') {
        opciones.alSeleccionar(parsed?.iso || '', parsed?.visual || fechaStr || '', inst);
      }
    },
  });

  cal?.set('dateFormat', 'd-m-Y');
  if (inicial) {
    cal?.setDate(inicial.date, false);
    input.value = inicial.visual;
    if (inputIso) inputIso.value = inicial.iso;
  }

  input.addEventListener('change', sincronizarDesdeInput);

  if (input.form && !input.dataset.dobleFormatoResetVinculado) {
    input.dataset.dobleFormatoResetVinculado = '1';
    input.form.addEventListener('reset', () => {
      setTimeout(() => {
        const parsed = parsearFechaFlexible(input.value);
        if (parsed) {
          input.value = parsed.visual;
          if (inputIso) inputIso.value = parsed.iso;
          cal?.setDate(parsed.date, false);
        } else {
          input.value = '';
          if (inputIso) inputIso.value = '';
          cal?.clear(false);
        }
        if (typeof opciones.alReset === 'function') {
          opciones.alReset();
        }
      }, 0);
    });
  }

  return {
    cal,
    input,
    inputIso,
    obtenerIso: () => (inputIso ? String(inputIso.value || '').trim() : fechaVisualAIso(input.value)),
    obtenerVisual: () => String(input.value || '').trim(),
    establecerIso: (iso, disparar = false) => {
      const parsed = parsearFechaFlexible(iso);
      if (!parsed) {
        input.value = '';
        if (inputIso) inputIso.value = '';
        cal?.clear(disparar);
        return;
      }
      if (inputIso) inputIso.value = parsed.iso;
      cal?.setDate(parsed.date, disparar);
      input.value = parsed.visual;
    },
    limpiar: () => {
      input.value = '';
      if (inputIso) inputIso.value = '';
      cal?.clear(false);
    },
  };
};

/**
 * Inicializa un par inicio/fin con `dd-mm-yyyy` visible, `YYYY-MM-DD` oculto y sincronización de `minDate`.
 * @param {string|HTMLInputElement|null} selectorInicio
 * @param {string|HTMLInputElement|null} selectorFin
 * @param {object} [opciones={}]
 */
export const inicializarRangoFechasDobleFormato = (selectorInicio, selectorFin, opciones = {}) => {
  const minBase = parsearFechaFlexible(opciones.minDate || '1930-01-01')?.date || '1930-01-01';
  let ctrlFin = null;
  let ctrlInicio = null;

  const sincronizarMinimoFin = (isoInicio) => {
    if (!ctrlFin?.cal) return;
    const parsedIni = parsearFechaFlexible(isoInicio);
    ctrlFin.cal.set('minDate', parsedIni ? parsedIni.date : minBase);

    const isoFin = ctrlFin.obtenerIso();
    if (parsedIni && isoFin && isoFin < parsedIni.iso) {
      ctrlFin.establecerIso(parsedIni.iso, true);
    }
  };

  const notificarRango = () => {
    if (typeof opciones.alCambiarRango === 'function') {
      opciones.alCambiarRango(ctrlInicio?.obtenerIso() || '', ctrlFin?.obtenerIso() || '');
    }
  };

  ctrlInicio = inicializarCampoFechaDobleFormato(selectorInicio, {
    ...opciones,
    alSeleccionar: (iso) => {
      sincronizarMinimoFin(iso);
      notificarRango();
    },
    alReset: () => {
      ctrlFin?.cal?.set('minDate', minBase);
      notificarRango();
    },
  });

  const isoInicioInicial = ctrlInicio?.obtenerIso() || '';
  ctrlFin = inicializarCampoFechaDobleFormato(selectorFin, {
    ...opciones,
    minDate: isoInicioInicial || opciones.minDate || '1930-01-01',
    alSeleccionar: (isoFin) => {
      const isoIni = ctrlInicio?.obtenerIso() || '';
      if (isoIni && isoFin && isoFin < isoIni) {
        ctrlFin?.establecerIso(isoIni, false);
      }
      notificarRango();
    },
  });

  return {
    inicio: ctrlInicio,
    fin: ctrlFin,
    calInicio: ctrlInicio?.cal || null,
    calFin: ctrlFin?.cal || null,
  };
};
