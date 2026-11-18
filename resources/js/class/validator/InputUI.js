/**
 * InputUI.js
 * Gestiona exclusivamente los estados visuales (bordes, focus y mensajes de error)
 * utilizando clases utilitarias de Tailwind CSS.
 */
export class InputUI {
    // Clases nativas de Tailwind para cada estado
    static classes = {
        neutral: ['border-slate-300', 'focus:border-black', 'focus:ring-black'],
        valid: ['border-emerald-500', 'focus:border-emerald-500', 'focus:ring-emerald-500'],
        invalid: ['border-red-500', 'focus:border-red-500', 'focus:ring-red-500']
    };

    /**
     * Resuelve los elementos a partir de un selector (#id o .clase) o elemento directo
     * @param {string|HTMLElement} selector 
     * @returns {HTMLElement[]}
     */
    static getElements(selector) {
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
    static getErrorElement(input) {
        const container = input.closest('.space-y-1\\.5') || input.parentElement?.parentElement;
        if (container) {
            const msgEl = container.querySelector('.input-error-msg');
            if (msgEl) return msgEl;
        }
        return input.id ? document.getElementById(`${input.id}-error`) : null;
    }

    /**
     * Obtiene el botón trigger si el input es un select personalizado
     * @param {HTMLInputElement|HTMLSelectElement} input
     * @returns {HTMLElement|null}
     */
    static getTrigger(input) {
        const container = input.closest('.space-y-1\\.5') || input.parentElement?.parentElement;
        return container ? container.querySelector('.custom-select-trigger') : null;
    }

    /**
     * Limpia todas las clases de estado aplicadas al input y a su trigger si existe
     * @param {HTMLInputElement} input 
     */
    static clearStateClasses(input) {
        input.classList.remove(
            ...this.classes.neutral,
            ...this.classes.valid,
            ...this.classes.invalid
        );

        const trigger = this.getTrigger(input);
        if (trigger) {
            trigger.classList.remove(
                ...this.classes.neutral,
                ...this.classes.valid,
                ...this.classes.invalid
            );
        }
    }

    /**
     * Aplica el estado VÁLIDO (borde y focus verde de Tailwind)
     * @param {HTMLInputElement} input 
     */
    static setValid(input) {
        this.clearStateClasses(input);
        input.classList.add(...this.classes.valid);

        const trigger = this.getTrigger(input);
        if (trigger) {
            trigger.classList.add(...this.classes.valid);
        }

        const errorEl = this.getErrorElement(input);
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
        }
    }

    /**
     * Aplica el estado INVÁLIDO (borde y focus rojo de Tailwind) con mensaje
     * @param {HTMLInputElement} input 
     * @param {string} message 
     */
    static setInvalid(input, message) {
        this.clearStateClasses(input);
        input.classList.add(...this.classes.invalid);

        const trigger = this.getTrigger(input);
        if (trigger) {
            trigger.classList.add(...this.classes.invalid);
        }

        const errorEl = this.getErrorElement(input);
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        }
    }

    /**
     * Restablece al estado NEUTRAL (borde slate-300 y focus negro de Tailwind)
     * @param {HTMLInputElement} input 
     */
    static setNeutral(input) {
        this.clearStateClasses(input);
        input.classList.add(...this.classes.neutral);

        const trigger = this.getTrigger(input);
        if (trigger) {
            trigger.classList.add(...this.classes.neutral);
        }

        const errorEl = this.getErrorElement(input);
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
        }
    }
}
