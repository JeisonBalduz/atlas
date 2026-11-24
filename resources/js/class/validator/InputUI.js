/**
 * InputUI.js
 * Gestiona exclusivamente los estados visuales (bordes, focus y mensajes de error) de los inputs.
 */
export class InputUI {
  static clases = {
    neutral: ['border-slate-300', 'focus:border-black', 'focus:ring-black'],
    valido: ['border-emerald-500', 'focus:border-emerald-500', 'focus:ring-emerald-500'],
    invalido: ['border-red-500', 'focus:border-red-500', 'focus:ring-red-500'],
  };

  static obtenerElementos(selector) {
    if (!selector) return [];
    if (typeof selector === 'string') return Array.from(document.querySelectorAll(selector));
    if (selector instanceof HTMLElement) return [selector];
    return [];
  }

  static obtenerElementoError(input) {
    const contenedor = input.closest('.space-y-1\\.5') || input.parentElement?.parentElement;
    const elementoMensaje = contenedor?.querySelector('.input-error-msg');
    if (elementoMensaje) return elementoMensaje;
    return input.id ? document.getElementById(`${input.id}-error`) : null;
  }

  static obtenerDisparador(input) {
    const contenedor = input.closest('.space-y-1\\.5') || input.parentElement?.parentElement;
    return contenedor?.querySelector('.custom-select-trigger') || null;
  }

  static aplicarEstado(input, estado, mensaje = '') {
    const todas = [...this.clases.neutral, ...this.clases.valido, ...this.clases.invalido];
    const nuevas = this.clases[estado] || this.clases.neutral;

    input.classList.remove(...todas);
    input.classList.add(...nuevas);

    const disparador = this.obtenerDisparador(input);
    if (disparador) {
      disparador.classList.remove(...todas);
      disparador.classList.add(...nuevas);
    }

    const elementoError = this.obtenerElementoError(input);
    if (elementoError) {
      elementoError.textContent = estado === 'invalido' ? mensaje : '';
      elementoError.classList.toggle('hidden', estado !== 'invalido');
    }
  }

  static establecerValido(input) {
    this.aplicarEstado(input, 'valido');
  }

  static establecerInvalido(input, mensaje) {
    this.aplicarEstado(input, 'invalido', mensaje);
  }

  static establecerNeutral(input) {
    this.aplicarEstado(input, 'neutral');
  }
}
