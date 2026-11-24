import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { crearBadge } from '/js/components/badge/crearBadge.js';
import { crearListaDetalle, Modal } from '/js/components/modal/Modal.js';
import {
  crearMarkupSelectPersonalizado,
  inicializarSelectsPersonalizados,
} from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import { columnasTablaAusencias } from '/js/templates/tabla_ausencias.js';
import { calcularDiasEntreFechas } from '/js/utils/calcularDiasEntreFechas.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

const renderDetalleAusencia = (detalle, institucionesMapa = {}) => {
  if (detalle.tipo_texto === 'Permiso') {
    return crearListaDetalle([
      { etiqueta: 'Tipo de permiso', valorHtml: detalle.tipo_catalogo || 'Permiso administrativo' },
      {
        etiqueta: 'Remunerable',
        valorHtml: crearBadge(detalle.remunerable ? 'Sí' : 'No', detalle.remunerable ? 'green' : 'red'),
      },
      { etiqueta: 'Departamento', valorHtml: detalle.departamento || '—' },
      { etiqueta: 'Fecha de solicitud', valorHtml: formatearFecha(detalle.fecha_solicitud) },
      { etiqueta: 'Motivo', valorHtml: detalle.motivo || '—', anchoCompleto: true },
    ]);
  }

  const baseItems = [
    { etiqueta: 'Tipo de reposo', valorHtml: detalle.tipo_catalogo || 'Reposo médico' },
    { etiqueta: 'Institución emisora', valorHtml: detalle.institucion || 'No especificada' },
    { etiqueta: 'Médico tratante', valorHtml: detalle.doctor || 'No especificado' },
    { etiqueta: 'Fecha de solicitud', valorHtml: formatearFecha(detalle.fecha_solicitud) },
    { etiqueta: 'Descripción médica', valorHtml: detalle.descripcion || '—', anchoCompleto: true },
  ];

  const validado = detalle.reposo_validado;
  if (validado) {
    baseItems.push(
      {
        etiqueta: 'Validación IVSS / Aseguradora',
        valorHtml: `${crearBadge('Validado', 'green')} · Código ${validado.codigo || '—'}`,
        subvalorHtml: `${validado.institucion_emisora || ''} · Expedido ${formatearFecha(validado.fecha_expide)} · Dr(a). ${validado.medico || '—'}`,
        anchoCompleto: true,
      }
    );
    return crearListaDetalle(baseItems);
  }

  if (!detalle.dtreposo_id) {
    return crearListaDetalle(baseItems);
  }

  const hoy = new Date().toISOString().slice(0, 10);
  const selectInstHtml = crearMarkupSelectPersonalizado({
    id: 'val-reposo-institucion',
    nombre: 'tpinstitucion_emisora_id',
    etiqueta: 'Institución convalidadora',
    opciones: institucionesMapa,
    placeholder: 'Seleccionar institución...',
    requerido: true,
  });

  return `
    <div class="space-y-4">
      ${crearListaDetalle(baseItems)}
      <form id="form-validar-reposo" class="rounded-2xl border border-yellow-200 bg-yellow-50/50 p-4 space-y-3">
        <p class="text-xs font-semibold uppercase tracking-wider text-yellow-900">Convalidar reposo médico (IVSS)</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>${selectInstHtml}</div>
          <div>
            <label for="val-reposo-codigo" class="block text-sm font-medium text-zinc-900 mb-1.5">Código de reposo *</label>
            <input id="val-reposo-codigo" name="codigo" type="text" maxlength="50" required placeholder="Ej. IVSS-2026-01" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-black" />
          </div>
          <div>
            <label for="val-reposo-aseg" class="block text-sm font-medium text-zinc-900 mb-1.5">N.° Aseguradora *</label>
            <input id="val-reposo-aseg" name="num_aseguradora" type="number" min="1" required placeholder="Ej. 10452" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-black" />
          </div>
          <div>
            <label for="val-reposo-clave" class="block text-sm font-medium text-zinc-900 mb-1.5">Clave *</label>
            <input id="val-reposo-clave" name="clave" type="number" min="1" required placeholder="Ej. 8812" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-black" />
          </div>
          <div>
            <label for="val-reposo-fecha" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha expedición *</label>
            <input id="val-reposo-fecha" name="fecha_expide" type="date" value="${hoy}" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-black" />
          </div>
          <div>
            <label for="val-reposo-medico" class="block text-sm font-medium text-zinc-900 mb-1.5">Médico aval *</label>
            <input id="val-reposo-medico" name="medico" type="text" maxlength="100" value="${detalle.doctor || ''}" required placeholder="Nombre del médico" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-black" />
          </div>
        </div>
      </form>
    </div>
  `;
};

