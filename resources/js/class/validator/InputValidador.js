import { InputUI } from './InputUI.js';

/**
 * InputValidador.js
 * Maneja las reglas de validación, formateo y eventos de los inputs,
 * delegando la manipulación visual en InputUI.
 */
export class InputValidador {
    /**
     * Convierte texto a formato título (primera letra de cada palabra en mayúscula)
     * @param {string} texto 
     * @returns {string}
     */
    static aTitulo(texto) {
        if (!texto) return '';
        return texto.replace(/(^|\s+)([a-záéíóúñü])/gi, (_coincidencia, espacio, caracter) => {
            return espacio + caracter.toUpperCase();
        });
    }

    /**
     * Valida inputs de letras con formato título.
     * Soporta permitir números opcionalmente mediante opciones.permitirNumeros (boolean).
     * @param {string|HTMLElement} selector 
     * @param {Object} opciones 
     * @param {number} [opciones.limite] - Límite máximo de letras/caracteres
     * @param {number} [opciones.minimo] - Mínimo de letras/caracteres requeridos
     * @param {boolean} [opciones.permitirNumeros=false] - Permite combinar letras y números
     * @param {string} [opciones.mensajeError]
     * @param {boolean} [opciones.requerido=false]
     */
    static soloLetras(selector, opciones = {}) {
        const elementos = InputUI.obtenerElementos(selector);
        const permitirNumeros = opciones.permitirNumeros ?? opciones.allowNumbers ?? false;
        const requerido = opciones.requerido ?? opciones.required ?? false;
        const minimo = opciones.minimo ?? opciones.min ?? null;

        const mensajePorDefecto = opciones.mensajeError || opciones.errorMessage || (permitirNumeros 
            ? 'Solo se permiten letras y números' 
            : 'Solo se permiten letras');

        // Expresión regular: solo letras o letras + números
        const filtroRegex = permitirNumeros 
            ? /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9\s]/g 
            : /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g;

