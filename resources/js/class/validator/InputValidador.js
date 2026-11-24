import { InputUI } from './InputUI.js';
import {
  esCorreoValido,
  evaluarArchivo,
  evaluarNumeros,
  evaluarTexto,
  resolverLimiteCampo,
} from './reglasValidacion.js';

/**
 * InputValidador.js
 * Enlaza eventos de los campos del formulario delegando las reglas en `reglasValidacion.js` y el estado visual en `InputUI`.
 */
export class InputValidador {
  static soloLetras(selector, opciones = {}) {
    const permitirNumeros = Boolean(opciones.permitirNumeros);
    const requerido = Boolean(opciones.requerido);
    const minimo = opciones.minimo ?? null;
    const mensajeBase =
      opciones.mensajeError || (permitirNumeros ? 'Solo se permiten letras y números' : 'Solo se permiten letras');

    InputUI.obtenerElementos(selector).forEach((campo) => {
      const limite = resolverLimiteCampo(campo, opciones);

      campo.addEventListener('input', () => {
        const { valor, error } = evaluarTexto(campo.value, { permitirNumeros, limite, minimo, mensajeBase });
        campo.value = valor;

        if (error) return InputUI.establecerInvalido(campo, error);
        valor.trim() ? InputUI.establecerValido(campo) : InputUI.establecerNeutral(campo);
      });

      campo.addEventListener('blur', () => {
        const valor = campo.value.trim();
        if (!valor) {
          return requerido
            ? InputUI.establecerInvalido(campo, 'Este campo es obligatorio')
            : InputUI.establecerNeutral(campo);
        }
        if (minimo && valor.length < minimo) {
          InputUI.establecerInvalido(campo, `Mínimo ${minimo} caracteres`);
        }
      });
    });
  }

  static soloNumeros(selector, opciones = {}) {
    const permitirLetras = Boolean(opciones.permitirLetras);
    const requerido = Boolean(opciones.requerido);
    const minimo = opciones.minimo ?? null;
    const mensajeBase =
      opciones.mensajeError || (permitirLetras ? 'Solo se permiten números y letras' : 'Solo se permiten números');

    InputUI.obtenerElementos(selector).forEach((campo) => {
      const limite = resolverLimiteCampo(campo, opciones);

      campo.addEventListener('input', () => {
        const { valor, error } = evaluarNumeros(campo.value, { permitirLetras, limite, minimo, mensajeBase });
        campo.value = valor;

        if (error) return InputUI.establecerInvalido(campo, error);
        valor ? InputUI.establecerValido(campo) : InputUI.establecerNeutral(campo);
      });

      campo.addEventListener('blur', () => {
        if (!campo.value.trim()) {
          requerido
            ? InputUI.establecerInvalido(campo, 'Este campo es obligatorio')
            : InputUI.establecerNeutral(campo);
        }
      });
    });
  }

  static correoElectronico(selector, opciones = {}) {
    const requerido = Boolean(opciones.requerido);
    const mensajeError = opciones.mensajeError || 'Ingrese un correo electrónico válido';

    InputUI.obtenerElementos(selector).forEach((campo) => {
      const validar = (enBlur = false) => {
        const valor = campo.value.trim();
        if (!valor) {
          return requerido && enBlur
            ? InputUI.establecerInvalido(campo, 'El correo electrónico es requerido')
            : InputUI.establecerNeutral(campo);
        }
        if (esCorreoValido(valor)) return InputUI.establecerValido(campo);
        enBlur || valor.includes('@')
          ? InputUI.establecerInvalido(campo, mensajeError)
          : InputUI.establecerNeutral(campo);
      };

      campo.addEventListener('input', () => validar(false));
      campo.addEventListener('blur', () => validar(true));
    });
  }

  static requerido(selector, opciones = {}) {
    const mensaje = opciones.mensajeError || 'Seleccione una opción';

    InputUI.obtenerElementos(selector).forEach((campo) => {
      campo.addEventListener('change', () => {
        if (campo.value) return InputUI.establecerValido(campo);
        campo.required ? InputUI.establecerInvalido(campo, mensaje) : InputUI.establecerNeutral(campo);
      });
    });
  }

  static archivo(selector, opciones = {}) {
    const tamanoMaximoMB = opciones.tamanoMaximoMB ?? 5;
    const requerido = Boolean(opciones.requerido);

    InputUI.obtenerElementos(selector).forEach((campo) => {
      campo.addEventListener('change', () => {
        const resultado = evaluarArchivo(campo, { tamanoMaximoMB, requerido });
        if (resultado === null) return InputUI.establecerNeutral(campo);
        if (resultado !== '') return InputUI.establecerInvalido(campo, resultado);
        InputUI.establecerValido(campo);
      });
    });
  }
}
