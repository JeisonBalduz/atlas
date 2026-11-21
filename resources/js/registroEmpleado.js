/**
 * registroEmpleado.js
 * Formulario multipaso de registro de trabajadores conectado a POST /api/empleados,
 * POST /api/documentos y selects dependientes de /api/ubicacion/*.
 */

import { InputValidador } from '/js/class/validator/InputValidador.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { inicializarPasoMultiple } from '/js/components/pasoMultiple.js';
import {
  inicializarSelectsPersonalizados,
  actualizarOpcionesSelect,
} from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';

const resolverTipoDocumentoId = (mapaTipos, palabrasClave) => {
  const entradas = Object.entries(mapaTipos || {});
  if (entradas.length === 0) return null;

  for (const [id, nombre] of entradas) {
    const normalizado = String(nombre || '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');
    if (palabrasClave.some((clave) => normalizado.includes(clave))) {
      return id;
    }
  }

  return entradas[0][0];
};

const calcularEdad = (ano, mes, dia) => {
  if (!ano || !mes || !dia) return '';
  const hoy = new Date();
  let edad = hoy.getFullYear() - ano;
  const mesActual = hoy.getMonth() + 1;
  const diaActual = hoy.getDate();
  if (mesActual < mes || (mesActual === mes && diaActual < dia)) {
    edad -= 1;
  }
  return edad >= 0 && edad <= 99 ? String(edad) : '';
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();

  const selectAno = document.getElementById('ano');
  const selectMes = document.getElementById('meses');
  const selectDia = document.getElementById('dia');
  const inputEdad = document.getElementById('edad');

  const actualizarDiasDelMes = () => {
    const ano = parseInt(selectAno?.value || '', 10) || new Date().getFullYear();
    const mes = parseInt(selectMes?.value || '', 10);

    if (!mes) {
      actualizarOpcionesSelect(
        '#dia',
        Array.from({ length: 31 }, (_, indice) => indice + 1),
        selectDia?.value || ''
      );
      return;
    }

    const cantidadDias = new Date(ano, mes, 0).getDate();
    const dias = Array.from({ length: cantidadDias }, (_, indice) => indice + 1);
    actualizarOpcionesSelect('#dia', dias, selectDia?.value || '');
  };

  const actualizarEdadDesdeFecha = () => {
    const ano = parseInt(selectAno?.value || '', 10);
    const mes = parseInt(selectMes?.value || '', 10);
    const dia = parseInt(selectDia?.value || '', 10);
    const calculada = calcularEdad(ano, mes, dia);
    if (inputEdad && calculada !== '') {
      inputEdad.value = calculada;
    }
  };

  selectMes?.addEventListener('change', () => {
    actualizarDiasDelMes();
    actualizarEdadDesdeFecha();
  });
  selectAno?.addEventListener('change', () => {
    actualizarDiasDelMes();
    actualizarEdadDesdeFecha();
  });
  selectDia?.addEventListener('change', actualizarEdadDesdeFecha);

  // Selects dependientes de ubicación: Estado -> Municipio -> Parroquia
  const selectEstado = document.getElementById('estado');
  const selectMunicipio = document.getElementById('municipio');

  selectEstado?.addEventListener('change', async () => {
    const estadoId = selectEstado.value;
    actualizarOpcionesSelect('#municipio', {});
    actualizarOpcionesSelect('#parroquia', {});
    if (!estadoId) return;

    const res = await Solicitud.get(`/api/ubicacion/municipios/${estadoId}`);
    if (res.status === 401) {
      window.location.href = '/autenticacion';
      return;
    }
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudieron cargar los municipios.');
      return;
    }
    actualizarOpcionesSelect('#municipio', res.data.data || {});
  });

  selectMunicipio?.addEventListener('change', async () => {
    const municipioId = selectMunicipio.value;
    actualizarOpcionesSelect('#parroquia', {});
    if (!municipioId) return;

    const res = await Solicitud.get(`/api/ubicacion/parroquias/${municipioId}`);
    if (res.status === 401) {
      window.location.href = '/autenticacion';
      return;
    }
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudieron cargar las parroquias.');
      return;
    }
    actualizarOpcionesSelect('#parroquia', res.data.data || {});
  });

  // Validaciones por campo
  InputValidador.soloLetras('#primerNombre, #primerApellido, #segundoNombre, #segundoApellido', {
    minimo: 2,
    limite: 50,
    requerido: true,
  });

  InputValidador.soloLetras('#calle', {
    permitirNumeros: true,
    limite: 120,
  });

  InputValidador.soloNumeros('#cedula', {
    limite: 8,
    minimo: 6,
  });

  InputValidador.soloNumeros('#edad', {
    limite: 2,
  });

  InputValidador.soloNumeros('#telefono', {
    limite: 7,
    minimo: 7,
  });

  InputValidador.requerido('#form-registro select, #fechaing3');

  InputValidador.archivo('#achivoDis, #contrato, #notificacion', {
    tamanoMaximoMB: 5,
  });

  let enviando = false;

  inicializarPasoMultiple('#form-registro', {
    alFinalizar: async (formulario) => {
      if (enviando) return;
      enviando = true;

      const btnFinalizar = formulario.querySelector('[data-step-next]');
      const etiquetaBtn = formulario.querySelector('[data-step-next-label]');
      const textoOriginal = etiquetaBtn?.textContent || 'Finalizar';

      if (btnFinalizar) btnFinalizar.disabled = true;
      if (etiquetaBtn) etiquetaBtn.textContent = 'Guardando...';

      try {
        const datosFormulario = new FormData();
        const nombresCampos = new FormData(formulario);
        for (const [clave, valor] of nombresCampos.entries()) {
          if (['docArchivoDis', 'contratoArchivo', 'notacionAchivo'].includes(clave)) {
            continue;
          }
          datosFormulario.append(clave, valor);
        }

        const resEmpleado = await Solicitud.post('/api/empleados', datosFormulario);
        if (resEmpleado.status === 401) {
          window.location.href = '/autenticacion';
          return;
        }

        if (!resEmpleado.ok || !resEmpleado.data?.ok) {
          mostrarToast('error', resEmpleado.message || 'No se pudo registrar el trabajador.');
          return;
        }

        const empleadoId = resEmpleado.data?.data?.empleado_id;
        let tiposDocs = {};
        try {
          tiposDocs = JSON.parse(formulario.dataset.tiposDocumentos || '{}') || {};
        } catch {
          tiposDocs = {};
        }

        // Subir documentos adjuntos vía POST /api/documentos
        const adjuntos = [
          { input: document.getElementById('contrato'), claves: ['contrat'] },
          { input: document.getElementById('notificacion'), claves: ['notific'] },
          { input: document.getElementById('achivoDis'), claves: ['discap', 'certific'] },
        ];

        const erroresDocs = [];
        for (const item of adjuntos) {
          const archivo = item.input?.files?.[0];
          const tipoId = resolverTipoDocumentoId(tiposDocs, item.claves);
          if (!archivo || !empleadoId || !tipoId) continue;

          const fdDoc = new FormData();
          fdDoc.append('empleado_id', String(empleadoId));
          fdDoc.append('tipo_documento_id', String(tipoId));
          fdDoc.append('archivo', archivo);

          const resDoc = await Solicitud.post('/api/documentos', fdDoc);
          if (!resDoc.ok || !resDoc.data?.ok) {
            erroresDocs.push(resDoc.message || `No se pudo subir ${archivo.name}`);
          }
        }

        if (erroresDocs.length > 0) {
          mostrarToast('warning', `Trabajador registrado, pero con advertencia en documentos: ${erroresDocs[0]}`);
        } else {
          mostrarToast('success', resEmpleado.data.message || 'Trabajador registrado correctamente.');
        }

        setTimeout(() => {
          window.location.href = '/empleado/registros';
        }, 700);
      } finally {
        enviando = false;
        if (btnFinalizar) btnFinalizar.disabled = false;
        if (etiquetaBtn) etiquetaBtn.textContent = textoOriginal;
      }
    },
  });
});
