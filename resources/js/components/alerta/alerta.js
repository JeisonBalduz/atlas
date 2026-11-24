import { ESTILOS_ALERTA } from '/js/components/variantes.js';

/**
 * Muestra un mensaje de error en el elemento indicado por selector.
 * @param {string} selector
 * @param {string} mensaje
 */
export const mostrarError = (selector, mensaje) => {
  const elemento = document.querySelector(selector);
  if (!elemento) return;
  elemento.textContent = mensaje || '';
  elemento.classList.remove('hidden');
};

/**
 * Oculta y limpia el contenedor de error indicado por selector.
 * @param {string} selector
 */
export const ocultarError = (selector) => {
  const elemento = document.querySelector(selector);
  if (!elemento) return;
  elemento.textContent = '';
  elemento.classList.add('hidden');
};

/**
 * Genera una caja de alerta semántica para modales o avisos.
 * @param {Object} opciones
 * @param {string} opciones.mensajeHtml
 * @param {'red'|'green'|'yellow'|'sky'} [opciones.variante='red']
 * @param {string} [opciones.icono='']
 * @returns {string}
 */
export const crearAlertaConfirmacion = ({ mensajeHtml, variante = 'red', icono = '' }) => {
  const estilo = ESTILOS_ALERTA[variante] || ESTILOS_ALERTA.red;

  if (!icono) {
    return `<div class="rounded-xl border ${estilo.caja} p-4 text-sm">${mensajeHtml}</div>`;
  }

  return `
    <div class="flex items-start gap-3 rounded-xl border ${estilo.caja} p-4 text-sm">
      <i data-lucide="${icono}" class="mt-0.5 h-5 w-5 shrink-0 ${estilo.icono}" aria-hidden="true"></i>
      <p>${mensajeHtml}</p>
    </div>
  `;
};
