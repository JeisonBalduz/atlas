/**
 * Cálculo de días hábiles según Convención Colectiva INCES (Cláusulas 6, 7 y 29) y LOTTT:
 * - Jornada ordinaria: lunes a viernes (se excluyen sábados y domingos).
 * - Feriados fijos nacionales e institucionales INCES (Cláusula 7):
 *   01-01, 03-19 (San José), 04-19, 05-01, 06-24, 07-05, 07-24, 10-12,
 *   11-20 (Día del Trabajador INCES), 12-17, 12-24, 12-25 y 12-31.
 * - Feriados móviles: Lunes y Martes de Carnaval + Jueves y Viernes Santo.
 */

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

/**
 * Devuelve la lista de feriados INCES (`YYYY-MM-DD`) de un año dado.
 * @param {number} anio
 * @returns {string[]}
 */
export const obtenerFeriadosIncesAnio = (anio) => {
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
  return Array.from(acumulados);
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

  const cursor = new Date(`${inicio}T00:00:00Z`);
  const limite = new Date(`${fin}T00:00:00Z`);
  if (Number.isNaN(cursor.getTime()) || Number.isNaN(limite.getTime()) || limite < cursor) {
    return 0;
  }

  let habiles = 0;
  while (cursor <= limite) {
    const diaSemana = cursor.getUTCDay();
    if (diaSemana !== 0 && diaSemana !== 6) {
      const iso = formatearIsoUtc(cursor);
      const feriados = obtenerFeriadosIncesAnio(cursor.getUTCFullYear());
      if (!feriados.includes(iso)) {
        habiles += 1;
      }
    }
    cursor.setUTCDate(cursor.getUTCDate() + 1);
  }

  return habiles;
};
