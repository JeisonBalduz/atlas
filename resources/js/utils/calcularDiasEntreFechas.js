/**
 * Calcula los días inclusive entre dos fechas ISO YYYY-MM-DD.
 * @param {string} inicio
 * @param {string} fin
 * @returns {number}
 */
export const calcularDiasEntreFechas = (inicio, fin) => {
  if (!inicio || !fin) return 0;
  const f1 = new Date(`${inicio}T00:00:00`);
  const f2 = new Date(`${fin}T00:00:00`);
  if (Number.isNaN(f1.getTime()) || Number.isNaN(f2.getTime()) || f2 < f1) return 0;
  return Math.max(0, Math.round((f2 - f1) / 86400000) + 1);
};
