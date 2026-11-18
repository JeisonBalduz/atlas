import { InputUI } from './InputUI.js';

/**
 * InputValidator.js
 * Maneja las reglas de validación, formateo y eventos de los inputs,
 * delegando la manipulación visual en InputUI.
 */
export class InputValidator {
    /**
     * Convierte texto a Title Case (primera letra de cada palabra en mayúscula)
     * @param {string} text 
     * @returns {string}
     */
    static toTitleCase(text) {
        if (!text) return '';
        return text.replace(/(^|\s+)([a-záéíóúñü])/gi, (match, space, char) => {
            return space + char.toUpperCase();
        });
    }

    /**
     * Valida inputs de letras con Title Case.
     * Soporta permitir números opcionalmente mediante options.allowNumbers (boolean).
     * @param {string|HTMLElement} selector 
     * @param {Object} options 
     * @param {number} [options.limit] - Límite máximo de letras/caracteres
     * @param {number} [options.min] - Mínimo de letras/caracteres requeridos
     * @param {boolean} [options.allowNumbers=false] - Permite combinar letras y números
     * @param {string} [options.errorMessage]
     * @param {boolean} [options.required=false]
     */
    static lettersOnly(selector, options = {}) {
        const elements = InputUI.getElements(selector);
        const allowNumbers = options.allowNumbers ?? false;
        
        const defaultMsg = options.errorMessage || (allowNumbers 
            ? 'Solo se permiten letras y números' 
            : 'Solo se permiten letras');

        // Regex: solo letras o letras + números
        const regexFilter = allowNumbers 
            ? /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9\s]/g 
            : /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g;

