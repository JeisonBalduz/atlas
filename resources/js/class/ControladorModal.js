/**
 * ControladorModal.js
 * Controlador accesible para el componente <dialog> nativo (`components/modal.blade.php`).
 * Soporta apertura con showModal(), inyección dinámica de contenido/formularios,
 * callbacks de confirmación y respaldo de cierre al clic en backdrop para navegadores sin `closedBy`.
 */

const VARIANTES_CONFIRMAR = {
  marca: ['bg-marca', 'hover:bg-marca-700', 'focus:ring-marca-500'],
  red: ['bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500'],
  green: ['bg-green-600', 'hover:bg-green-700', 'focus:ring-green-500'],
};

export class ControladorModal {
  /**
   * @param {string|HTMLDialogElement} selector - Selector CSS o nodo <dialog>
   */
  constructor(selector) {
    this.dialogo =
      typeof selector === "string" ? document.querySelector(selector) : selector;

    if (!this.dialogo) {
      throw new Error("ControladorModal: no se encontró el elemento <dialog> indicado.");
    }

    this.nodoTitulo = this.dialogo.querySelector("[data-modal-titulo]");
    this.nodoSubtitulo = this.dialogo.querySelector("[data-modal-subtitulo]");
    this.nodoCuerpo = this.dialogo.querySelector("[data-modal-cuerpo]");
    this.botonConfirmar = this.dialogo.querySelector("[data-modal-confirmar]");
    this.accionConfirmarActual = null;
    this.textoCargandoActual = "Procesando...";

    this.#vincularEventos();
  }

  /**
   * Abre la ventana modal actualizando opcionalmente sus textos, contenido, variantes y acción async.
   * @param {Object} [opciones]
   */
  abrir(opciones = {}) {
    if (opciones.titulo !== undefined && this.nodoTitulo) {
      this.nodoTitulo.textContent = opciones.titulo;
    }

    if (opciones.subtitulo !== undefined && this.nodoSubtitulo) {
      this.nodoSubtitulo.textContent = opciones.subtitulo;
      this.nodoSubtitulo.classList.toggle("hidden", !opciones.subtitulo);
    }

    if (opciones.contenidoHtml !== undefined && this.nodoCuerpo) {
      this.nodoCuerpo.innerHTML = opciones.contenidoHtml;
    }

    this.#configurarBotonConfirmar(opciones);

    if (!this.dialogo.open) {
      this.dialogo.showModal();
    }

    if (typeof window.lucide !== "undefined") {
      window.lucide.createIcons();
    }
  }

  #configurarBotonConfirmar(opciones) {
    if (!this.botonConfirmar) return;

    if (opciones.varianteConfirmar && VARIANTES_CONFIRMAR[opciones.varianteConfirmar]) {
      Object.values(VARIANTES_CONFIRMAR).forEach((clases) => {
        this.botonConfirmar.classList.remove(...clases);
      });
      this.botonConfirmar.classList.add(...VARIANTES_CONFIRMAR[opciones.varianteConfirmar]);
    }

    if (opciones.mostrarConfirmar !== undefined) {
      this.botonConfirmar.classList.toggle("hidden", !opciones.mostrarConfirmar);
    }

    if (opciones.textoConfirmar) {
      this.botonConfirmar.textContent = opciones.textoConfirmar;
    }

    this.textoCargandoActual = opciones.textoCargando || "Procesando...";
    this.accionConfirmarActual =
      typeof opciones.alConfirmar === "function" ? opciones.alConfirmar : null;
  }

  /**
   * Cierra el modal si se encuentra abierto.
   */
  cerrar() {
    if (this.dialogo.open) {
      this.dialogo.close();
    }
  }

  /**
   * Permite inyectar HTML dentro del cuerpo sin cerrar el modal.
   * @param {string} html
   */
  establecerContenido(html) {
    if (this.nodoCuerpo) {
      this.nodoCuerpo.innerHTML = html;
      if (typeof window.lucide !== "undefined") {
        window.lucide.createIcons();
      }
    }
  }

  async #ejecutarConfirmacion() {
    if (typeof this.accionConfirmarActual !== "function") return;

    const resultado = this.accionConfirmarActual(this.dialogo);
    if (typeof resultado?.then !== "function") {
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

  #vincularEventos() {
    this.dialogo.addEventListener("click", (evento) => {
      if (evento.target.closest("[data-modal-cerrar]")) {
        this.cerrar();
      }
    });

    if (this.botonConfirmar) {
      this.botonConfirmar.addEventListener("click", () => {
        this.#ejecutarConfirmacion();
      });
    }

    if (!("closedBy" in HTMLDialogElement.prototype)) {
      this.dialogo.addEventListener("click", (evento) => {
        if (evento.target !== this.dialogo) return;

        const rectangulo = this.dialogo.getBoundingClientRect();
        const clicDentroContenido =
          rectangulo.top <= evento.clientY &&
          evento.clientY <= rectangulo.top + rectangulo.height &&
          rectangulo.left <= evento.clientX &&
          evento.clientX <= rectangulo.left + rectangulo.width;

        if (!clicDentroContenido) {
          this.cerrar();
        }
      });
    }
  }
}
