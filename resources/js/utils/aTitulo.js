/**
 * Convierte un texto a formato Título (primera letra de cada palabra en mayúscula).
 * @param {string} texto
 * @returns {string}
 */
export const aTitulo = (texto) => {
  if (!texto) return '';
  return texto.replace(/(^|\s+)([a-záéíóúñü])/gi, (_coincidencia, espacio, caracter) => {
    return espacio + caracter.toUpperCase();
  });
};
