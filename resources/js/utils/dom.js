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

/**
 * Lee y parsea un JSON embebido dentro de una etiqueta <script id="..."> o fallback.
 * @param {string} idScript
 * @param {*} [valorPorDefecto={}]
 */
export const leerJsonDeScript = (idScript, valorPorDefecto = {}) => {
  try {
    const script = document.getElementById(idScript);
    if (!script?.textContent) return valorPorDefecto;
    return JSON.parse(script.textContent) || valorPorDefecto;
  } catch {
    return valorPorDefecto;
  }
};
