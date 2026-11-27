import { parsearFechaFlexible } from '/js/utils/formatearFecha.js';

/**
 * Calcula los días inclusive entre dos fechas (`YYYY-MM-DD` o `dd-mm-yyyy`) usando parseo numérico.
 * @param {string} inicio
 * @param {string} fin
 * @returns {number}
 */
export const calcularDiasEntreFechas = (inicio, fin) => {
  const f1 = parsearFechaFlexible(inicio);
  const f2 = parsearFechaFlexible(fin);
  if (!f1 || !f2 || f2.date < f1.date) return 0;

  const utc1 = Date.UTC(f1.anio, f1.mes - 1, f1.dia);
  const utc2 = Date.UTC(f2.anio, f2.mes - 1, f2.dia);
  return Math.max(0, Math.round((utc2 - utc1) / 86400000) + 1);
};
