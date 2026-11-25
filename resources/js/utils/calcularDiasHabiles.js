/**
 * Cálculo de días hábiles según Convención Colectiva INCES (Cláusulas 6, 7 y 29) y LOTTT:
 * - Jornada ordinaria: lunes a viernes (se excluyen sábados y domingos).
 * - Feriados fijos nacionales e institucionales INCES (Cláusula 7):
 *   01-01, 03-19 (San José), 04-19, 05-01, 06-24, 07-05, 07-24, 10-12,
 *   11-20 (Día del Trabajador INCES), 12-17, 12-24, 12-25 y 12-31.
 * - Feriados móviles: Lunes y Martes de Carnaval + Jueves y Viernes Santo.
 * - Sincronizable con el catálogo local `dias_feriados` servido por `/api/vacaciones/feriados`.
 */

const REGEX_FECHA_ISO = /^\d{4}-\d{2}-\d{2}$/;

const FERIADOS_FIJOS_MM_DD = [
  '01-01',
  '03-19',
  '04-19',
  '05-01',
  '06-24',
  '07-05',
  '07-24',
  '10-12',
  '11-20',
  '12-17',
  '12-24',
  '12-25',
  '12-31',
];

const formatearIsoUtc = (fechaUtc) => {
  const anio = fechaUtc.getUTCFullYear();
  const mes = String(fechaUtc.getUTCMonth() + 1).padStart(2, '0');
  const dia = String(fechaUtc.getUTCDate()).padStart(2, '0');
  return `${anio}-${mes}-${dia}`;
};

/**
 * Parsea una fecha `YYYY-MM-DD` estrictamente en UTC sin desfases de zona horaria.
 * @param {string} fechaIso
 * @returns {Date|null}
 */
export const parsearFechaIsoUtc = (fechaIso) => {
  const texto = String(fechaIso || '').trim();
  if (!REGEX_FECHA_ISO.test(texto)) return null;

  const fecha = new Date(`${texto}T00:00:00Z`);
  if (Number.isNaN(fecha.getTime())) return null;

  return formatearIsoUtc(fecha) === texto ? fecha : null;
};

/**
 * Valida que una cadena corresponda a una fecha calendario real en formato `YYYY-MM-DD`.
 * @param {string} fechaIso
 * @returns {boolean}
 */
export const esFechaIsoValida = (fechaIso) => parsearFechaIsoUtc(fechaIso) !== null;

const calcularDomingoPascuaUtc = (anio) => {
  const a = anio % 19;
  const b = Math.floor(anio / 100);
  const c = anio % 100;
  const d = Math.floor(b / 4);
  const e = b % 4;
  const f = Math.floor((b + 8) / 25);
  const g = Math.floor((b - f + 1) / 3);
  const h = (19 * a + b - d - g + 15) % 30;
  const i = Math.floor(c / 4);
  const k = c % 4;
  const l = (32 + 2 * e + 2 * i - h - k) % 7;
  const m = Math.floor((a + 11 * h + 22 * l) / 451);
  const mes = Math.floor((h + l - 7 * m + 114) / 31);
  const dia = ((h + l - 7 * m + 114) % 31) + 1;

  return new Date(Date.UTC(anio, mes - 1, dia));
};

const sumarDiasUtc = (baseUtc, dias) => {
  const copia = new Date(baseUtc.getTime());
  copia.setUTCDate(copia.getUTCDate() + dias);
  return copia;
};

const cacheFeriadosPorAnio = new Map();
const feriadosRemotosPorAnio = new Map();

/**
 * Registra la lista de feriados `YYYY-MM-DD` obtenida desde el backend (`/api/vacaciones/feriados`).
 * @param {string[]} fechasIso
 */
export const establecerFeriadosRemotos = (fechasIso = []) => {
  if (!Array.isArray(fechasIso)) return;

  feriadosRemotosPorAnio.clear();
  cacheFeriadosPorAnio.clear();

  fechasIso.forEach((fecha) => {
    const limpia = String(fecha || '').trim();
    if (!esFechaIsoValida(limpia)) return;
    const anio = Number(limpia.slice(0, 4));
    if (!feriadosRemotosPorAnio.has(anio)) {
      feriadosRemotosPorAnio.set(anio, new Set());
    }
    feriadosRemotosPorAnio.get(anio).add(limpia);
  });
};

/**
 * Devuelve la lista de feriados INCES (`YYYY-MM-DD`) de un año dado.
 * Prioriza los feriados cargados desde PostgreSQL si existen para ese año.
 * @param {number} anio
 * @returns {string[]}
 */
