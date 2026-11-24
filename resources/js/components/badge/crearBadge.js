import { ESTILOS_BADGE } from '/js/components/variantes.js';

/**
 * Genera un badge semántico siguiendo la paleta institucional de Atlas.
 * @param {string} texto
 * @param {'green'|'red'|'yellow'|'sky'|'marca'|'neutro'} [variante='neutro']
 * @returns {string}
 */
export const crearBadge = (texto, variante = 'neutro') => {
  const clase = ESTILOS_BADGE[variante] || ESTILOS_BADGE.neutro;
  return `<span class="inline-flex items-center rounded-md border-none px-2.5 py-0.5 text-xs font-medium whitespace-nowrap ${clase}">${texto ?? ''}</span>`;
};
