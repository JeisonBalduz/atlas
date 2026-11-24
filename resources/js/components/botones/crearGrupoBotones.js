/**
 * Envuelve un arreglo de botones HTML en el contenedor de acciones de fila.
 * @param {string[]} botonesHtml
 * @returns {string}
 */
export const crearGrupoBotones = (botonesHtml = []) =>
  `<div class="inline-flex items-center justify-end gap-1.5">${botonesHtml.filter(Boolean).join('')}</div>`;