export const obtenerFeriadosIncesAnio = (anio) => {
  if (feriadosRemotosPorAnio.has(anio)) {
    return Array.from(feriadosRemotosPorAnio.get(anio)).sort();
  }

  if (cacheFeriadosPorAnio.has(anio)) {
    return cacheFeriadosPorAnio.get(anio);
  }

  const feriados = FERIADOS_FIJOS_MM_DD.map((mmdd) => `${anio}-${mmdd}`);
  const pascua = calcularDomingoPascuaUtc(anio);

  // Lunes (-48) y Martes (-47) de Carnaval + Jueves (-3) y Viernes (-2) Santo
  [-48, -47, -3, -2].forEach((offset) => {
    feriados.push(formatearIsoUtc(sumarDiasUtc(pascua, offset)));
  });

  cacheFeriadosPorAnio.set(anio, feriados);
  return feriados;
};

/**
 * Devuelve feriados INCES para un rango de años.
 * @param {number} [anioBase]
 * @returns {string[]}
 */
export const obtenerListaFeriadosInces = (anioBase = new Date().getFullYear()) => {
  const acumulados = new Set();
  for (let a = anioBase - 1; a <= anioBase + 3; a += 1) {
    obtenerFeriadosIncesAnio(a).forEach((f) => acumulados.add(f));
  }
  return Array.from(acumulados).sort();
};

/**
 * Determina si una fecha `YYYY-MM-DD` corresponde a un día hábil laborable (lunes a viernes no feriado).
 * @param {string} fechaIso
 * @param {Set<string>|string[]|null} [feriadosCustom]
 * @returns {boolean}
 */
export const esDiaHabilInces = (fechaIso, feriadosCustom = null) => {
  const fechaUtc = parsearFechaIsoUtc(fechaIso);
  if (!fechaUtc) return false;

  const diaSemana = fechaUtc.getUTCDay();
  if (diaSemana === 0 || diaSemana === 6) return false;

  const iso = formatearIsoUtc(fechaUtc);
  if (feriadosCustom instanceof Set) {
    return !feriadosCustom.has(iso);
  }
  if (Array.isArray(feriadosCustom)) {
    return !feriadosCustom.includes(iso);
  }

  const feriados = obtenerFeriadosIncesAnio(fechaUtc.getUTCFullYear());
  return !feriados.includes(iso);
};

/**
 * Calcula la fecha final (último día hábil de disfrute inclusive) y la fecha de reincorporación
 * (primer día hábil posterior a `fechaFinal`) a partir de `fechaInicio` hábil y `diasHabiles >= 1`.
 *
 * @param {string} fechaInicio
 * @param {number|string} diasHabiles
 * @param {Set<string>|string[]|null} [feriadosCustom]
 * @returns {{ fechaFinal: string, fechaReincorporacion: string }}
 */
export const calcularFechaFinalHabiles = (fechaInicio, diasHabiles, feriadosCustom = null) => {
  const dias = Number(diasHabiles);
  if (!Number.isInteger(dias) || dias < 1 || !esDiaHabilInces(fechaInicio, feriadosCustom)) {
    return { fechaFinal: '', fechaReincorporacion: '' };
  }

  const cursor = parsearFechaIsoUtc(fechaInicio);
  if (!cursor) {
    return { fechaFinal: '', fechaReincorporacion: '' };
  }

  let contados = 1;
  while (contados < dias) {
    cursor.setUTCDate(cursor.getUTCDate() + 1);
    const iso = formatearIsoUtc(cursor);
    if (esDiaHabilInces(iso, feriadosCustom)) {
      contados += 1;
    }
  }

  const fechaFinal = formatearIsoUtc(cursor);

  const cursorReinc = new Date(cursor.getTime());
  do {
    cursorReinc.setUTCDate(cursorReinc.getUTCDate() + 1);
  } while (!esDiaHabilInces(formatearIsoUtc(cursorReinc), feriadosCustom));

  return {
    fechaFinal,
    fechaReincorporacion: formatearIsoUtc(cursorReinc),
  };
};

/**
 * Calcula los días hábiles INCES inclusivos entre `inicio` y `fin` (`YYYY-MM-DD`).
 * Si `fin < inicio` o formato inválido, devuelve `0`.
 * @param {string} inicio
 * @param {string} fin
 * @returns {number}
 */
export const calcularDiasHabiles = (inicio, fin) => {
  if (!inicio || !fin || fin < inicio) return 0;

  const cursor = parsearFechaIsoUtc(inicio);
  const limite = parsearFechaIsoUtc(fin);
  if (!cursor || !limite || limite < cursor) {
    return 0;
  }

  let habiles = 0;
  while (cursor <= limite) {
    const iso = formatearIsoUtc(cursor);
    if (esDiaHabilInces(iso)) {
      habiles += 1;
    }
    cursor.setUTCDate(cursor.getUTCDate() + 1);
  }

  return habiles;
};
