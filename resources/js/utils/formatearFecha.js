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
