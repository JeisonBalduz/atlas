import { mostrarToast } from '/js/components/toast/toast.js';
import { ESTILOS_ALERTA } from '/js/components/variantes.js';

const resolverFormulario = (referencia) => {
  if (!referencia) return null;
  if (typeof referencia === 'string') return document.querySelector(referencia);
  return referencia instanceof HTMLElement ? referencia : null;
};

const asegurarBannerFormulario = (contenedor) => {
  if (!contenedor) return null;

  let banner = contenedor.matches?.('[data-alerta-formulario]')
    ? contenedor
    : contenedor.querySelector('[data-alerta-formulario]');

  if (!banner) {
    banner = document.createElement('div');
    banner.setAttribute('data-alerta-formulario', '');
    banner.setAttribute('role', 'alert');
    banner.setAttribute('aria-live', 'assertive');
    banner.className =
      'col-span-full hidden w-full rounded-xl border-2 border-red-700 bg-red-600 px-4 py-3.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all';
    banner.innerHTML = `
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-start gap-2.5 min-w-0 flex-1">
          <svg class="mt-0.5 h-5 w-5 shrink-0 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <p data-alerta-texto class="leading-relaxed break-words"></p>
        </div>
        <button type="button" data-alerta-cerrar aria-label="Cerrar alerta" class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-white/85 transition-colors hover:bg-red-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-white cursor-pointer">
          <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    `;
    contenedor.prepend(banner);
  }

  if (!banner.dataset.alertaVinculada) {
    banner.dataset.alertaVinculada = '1';
    banner.querySelector('[data-alerta-cerrar]')?.addEventListener('click', () => {
      banner.classList.add('hidden');
    });
  }

  return banner;
};

/**
 * Oculta el banner de alerta inline dentro del formulario indicado.
 * @param {HTMLElement|string|null} formulario
 */
export const limpiarAlertaFormulario = (formulario) => {
  const nodo = resolverFormulario(formulario);
  const banner = nodo?.matches?.('[data-alerta-formulario]')
    ? nodo
    : nodo?.querySelector?.('[data-alerta-formulario]');
  if (!banner) return;

  const texto = banner.querySelector('[data-alerta-texto]');
  if (texto) texto.textContent = '';
  banner.classList.add('hidden');
};

/**
 * Regla Atlas para ERROR en formularios: muestra TANTO la alerta inline de alto contraste COMO el toast.
 * @param {HTMLElement|string|null} formulario
 * @param {string} mensaje
 */
export const notificarErrorFormulario = (formulario, mensaje) => {
  const textoFinal = String(mensaje || 'Verifica los campos del formulario e inténtalo nuevamente.').trim();
  const nodo = resolverFormulario(formulario);
  const banner = asegurarBannerFormulario(nodo);

  if (banner) {
    const texto = banner.querySelector('[data-alerta-texto]');
    if (texto) texto.textContent = textoFinal;
    banner.classList.remove('hidden');
    window.lucide?.createIcons({ attrs: {}, nameAttr: 'data-lucide' });
    banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  mostrarToast('error', textoFinal);
};

/**
 * Regla Atlas para ÉXITO (`ok`) en formularios: oculta cualquier alerta inline previa y muestra ÚNICAMENTE el toast.
 * @param {HTMLElement|string|null} formulario
 * @param {string} mensaje
 */
export const notificarExitoFormulario = (formulario, mensaje) => {
  limpiarAlertaFormulario(formulario);
  mostrarToast('success', String(mensaje || 'Operación realizada correctamente.').trim());
};

/**
 * Muestra un mensaje de error en el elemento indicado por selector (usado en autenticación).
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
