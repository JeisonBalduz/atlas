/**
 * editar_empleado.js
 * Formulario de edición de trabajador conectado a POST /api/empleados/{id} con FormData.
 */

import { Solicitud } from '/js/class/Solicitud.js';
import { InputValidador } from '/js/class/validator/InputValidador.js';
import {
  actualizarOpcionesSelect,
  inicializarSelectsPersonalizados,
} from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';

const actualizarSubtituloEdicion = () => {
  const codigo = document.getElementById('codigo_empleado')?.value || '';
  const primerNombre = document.getElementById('primer_nombre')?.value || '';
  const primerApellido = document.getElementById('primer_apellido')?.value || '';
  const subtitulo = document.getElementById('subtitulo-editar-empleado');

  if (subtitulo && (primerNombre || codigo)) {
    subtitulo.textContent = `Editando ficha de ${primerNombre} ${primerApellido} (${codigo}).`.trim();
  }
};

const configurarValidaciones = () => {
  InputValidador.soloLetras('#primer_nombre, #segundo_nombre, #primer_apellido, #segundo_apellido', {
    minimo: 2,
    limite: 100,
    requerido: true,
  });
  InputValidador.soloNumeros('#cedula', { minimo: 6, limite: 9, requerido: true });
  InputValidador.correoElectronico('#correo');
  InputValidador.requerido('#formulario-editar-empleado select');
  InputValidador.archivo('.atlas-doc-input', { tamanoMaximoMB: 5 });
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();
  actualizarSubtituloEdicion();
  configurarValidaciones();

  const selectEstado = document.getElementById('estado');
  const selectMunicipio = document.getElementById('municipio');
  const formulario = document.getElementById('formulario-editar-empleado');
  const empleadoId = formulario?.dataset?.empleadoId;

  selectEstado?.addEventListener('change', async () => {
    const estadoId = selectEstado.value;
    actualizarOpcionesSelect('#municipio', {});
    actualizarOpcionesSelect('#parroquia_id', {});
    if (!estadoId) return;

    const res = await Solicitud.get(`/api/ubicacion/municipios/${estadoId}`);
    if (res.ok && res.data?.ok) {
      actualizarOpcionesSelect('#municipio', res.data.data || {});
    }
  });

  selectMunicipio?.addEventListener('change', async () => {
    const municipioId = selectMunicipio.value;
    actualizarOpcionesSelect('#parroquia_id', {});
    if (!municipioId) return;

    const res = await Solicitud.get(`/api/ubicacion/parroquias/${municipioId}`);
    if (res.ok && res.data?.ok) {
      actualizarOpcionesSelect('#parroquia_id', res.data.data || {});
    }
  });

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
      const res = await Solicitud.post(`/api/empleados/${empleadoId}`, new FormData(formulario));
      if (!res.ok || !res.data?.ok) {
        mostrarToast('error', res.message || 'No se pudo actualizar el trabajador.');
        return;
      }

      mostrarToast('success', res.data.message || 'Datos del trabajador actualizados correctamente.');
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
