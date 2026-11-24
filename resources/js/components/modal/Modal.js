import { inicializarSelectsPersonalizados } from '/js/components/select/selectPersonalizado.js';
import { VARIANTES_BOTON_MODAL } from '/js/components/variantes.js';

/**
 * Genera una grilla <dl> de tarjetas de detalle estático para el cuerpo del modal.
 * @param {Array<{ etiqueta: string, valorHtml: string, subvalorHtml?: string, anchoCompleto?: boolean }>} items
 * @param {number} [columnas=2]
 * @returns {string}
 */
export const crearListaDetalle = (items = [], columnas = 2) => {
  const claseCols = columnas === 2 ? 'sm:grid-cols-2' : 'sm:grid-cols-1';
  const tarjetas = items
    .map(
      ({ etiqueta, valorHtml, subvalorHtml = '', anchoCompleto = false }) => `
        <div class="${anchoCompleto ? 'sm:col-span-2 ' : ''}rounded-xl border border-slate-200 bg-slate-50 p-3.5">
          <dt class="text-xs font-medium text-zinc-500">${etiqueta}</dt>
          <dd class="mt-1 font-medium text-zinc-900">${valorHtml}</dd>
          ${subvalorHtml ? `<dd class="text-xs text-zinc-500">${subvalorHtml}</dd>` : ''}
        </div>
      `
    )
    .join('');

  return `<dl class="grid grid-cols-1 gap-3 ${claseCols} text-sm">${tarjetas}</dl>`;
};

/**
 * Componente único y reutilizable de ventana modal sobre `<dialog>` (`components/modal.blade.php`).
 * Recibe cualquier contenido dinámico: tablas, detalle estático o formularios.
 */
export class Modal {
  /**
   * @param {string|HTMLDialogElement} selector
   */
  constructor(selector) {
    this.dialogo = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!this.dialogo) {
      throw new Error('Modal: no se encontró el elemento <dialog> indicado.');
    }

    this.nodoTitulo = this.dialogo.querySelector('[data-modal-titulo]');
    this.nodoSubtitulo = this.dialogo.querySelector('[data-modal-subtitulo]');
    this.nodoCuerpo = this.dialogo.querySelector('[data-modal-cuerpo]');
    this.botonConfirmar = this.dialogo.querySelector('[data-modal-confirmar]');
    this.accionConfirmarActual = null;
    this.textoCargandoActual = 'Procesando...';

    this.vincularEventos();
  }

  abrir(opciones = {}) {
    if (opciones.titulo !== undefined && this.nodoTitulo) {
      this.nodoTitulo.textContent = opciones.titulo;
    }

    if (opciones.subtitulo !== undefined && this.nodoSubtitulo) {
      this.nodoSubtitulo.textContent = opciones.subtitulo;
      this.nodoSubtitulo.classList.toggle('hidden', !opciones.subtitulo);
    }

    if (opciones.contenidoHtml !== undefined) {
      this.establecerContenido(opciones.contenidoHtml);
    }

    this.configurarBotonConfirmar(opciones);

    if (!this.dialogo.open) {
      this.dialogo.showModal();
    }

    window.lucide?.createIcons();
  }

  cerrar() {
    if (this.dialogo.open) {
      this.dialogo.close();
    }
  }

  establecerContenido(html) {
    if (!this.nodoCuerpo) return;
    this.nodoCuerpo.innerHTML = html;
    inicializarSelectsPersonalizados(this.nodoCuerpo);
    window.lucide?.createIcons();
  }

  configurarBotonConfirmar(opciones) {
    if (!this.botonConfirmar) return;

    if (opciones.varianteConfirmar && VARIANTES_BOTON_MODAL[opciones.varianteConfirmar]) {
      Object.values(VARIANTES_BOTON_MODAL).forEach((clases) => {
        this.botonConfirmar.classList.remove(...clases);
      });
      this.botonConfirmar.classList.add(...VARIANTES_BOTON_MODAL[opciones.varianteConfirmar]);
    }

    if (opciones.mostrarConfirmar !== undefined) {
      this.botonConfirmar.classList.toggle('hidden', !opciones.mostrarConfirmar);
    }

    if (opciones.textoConfirmar) {
      this.botonConfirmar.textContent = opciones.textoConfirmar;
    }

    this.textoCargandoActual = opciones.textoCargando || 'Procesando...';
    this.accionConfirmarActual = typeof opciones.alConfirmar === 'function' ? opciones.alConfirmar : null;
  }

  async ejecutarConfirmacion() {
    if (typeof this.accionConfirmarActual !== 'function') return;

    const resultado = this.accionConfirmarActual(this.dialogo);
    if (typeof resultado?.then !== 'function') {
      if (resultado !== false) this.cerrar();
      return;
    }

    const textoOriginal = this.botonConfirmar.textContent;
    this.botonConfirmar.disabled = true;
    this.botonConfirmar.textContent = this.textoCargandoActual;

    try {
      const resultadoFinal = await resultado;
      if (resultadoFinal !== false) {
        this.cerrar();
      }
    } finally {
      this.botonConfirmar.disabled = false;
      this.botonConfirmar.textContent = textoOriginal;
    }
  }

  vincularEventos() {
    this.dialogo.addEventListener('click', (evento) => {
      if (evento.target.closest('[data-modal-cerrar]')) {
        this.cerrar();
      }
    });

    this.botonConfirmar?.addEventListener('click', () => this.ejecutarConfirmacion());

    if (!('closedBy' in HTMLDialogElement.prototype)) {
      this.dialogo.addEventListener('click', (evento) => {
        if (evento.target !== this.dialogo) return;
        const rect = this.dialogo.getBoundingClientRect();
        const dentro =
          rect.top <= evento.clientY &&
          evento.clientY <= rect.top + rect.height &&
          rect.left <= evento.clientX &&
          evento.clientX <= rect.left + rect.width;
        if (!dentro) this.cerrar();
      });
    }
  }
}

export const ControladorModal = Modal;
