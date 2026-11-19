/**
 * InputUI.js
 * Gestiona exclusivamente los estados visuales (bordes, focus y mensajes de error)
 * utilizando clases utilitarias de Tailwind CSS.
 */
export class InputUI {
    // Clases de Tailwind para cada estado
    static clases = {
        neutral: ['border-slate-300', 'focus:border-black', 'focus:ring-black'],
        valido: ['border-emerald-500', 'focus:border-emerald-500', 'focus:ring-emerald-500'],
        invalido: ['border-red-500', 'focus:border-red-500', 'focus:ring-red-500'],
        // Retrocompatibilidad
        valid: ['border-emerald-500', 'focus:border-emerald-500', 'focus:ring-emerald-500'],
        invalid: ['border-red-500', 'focus:border-red-500', 'focus:ring-red-500']
    };

    static classes = this.clases;

    /**
     * Resuelve los elementos a partir de un selector (#id o .clase) o elemento directo
     * @param {string|HTMLElement} selector 
     * @returns {HTMLElement[]}
     */
    static obtenerElementos(selector) {
        if (!selector) return [];
        if (typeof selector === 'string') {
            return Array.from(document.querySelectorAll(selector));
        }
        if (selector instanceof HTMLElement) {
            return [selector];
        }
        return [];
    }

    /**
     * Obtiene el contenedor de error asociado al input
     * @param {HTMLInputElement} input 
     * @returns {HTMLElement|null}
     */
    static obtenerElementoError(input) {
        const contenedor = input.closest('.space-y-1\\.5') || input.parentElement?.parentElement;
        if (contenedor) {
            const elementoMensaje = contenedor.querySelector('.input-error-msg');
            if (elementoMensaje) return elementoMensaje;
        }
        return input.id ? document.getElementById(`${input.id}-error`) : null;
    }

    /**
     * Obtiene el botón disparador (trigger) si el input es un select personalizado
     * @param {HTMLInputElement|HTMLSelectElement} input
     * @returns {HTMLElement|null}
     */
    static obtenerDisparador(input) {
        const contenedor = input.closest('.space-y-1\\.5') || input.parentElement?.parentElement;
        return contenedor ? contenedor.querySelector('.custom-select-trigger') : null;
    }

    /**
     * Limpia todas las clases de estado aplicadas al input y a su disparador si existe
     * @param {HTMLInputElement} input 
     */
    static limpiarClasesEstado(input) {
        input.classList.remove(
            ...this.clases.neutral,
            ...this.clases.valido,
            ...this.clases.invalido
        );

        const disparador = this.obtenerDisparador(input);
        if (disparador) {
            disparador.classList.remove(
                ...this.clases.neutral,
                ...this.clases.valido,
                ...this.clases.invalido
            );
        }
    }

    /**
     * Aplica el estado VÁLIDO (borde y focus verde de Tailwind)
     * @param {HTMLInputElement} input 
     */
    static establecerValido(input) {
        this.limpiarClasesEstado(input);
        input.classList.add(...this.clases.valido);

        const disparador = this.obtenerDisparador(input);
        if (disparador) {
            disparador.classList.add(...this.clases.valido);
        }

        const elementoError = this.obtenerElementoError(input);
        if (elementoError) {
            elementoError.textContent = '';
            elementoError.classList.add('hidden');
        }
    }

    /**
     * Aplica el estado INVÁLIDO (borde y focus rojo de Tailwind) con mensaje
     * @param {HTMLInputElement} input 
     * @param {string} mensaje 
     */
    static establecerInvalido(input, mensaje) {
        this.limpiarClasesEstado(input);
        input.classList.add(...this.clases.invalido);

        const disparador = this.obtenerDisparador(input);
        if (disparador) {
            disparador.classList.add(...this.clases.invalido);
        }

        const elementoError = this.obtenerElementoError(input);
        if (elementoError) {
            elementoError.textContent = mensaje;
            elementoError.classList.remove('hidden');
        }
    }

    /**
     * Restablece al estado NEUTRAL (borde slate-300 y focus negro de Tailwind)
     * @param {HTMLInputElement} input 
     */
    static establecerNeutral(input) {
        this.limpiarClasesEstado(input);
        input.classList.add(...this.clases.neutral);

        const disparador = this.obtenerDisparador(input);
        if (disparador) {
            disparador.classList.add(...this.clases.neutral);
        }

        const elementoError = this.obtenerElementoError(input);
        if (elementoError) {
            elementoError.textContent = '';
            elementoError.classList.add('hidden');
        }
    }

    // Alias para compatibilidad hacia atrás
    static getElements(selector) { return this.obtenerElementos(selector); }
    static getErrorElement(input) { return this.obtenerElementoError(input); }
    static getTrigger(input) { return this.obtenerDisparador(input); }
    static clearStateClasses(input) { return this.limpiarClasesEstado(input); }
    static setValid(input) { return this.establecerValido(input); }
    static setInvalid(input, mensaje) { return this.establecerInvalido(input, mensaje); }
    static setNeutral(input) { return this.establecerNeutral(input); }
}
