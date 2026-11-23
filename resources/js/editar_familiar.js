/**
 * editar_familiar.js
 * Formulario de edición de carga familiar conectado a POST /api/familiares/{id} con FormData.
 */

import { Solicitud } from '/js/class/Solicitud.js';
import { InputValidador } from '/js/class/validator/InputValidador.js';
import { inicializarSelectsPersonalizados } from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';

const configurarValidaciones = () => {
  InputValidador.soloLetras('#primer_nombre, #segundo_nombre, #primer_apellido, #segundo_apellido', {
    minimo: 2,
    limite: 100,
    requerido: true,
  });
  InputValidador.soloNumeros('#cedula', { minimo: 6, limite: 9, requerido: true });
  InputValidador.soloNumeros('#tomo, #folio', { minimo: 1, limite: 6, requerido: true });
  InputValidador.requerido('#formulario-editar-familiar select, #fecha_nacimiento');
  InputValidador.archivo('.atlas-doc-fam-input', { tamanoMaximoMB: 5 });
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();
  configurarValidaciones();

  const formulario = document.getElementById('formulario-editar-familiar');
  const familiaId = formulario?.dataset?.familiaId;
  let enviando = false;

  formulario?.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    if (enviando) return;

    if (!familiaId) {
      mostrarToast('error', 'No se encontró el identificador del familiar a actualizar.');
      return;
    }

    const btnSubmit = formulario.querySelector('button[type="submit"]');
    const textoSpan = btnSubmit?.querySelector('span');
    const textoOriginal = textoSpan?.textContent || 'Actualizar Familiar';

    enviando = true;
    if (btnSubmit) btnSubmit.disabled = true;
    if (textoSpan) textoSpan.textContent = 'Guardando...';

    try {
      const res = await Solicitud.post(`/api/familiares/${familiaId}`, new FormData(formulario));
      if (!res.ok || !res.data?.ok) {
        mostrarToast('error', res.message || 'No se pudo actualizar el familiar.');
        return;
      }

      mostrarToast('success', res.data.message || 'Familiar actualizado correctamente.');
      setTimeout(() => {
        window.location.href = '/empleado/registros/familiar';
      }, 650);
    } finally {
      enviando = false;
      if (btnSubmit) btnSubmit.disabled = false;
      if (textoSpan) textoSpan.textContent = textoOriginal;
    }
  });
});
