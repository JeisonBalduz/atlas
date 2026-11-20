/**
 * ControladorModal.js
 * Controlador accesible para el componente <dialog> nativo (`components/modal.blade.php`).
 * Soporta apertura con showModal(), inyección dinámica de contenido/formularios,
 * callbacks de confirmación y respaldo de cierre al clic en backdrop para navegadores sin `closedBy`.
 */

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

    this.#vincularEventos();
  }

  /**
   * Abre la ventana modal actualizando opcionalmente sus textos, contenido y acción de confirmación.
   * @param {Object} [opciones]
   * @param {string} [opciones.titulo]
   * @param {string} [opciones.subtitulo]
   * @param {string} [opciones.contenidoHtml]
   * @param {string} [opciones.textoConfirmar]
   * @param {Function|null} [opciones.alConfirmar]
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

    if (this.botonConfirmar) {
      if (opciones.textoConfirmar) {
        this.botonConfirmar.textContent = opciones.textoConfirmar;
      }
      this.accionConfirmarActual =
        typeof opciones.alConfirmar === "function" ? opciones.alConfirmar : null;
    }

    if (!this.dialogo.open) {
      this.dialogo.showModal();
    }

    if (typeof window.lucide !== "undefined") {
      window.lucide.createIcons();
    }
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

  #vincularEventos() {
    // Botones de cierre internos
    this.dialogo.addEventListener("click", (evento) => {
      if (evento.target.closest("[data-modal-cerrar]")) {
        this.cerrar();
      }
    });

    // Botón de confirmación
    if (this.botonConfirmar) {
      this.botonConfirmar.addEventListener("click", () => {
        if (typeof this.accionConfirmarActual === "function") {
          const resultado = this.accionConfirmarActual(this.dialogo);
          if (resultado !== false) {
            this.cerrar();
          }
        }
      });
    }

    // Respaldo obligatorio de light-dismiss para navegadores sin soporte de `closedby="any"`
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
