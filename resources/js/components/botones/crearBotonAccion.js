import { VARIANTES_BOTON_ACCION } from '/js/components/variantes.js';

/**
 * Botón o enlace compacto para acciones de fila.
 * @param {Object} opciones
 * @returns {string}
 */
export const crearBotonAccion = ({
  accion = '',
  etiqueta,
  icono = '',
  variante = 'zinc',
  href = null,
  id = '',
  deshabilitado = false,
  titulo = '',
  contador = null,
}) => {
  const clases = [
    'inline-flex items-center justify-center gap-1.5 rounded-lg px-2 py-2 text-xs font-semibold shadow-sm transition-all duration-200 focus-visible:outline focus-visible:outline-offset-2 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer whitespace-nowrap',
    VARIANTES_BOTON_ACCION[variante] || VARIANTES_BOTON_ACCION.zinc,
  ].join(' ');

  const iconoHtml = icono ? `<i data-lucide="${icono}" class="h-3.5 w-3.5 shrink-0" aria-hidden="true"></i>` : '';
  const contadorHtml =
    contador !== null && contador !== undefined
      ? `<span class="rounded-md bg-white/20 px-1.5 py-0.5 text-[11px] font-bold leading-none">${contador}</span>`
      : '';
  const titleAttr = titulo ? `title="${titulo}"` : '';
  const ariaLabel = `aria-label="${titulo || etiqueta}"`;

  if (href && !deshabilitado) {
    return `<a href="${href}" class="${clases}" ${titleAttr} ${ariaLabel}>${iconoHtml}<span>${etiqueta}</span>${contadorHtml}</a>`;
  }

  return `<button type="button" data-accion="${accion}" data-id="${id}" class="${clases}" ${titleAttr} ${ariaLabel} ${deshabilitado ? 'disabled' : ''}>${iconoHtml}<span>${etiqueta}</span>${contadorHtml}</button>`;
};