        elementos.forEach(campo => {
            const limite = opciones.limite || opciones.limit || 
                          (campo.dataset.limite ? parseInt(campo.dataset.limite, 10) : null) || 
                          (campo.dataset.limit ? parseInt(campo.dataset.limit, 10) : null) || 
                          (campo.maxLength > 0 && campo.maxLength < 1000 ? campo.maxLength : null);

            campo.addEventListener('input', () => {
                const original = campo.value;
                let filtrado = original.replace(filtroRegex, '');

                let tieneError = false;
                let mensajeError = mensajePorDefecto;

                if (original !== filtrado) {
                    tieneError = true;
                }

                if (limite && filtrado.length > limite) {
                    filtrado = filtrado.slice(0, limite);
                    tieneError = true;
                    mensajeError = `Máximo ${limite} caracteres permitidos`;
                }

                campo.value = this.aTitulo(filtrado);

                if (tieneError) {
                    InputUI.establecerInvalido(campo, mensajeError);
                } else if (minimo && filtrado.trim().length > 0 && filtrado.trim().length < minimo) {
                    InputUI.establecerInvalido(campo, `Mínimo ${minimo} caracteres`);
                } else if (filtrado.trim().length > 0) {
                    InputUI.establecerValido(campo);
                } else {
                    campo.value = '';
                    InputUI.establecerNeutral(campo);
                }
            });

            campo.addEventListener('blur', () => {
                const valor = campo.value.trim();
                if (!valor) {
                    requerido ? InputUI.establecerInvalido(campo, 'Este campo es obligatorio') : InputUI.establecerNeutral(campo);
                } else if (minimo && valor.length < minimo) {
                    InputUI.establecerInvalido(campo, `Mínimo ${minimo} caracteres`);
                }
            });
        });
    }

    /**
     * Valida inputs numéricos con límite de dígitos.
     * Soporta permitir letras opcionalmente mediante opciones.permitirLetras (boolean).
     * @param {string|HTMLElement} selector 
     * @param {Object} opciones 
     * @param {number} [opciones.limite] - Límite máximo de caracteres/dígitos
     * @param {number} [opciones.minimo] - Mínimo de dígitos requeridos (ej: cédula 6)
     * @param {boolean} [opciones.permitirLetras=false] - Permite letras además de números
     * @param {string} [opciones.mensajeError]
     * @param {boolean} [opciones.requerido=false]
     */
    static soloNumeros(selector, opciones = {}) {
        const elementos = InputUI.obtenerElementos(selector);
        const permitirLetras = opciones.permitirLetras ?? opciones.allowLetters ?? false;
        const requerido = opciones.requerido ?? opciones.required ?? false;
        const minimo = opciones.minimo ?? opciones.min ?? null;

        elementos.forEach(campo => {
            const limite = opciones.limite || opciones.limit || 
                          (campo.dataset.limite ? parseInt(campo.dataset.limite, 10) : null) || 
                          (campo.dataset.limit ? parseInt(campo.dataset.limit, 10) : null) || 
                          (campo.maxLength > 0 ? campo.maxLength : null);

            const mensajeBase = permitirLetras ? 'Solo se permiten números y letras' : 'Solo se permiten números';
            const mensajePorDefecto = opciones.mensajeError || opciones.errorMessage || (limite 
                ? `${mensajeBase} (máximo ${limite} dígitos)` 
                : mensajeBase);

            // Expresión regular: solo dígitos o alfanumérico sin caracteres especiales
            const filtroRegex = permitirLetras 
                ? /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9]/g 
                : /[^0-9]/g;

            campo.addEventListener('input', () => {
                const original = campo.value;
                let filtrado = original.replace(filtroRegex, '');

                let tieneError = false;
                let mensajeError = mensajePorDefecto;

                if (original !== filtrado) {
                    tieneError = true;
                    mensajeError = mensajeBase;
                }

                if (limite && filtrado.length > limite) {
                    filtrado = filtrado.slice(0, limite);
                    tieneError = true;
                    mensajeError = `Máximo ${limite} caracteres permitidos`;
                }

                campo.value = filtrado;

                if (tieneError) {
                    InputUI.establecerInvalido(campo, mensajeError);
                } else if (minimo && filtrado.length > 0 && filtrado.length < minimo) {
                    InputUI.establecerInvalido(campo, `Mínimo ${minimo} dígitos`);
                } else if (filtrado.length > 0) {
                    InputUI.establecerValido(campo);
                } else {
                    InputUI.establecerNeutral(campo);
                }
            });

            campo.addEventListener('blur', () => {
                if (!campo.value.trim()) {
                    requerido ? InputUI.establecerInvalido(campo, 'Este campo es obligatorio') : InputUI.establecerNeutral(campo);
                }
            });
        });
    }

    /**
     * Valida inputs de correo electrónico por selector (#id o .clase).
     * @param {string|HTMLElement} selector 
     * @param {Object} opciones 
     * @param {string} [opciones.mensajeError='Ingrese un correo electrónico válido']
     * @param {boolean} [opciones.requerido=false]
     */
    static correoElectronico(selector, opciones = {}) {
        const elementos = InputUI.obtenerElementos(selector);
        const mensajePorDefecto = opciones.mensajeError || opciones.errorMessage || 'Ingrese un correo electrónico válido';
        const requerido = opciones.requerido ?? opciones.required ?? false;
        const regexCorreo = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        elementos.forEach(campo => {
            const validar = (esDesenfoque = false) => {
                const valor = campo.value.trim();

                if (!valor) {
                    if (requerido && esDesenfoque) {
                        InputUI.establecerInvalido(campo, 'El correo electrónico es requerido');
                    } else {
                        InputUI.establecerNeutral(campo);
                    }
                    return;
                }

                if (regexCorreo.test(valor)) {
                    InputUI.establecerValido(campo);
                } else {
                    if (esDesenfoque || valor.includes('@')) {
                        InputUI.establecerInvalido(campo, mensajePorDefecto);
                    } else {
                        InputUI.establecerNeutral(campo);
                    }
                }
            };

            campo.addEventListener('input', () => validar(false));
            campo.addEventListener('blur', () => validar(true));
        });
    }

    /**
     * Alias corto para correoElectronico
     */
    static correo(selector, opciones = {}) {
        return this.correoElectronico(selector, opciones);
    }

    /**
     * Valida campos de selección (select, date): verde si tiene valor.
     * @param {string|HTMLElement} selector
     * @param {Object} opciones
     * @param {string} [opciones.mensajeError='Seleccione una opción']
     */
    static requerido(selector, opciones = {}) {
        const mensaje = opciones.mensajeError || opciones.errorMessage || 'Seleccione una opción';

        InputUI.obtenerElementos(selector).forEach(campo => {
            campo.addEventListener('change', () => {
                if (campo.value) return InputUI.establecerValido(campo);
                campo.required ? InputUI.establecerInvalido(campo, mensaje) : InputUI.establecerNeutral(campo);
            });
        });
    }

    /**
     * Valida inputs de archivo: formato (según atributo accept) y peso máximo.
     * @param {string|HTMLElement} selector
     * @param {Object} opciones
     * @param {number} [opciones.tamanoMaximoMB=5]
     * @param {boolean} [opciones.requerido=false]
     */
    static archivo(selector, opciones = {}) {
        const tamanoMaximoMB = opciones.tamanoMaximoMB ?? opciones.maxSizeMB ?? 5;
        const requerido = opciones.requerido ?? opciones.required ?? false;

        InputUI.obtenerElementos(selector).forEach(campo => {
            campo.addEventListener('change', () => {
                const archivo = campo.files[0];
                const esObligatorio = campo.required || requerido;

                if (!archivo) {
                    return esObligatorio ? InputUI.establecerInvalido(campo, 'Debe adjuntar un archivo') : InputUI.establecerNeutral(campo);
                }

                const permitidos = campo.accept.split(',').map(extension => extension.trim().toLowerCase()).filter(Boolean);
                const extensionArchivo = `.${archivo.name.split('.').pop().toLowerCase()}`;

                if (permitidos.length && !permitidos.includes(extensionArchivo)) {
                    return InputUI.establecerInvalido(campo, `Formato no permitido (${permitidos.join(', ')})`);
                }
                if (archivo.size > tamanoMaximoMB * 1024 * 1024) {
                    return InputUI.establecerInvalido(campo, `El archivo supera los ${tamanoMaximoMB} MB`);
                }
                InputUI.establecerValido(campo);
            });
        });
    }

    // Alias para compatibilidad hacia atrás
    static toTitleCase(texto) { return this.aTitulo(texto); }
    static lettersOnly(selector, opciones) { return this.soloLetras(selector, opciones); }
    static numbersOnly(selector, opciones) { return this.soloNumeros(selector, opciones); }
    static email(selector, opciones) { return this.correoElectronico(selector, opciones); }
    static required(selector, opciones) { return this.requerido(selector, opciones); }
    static file(selector, opciones) { return this.archivo(selector, opciones); }
}