        elements.forEach(input => {
            const limit = options.limit || 
                          (input.dataset.limit ? parseInt(input.dataset.limit, 10) : null) || 
                          (input.maxLength > 0 && input.maxLength < 1000 ? input.maxLength : null);

            input.addEventListener('input', () => {
                const original = input.value;
                let filtered = original.replace(regexFilter, '');

                let hasError = false;
                let errorMsg = defaultMsg;

                if (original !== filtered) {
                    hasError = true;
                }

                if (limit && filtered.length > limit) {
                    filtered = filtered.slice(0, limit);
                    hasError = true;
                    errorMsg = `Máximo ${limit} caracteres permitidos`;
                }

                input.value = this.toTitleCase(filtered);

                if (hasError) {
                    InputUI.setInvalid(input, errorMsg);
                } else if (options.min && filtered.trim().length > 0 && filtered.trim().length < options.min) {
                    InputUI.setInvalid(input, `Mínimo ${options.min} caracteres`);
                } else if (filtered.trim().length > 0) {
                    InputUI.setValid(input);
                } else {
                    input.value = '';
                    InputUI.setNeutral(input);
                }
            });

            input.addEventListener('blur', () => {
                const val = input.value.trim();
                if (!val) {
                    options.required ? InputUI.setInvalid(input, 'Este campo es obligatorio') : InputUI.setNeutral(input);
                } else if (options.min && val.length < options.min) {
                    InputUI.setInvalid(input, `Mínimo ${options.min} caracteres`);
                }
            });
        });
    }

    /**
     * Valida inputs numéricos con límite de dígitos.
     * Soporta permitir letras opcionalmente mediante options.allowLetters (boolean).
     * @param {string|HTMLElement} selector 
     * @param {Object} options 
     * @param {number} [options.limit] - Límite máximo de caracteres/dígitos
     * @param {number} [options.min] - Mínimo de dígitos requeridos (ej: cédula 6)
     * @param {boolean} [options.allowLetters=false] - Permite letras además de números
     * @param {string} [options.errorMessage]
     * @param {boolean} [options.required=false]
     */
    static numbersOnly(selector, options = {}) {
        const elements = InputUI.getElements(selector);
        const allowLetters = options.allowLetters ?? false;

        elements.forEach(input => {
            const limit = options.limit || 
                          (input.dataset.limit ? parseInt(input.dataset.limit, 10) : null) || 
                          (input.maxLength > 0 ? input.maxLength : null);

            const baseMsg = allowLetters ? 'Solo se permiten números y letras' : 'Solo se permiten números';
            const defaultMsg = options.errorMessage || (limit 
                ? `${baseMsg} (máximo ${limit} dígitos)` 
                : baseMsg);

            // Regex: solo dígitos o alfanumérico sin caracteres especiales
            const regexFilter = allowLetters 
                ? /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9]/g 
                : /[^0-9]/g;

            input.addEventListener('input', () => {
                const original = input.value;
                let filtered = original.replace(regexFilter, '');

                let hasError = false;
                let errorMsg = defaultMsg;

                if (original !== filtered) {
                    hasError = true;
                    errorMsg = baseMsg;
                }

                if (limit && filtered.length > limit) {
                    filtered = filtered.slice(0, limit);
                    hasError = true;
                    errorMsg = `Máximo ${limit} caracteres permitidos`;
                }

                input.value = filtered;

                if (hasError) {
                    InputUI.setInvalid(input, errorMsg);
                } else if (options.min && filtered.length > 0 && filtered.length < options.min) {
                    InputUI.setInvalid(input, `Mínimo ${options.min} dígitos`);
                } else if (filtered.length > 0) {
                    InputUI.setValid(input);
                } else {
                    InputUI.setNeutral(input);
                }
            });

            input.addEventListener('blur', () => {
                if (!input.value.trim()) {
                    options.required ? InputUI.setInvalid(input, 'Este campo es obligatorio') : InputUI.setNeutral(input);
                }
            });
        });
    }

    /**
     * Valida inputs de correo electrónico por selector (#id o .clase).
     * @param {string|HTMLElement} selector 
     * @param {Object} options 
     * @param {string} [options.errorMessage='Ingrese un correo electrónico válido']
     * @param {boolean} [options.required=false]
     */
    static email(selector, options = {}) {
        const elements = InputUI.getElements(selector);
        const defaultMsg = options.errorMessage || 'Ingrese un correo electrónico válido';
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        elements.forEach(input => {
            const validate = (isBlur = false) => {
                const val = input.value.trim();

                if (!val) {
                    if (options.required && isBlur) {
                        InputUI.setInvalid(input, 'El correo electrónico es requerido');
                    } else {
                        InputUI.setNeutral(input);
                    }
                    return;
                }

                if (emailRegex.test(val)) {
                    InputUI.setValid(input);
                } else {
                    if (isBlur || val.includes('@')) {
                        InputUI.setInvalid(input, defaultMsg);
                    } else {
                        InputUI.setNeutral(input);
                    }
                }
            };

            input.addEventListener('input', () => validate(false));
            input.addEventListener('blur', () => validate(true));
        });
    }

    /**
     * Valida campos de selección (select, date): verde si tiene valor.
     * @param {string|HTMLElement} selector
     * @param {Object} options
     * @param {string} [options.errorMessage='Seleccione una opción']
     */
    static required(selector, options = {}) {
        const msg = options.errorMessage || 'Seleccione una opción';

        InputUI.getElements(selector).forEach(field => {
            field.addEventListener('change', () => {
                if (field.value) return InputUI.setValid(field);
                field.required ? InputUI.setInvalid(field, msg) : InputUI.setNeutral(field);
            });
        });
    }

    /**
     * Valida inputs de archivo: formato (según atributo accept) y peso máximo.
     * @param {string|HTMLElement} selector
     * @param {Object} options
     * @param {number} [options.maxSizeMB=5]
     */
    static file(selector, options = {}) {
        const maxMB = options.maxSizeMB ?? 5;

        InputUI.getElements(selector).forEach(input => {
            input.addEventListener('change', () => {
                const file = input.files[0];
                if (!file) {
                    return input.required ? InputUI.setInvalid(input, 'Debe adjuntar un archivo') : InputUI.setNeutral(input);
                }

                const allowed = input.accept.split(',').map(a => a.trim().toLowerCase()).filter(Boolean);
                const ext = `.${file.name.split('.').pop().toLowerCase()}`;

                if (allowed.length && !allowed.includes(ext)) {
                    return InputUI.setInvalid(input, `Formato no permitido (${allowed.join(', ')})`);
                }
                if (file.size > maxMB * 1024 * 1024) {
                    return InputUI.setInvalid(input, `El archivo supera los ${maxMB} MB`);
                }
                InputUI.setValid(input);
            });
        });
    }
}
