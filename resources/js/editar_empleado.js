/**
 * editar_empleado.js
 * Formulario de edición de trabajador conectado a POST /api/empleados/{id} con FormData,
 * manteniendo íntegramente los datos y <select> hidratados desde PHP.
 */

import { InputValidador } from '/js/class/validator/InputValidador.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { inicializarSelectsPersonalizados } from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();

  const formulario = document.getElementById('formulario-editar-empleado');
  const empleadoId = formulario?.dataset?.empleadoId;

  const codigo = document.getElementById('codigo_empleado')?.value || '';
  const primerNombre = document.getElementById('primer_nombre')?.value || '';
  const primerApellido = document.getElementById('primer_apellido')?.value || '';
  const subtitulo = document.getElementById('subtitulo-editar-empleado');
  if (subtitulo && (primerNombre || codigo)) {
    subtitulo.textContent = `Editando ficha de ${primerNombre} ${primerApellido} (${codigo}).`.trim();
  }

  // Validaciones de campos
  InputValidador.soloLetras('#primer_nombre, #segundo_nombre, #primer_apellido, #segundo_apellido', {
    minimo: 2,
    limite: 100,
    requerido: true,
  });
  InputValidador.soloNumeros('#cedula', { minimo: 6, limite: 9, requerido: true });
  InputValidador.correoElectronico('#correo');
  InputValidador.requerido('#formulario-editar-empleado select');

  let enviando = false;

  formulario?.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    if (enviando) return;

    if (!empleadoId) {
      mostrarToast('error', 'No se encontró el identificador del trabajador a actualizar.');
      return;
    }

    const btnSubmit = formulario.querySelector('button[type="submit"]');
    const textoSpan = btnSubmit?.querySelector('span');
    const textoOriginal = textoSpan?.textContent || 'Actualizar Empleado';

    enviando = true;
    if (btnSubmit) btnSubmit.disabled = true;
    if (textoSpan) textoSpan.textContent = 'Guardando...';

    try {
      const formData = new FormData(formulario);
      const respuesta = await Solicitud.post(`/api/empleados/${empleadoId}`, formData);

      if (respuesta.status === 401) {
        window.location.href = '/autenticacion';
        return;
      }

      if (!respuesta.ok || !respuesta.data?.ok) {
        mostrarToast('error', respuesta.message || 'No se pudo actualizar el trabajador.');
        return;
      }

      mostrarToast('success', respuesta.data.message || 'Datos del trabajador actualizados correctamente.');
      setTimeout(() => {
        window.location.href = '/empleado/registros';
      }, 650);
    } finally {
      enviando = false;
      if (btnSubmit) btnSubmit.disabled = false;
      if (textoSpan) textoSpan.textContent = textoOriginal;
    }
  });
});
