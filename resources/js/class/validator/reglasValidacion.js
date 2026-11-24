import { aTitulo } from '/js/utils/aTitulo.js';

const REGEX_CORREO = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

export const resolverLimiteCampo = (campo, opciones = {}) => {
  if (opciones.limite) return opciones.limite;
  if (campo.dataset.limite) return parseInt(campo.dataset.limite, 10);
  if (campo.maxLength > 0 && campo.maxLength < 1000) return campo.maxLength;
  return null;
};

export const evaluarTexto = (original, { permitirNumeros = false, limite = null, minimo = null, mensajeBase }) => {
  const regex = permitirNumeros ? /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9\s]/g : /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]/g;
  let filtrado = original.replace(regex, '');
  let error = original !== filtrado ? mensajeBase : '';

  if (limite && filtrado.length > limite) {
    filtrado = filtrado.slice(0, limite);
    error = `Máximo ${limite} caracteres permitidos`;
  }

  const valorFinal = filtrado.trim() ? aTitulo(filtrado) : '';
  if (!error && minimo && valorFinal.trim().length > 0 && valorFinal.trim().length < minimo) {
    error = `Mínimo ${minimo} caracteres`;
  }

  return { valor: valorFinal, error };
};

export const evaluarNumeros = (original, { permitirLetras = false, limite = null, minimo = null, mensajeBase }) => {
  const regex = permitirLetras ? /[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9]/g : /[^0-9]/g;
  let filtrado = original.replace(regex, '');
  let error = original !== filtrado ? mensajeBase : '';

  if (limite && filtrado.length > limite) {
    filtrado = filtrado.slice(0, limite);
    error = `Máximo ${limite} caracteres permitidos`;
  }

  if (!error && minimo && filtrado.length > 0 && filtrado.length < minimo) {
    error = `Mínimo ${minimo} dígitos`;
  }

  return { valor: filtrado, error };
};

export const esCorreoValido = (valor) => REGEX_CORREO.test(String(valor || '').trim());

export const evaluarArchivo = (campo, { tamanoMaximoMB = 5, requerido = false }) => {
  const archivo = campo.files?.[0];
  const esObligatorio = campo.required || requerido;

  if (!archivo) {
    return esObligatorio ? 'Debe adjuntar un archivo' : null;
  }

  const permitidos = (campo.accept || '')
    .split(',')
    .map((ext) => ext.trim().toLowerCase())
    .filter(Boolean);
  const extensionArchivo = `.${archivo.name.split('.').pop().toLowerCase()}`;

  if (permitidos.length && !permitidos.includes(extensionArchivo)) {
    return `Formato no permitido (${permitidos.join(', ')})`;
  }
  if (archivo.size > tamanoMaximoMB * 1024 * 1024) {
    return `El archivo supera los ${tamanoMaximoMB} MB`;
  }

  return '';
};