const leerInstitucionesDesdeSelect = () => {
  const selectInst = document.getElementById('tipo_institucion_emisora_id');
  const mapa = {};
  if (!selectInst) return mapa;
  Array.from(selectInst.options).forEach((opt) => {
    if (opt.value) mapa[opt.value] = opt.textContent.trim();
  });
  return mapa;
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();

  const modalDetalle = new Modal('#modal-detalle-ausencia');
  const bloquePermiso = document.getElementById('bloque-campos-permiso');
  const bloqueReposo = document.getElementById('bloque-campos-reposo');
  const inputInicio = document.getElementById('fecha_inicio');
  const inputFin = document.getElementById('fecha_fin');
  const inputDias = document.getElementById('dias_ausencia');
  const institucionesMapa = leerInstitucionesDesdeSelect();

  let filtroTipoActual = 'todos';

  const alternarBloques = () => {
    const tipo = document.querySelector('input[name="tipo_ausencia"]:checked')?.value || '1';
    const esPermiso = tipo === '1';
    bloquePermiso?.classList.toggle('hidden', !esPermiso);
    bloqueReposo?.classList.toggle('hidden', esPermiso);
  };
  document.querySelectorAll('input[name="tipo_ausencia"]').forEach((r) => r.addEventListener('change', alternarBloques));

  const actualizarDias = () => {
    const dias = calcularDiasEntreFechas(inputInicio?.value, inputFin?.value);
    if (inputDias) inputDias.value = dias > 0 ? String(dias) : '';
  };
  inputInicio?.addEventListener('change', actualizarDias);
  inputFin?.addEventListener('change', actualizarDias);

  const verDetalleAusencia = async (registro) => {
    modalDetalle.abrir({
      titulo: `${registro.tipo_texto || 'Ausencia'} · ${registro.nombre_completo || 'Trabajador'}`,
      subtitulo: `${formatearFecha(registro.fecha_inicio)} al ${formatearFecha(registro.fecha_fin)} (${registro.dias_ausencia} días)`,
      contenidoHtml: '<p class="py-8 text-center text-sm text-zinc-500">Consultando detalle...</p>',
      mostrarConfirmar: false,
    });

    const res = await Solicitud.get(`/api/ausencias/${registro.ausencia_id}`);
    if (!res.ok || !res.data?.ok) {
      modalDetalle.establecerContenido('<p class="py-6 text-center text-sm text-red-600">No se pudo cargar el detalle de la ausencia.</p>');
      return;
    }

    const detalle = res.data.data;
    const puedeConvalidar = detalle.tipo_texto === 'Reposo' && detalle.dtreposo_id && !detalle.reposo_validado;

    modalDetalle.abrir({
      titulo: `${detalle.tipo_texto} · ${detalle.nombre_completo}`,
      subtitulo: `${formatearFecha(detalle.fecha_inicio)} al ${formatearFecha(detalle.fecha_fin)} (${detalle.dias_ausencia} días)`,
      contenidoHtml: renderDetalleAusencia(detalle, institucionesMapa),
      mostrarConfirmar: Boolean(puedeConvalidar),
      textoConfirmar: 'Guardar convalidación',
      varianteConfirmar: 'marca',
      alConfirmar: puedeConvalidar
        ? async (dlg) => {
            const formVal = dlg.querySelector('#form-validar-reposo');
            const payloadVal = {
              tpinstitucion_emisora_id: Number(formVal?.elements?.tpinstitucion_emisora_id?.value || 0),
              codigo: formVal?.elements?.codigo?.value?.trim() || '',
              num_aseguradora: Number(formVal?.elements?.num_aseguradora?.value || 0),
              clave: Number(formVal?.elements?.clave?.value || 0),
              fecha_expide: formVal?.elements?.fecha_expide?.value || '',
              medico: formVal?.elements?.medico?.value?.trim() || '',
            };

            if (!payloadVal.tpinstitucion_emisora_id || !payloadVal.codigo || !payloadVal.num_aseguradora || !payloadVal.clave || !payloadVal.medico) {
              mostrarToast('error', 'Completa todos los datos de convalidación del reposo.');
              return false;
            }

            const resVal = await Solicitud.post(`/api/ausencias/reposos/${detalle.dtreposo_id}/validar`, payloadVal);
            if (!resVal.ok || !resVal.data?.ok) {
              mostrarToast('error', resVal.message || 'No se pudo convalidar el reposo médico.');
              return false;
            }

            mostrarToast('success', resVal.data.message || 'Reposo médico convalidado.');
            return true;
          }
        : null,
    });
  };

  const tabla = new GestorDataTable('#tabla-ausencias', {
    datos: [],
    ordenInicial: [[4, 'desc']],
    columnas: columnasTablaAusencias(),
    alAccionar: (accion, registro) => {
      if (accion === 'mostrar') verDetalleAusencia(registro);
    },
  });

  const cargarAusencias = async () => {
    const res = await Solicitud.get('/api/ausencias');
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudieron cargar las ausencias justificadas.');
      return;
    }

    const lista = Array.isArray(res.data.data) ? res.data.data : [];
    tabla.actualizarDatos(lista);
    if (filtroTipoActual !== 'todos') {
      tabla.filtrarColumna(2, filtroTipoActual);
    }
  };

  const botonesFiltro = document.querySelectorAll('.btn-filtro-ausencia');
  botonesFiltro.forEach((boton) => {
    boton.addEventListener('click', () => {
      filtroTipoActual = boton.dataset.filtroTipo || 'todos';
      botonesFiltro.forEach((btn) => {
        const activo = btn === boton;
        btn.classList.toggle('bg-marca', activo);
        btn.classList.toggle('text-white', activo);
        btn.classList.toggle('text-zinc-500', !activo);
      });
      tabla.filtrarColumna(2, filtroTipoActual);
    });
  });

  const formulario = document.getElementById('formulario-ausencia');
  let enviando = false;

  formulario?.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    if (enviando) return;

    const tipo = Number(document.querySelector('input[name="tipo_ausencia"]:checked')?.value || 1);
    const empleadoId = Number(document.getElementById('empleado_id')?.value || 0);
    const dias = Number(inputDias?.value || 0);

    if (!empleadoId || !inputInicio?.value || !inputFin?.value || dias <= 0) {
      mostrarToast('error', 'Completa el empleado y el rango de fechas de la ausencia.');
      return;
    }

    const payload = {
      tipo_ausencia: tipo,
      empleado_id: empleadoId,
      fecha_inicio: inputInicio.value,
      fecha_fin: inputFin.value,
      dias_ausencia: dias,
    };

    if (tipo === 1) {
      payload.tppermiso_id = Number(document.getElementById('tppermiso_id')?.value || 0);
      payload.motivo = document.getElementById('motivo')?.value.trim() || '';
      payload.remunerable = Boolean(document.getElementById('remunerable')?.checked);

      if (!payload.tppermiso_id || !payload.motivo) {
        mostrarToast('error', 'Selecciona el tipo de permiso e indica el motivo.');
        return;
      }
    } else {
      payload.tpreposo_id = Number(document.getElementById('tpreposo_id')?.value || 0);
      payload.tipo_institucion_emisora_id = Number(document.getElementById('tipo_institucion_emisora_id')?.value || 0);
      payload.doctor = document.getElementById('doctor')?.value.trim() || '';
      payload.descripcion = document.getElementById('descripcion')?.value.trim() || '';

      if (!payload.tpreposo_id || !payload.descripcion) {
        mostrarToast('error', 'Selecciona el tipo de reposo e indica la descripción médica.');
        return;
      }
    }

    enviando = true;
    const btnSubmit = formulario.querySelector('button[type="submit"]');
    if (btnSubmit) btnSubmit.disabled = true;

    try {
      const res = await Solicitud.post('/api/ausencias', payload);
      if (!res.ok || !res.data?.ok) {
        mostrarToast('error', res.message || 'No se pudo registrar la ausencia justificada.');
        return;
      }

      mostrarToast('success', res.data.message || 'Ausencia justificada registrada correctamente.');
      formulario.reset();
      alternarBloques();
      await cargarAusencias();
    } finally {
      enviando = false;
      if (btnSubmit) btnSubmit.disabled = false;
    }
  });

  cargarAusencias();
});
