import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import {
  crearAlertaConfirmacion,
  limpiarAlertaFormulario,
  notificarErrorFormulario,
  notificarExitoFormulario,
} from '/js/components/alerta/alerta.js';
import { crearBadge } from '/js/components/badge/crearBadge.js';
// import {
//   inicializarCalendario,
//   inicializarRangoFechas,
//   validarRangoFechas,
// } from '/js/components/calendario/calendario.js';
import { crearListaDetalle, Modal } from '/js/components/modal/Modal.js';
import {
  crearMarkupSelectPersonalizado,
  inicializarSelectsPersonalizados,
} from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import { columnasTablaAusencias } from '/js/templates/tabla_ausencias.js';
import { calcularDiasEntreFechas } from '/js/utils/calcularDiasEntreFechas.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

const leerMapaOpcionesSelect = (idSelect) => {
  const nodo = document.getElementById(idSelect);
  const mapa = {};
  if (!nodo) return mapa;
  Array.from(nodo.options).forEach((opt) => {
    if (opt.value) mapa[opt.value] = opt.textContent.trim();
  });
  return mapa;
};

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
    baseItems.push({
      etiqueta: 'Validación IVSS / Aseguradora',
      valorHtml: `${crearBadge('Validado', 'green')} · Código ${validado.codigo || '—'}`,
      subvalorHtml: `${validado.institucion_emisora || ''} · Expedido ${formatearFecha(validado.fecha_expide)} · Dr(a). ${validado.medico || '—'}`,
      anchoCompleto: true,
    });
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
      <form id="form-validar-reposo" class="rounded-2xl border border-yellow-200 bg-yellow-50/50 p-4 space-y-3" novalidate>
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
            <div class="relative">
              <input id="val-reposo-fecha" name="fecha_expide" type="date" value="${hoy}" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-black" />
            </div>
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

const renderFormularioEditarAusencia = (detalle, permisosMapa, repososMapa) => {
  const esPermiso = detalle.tipo_texto === 'Permiso';
  const bloqueEspecifico = esPermiso
    ? `
      <div>
        ${crearMarkupSelectPersonalizado({
          id: 'edit-aus-tppermiso',
          nombre: 'tppermiso_id',
          etiqueta: 'Tipo de permiso',
          opciones: permisosMapa,
          valorSeleccionado: detalle.tppermiso_id || '',
          buscador: true,
        })}
      </div>
      <div>
        <label for="edit-aus-motivo" class="block text-sm font-medium text-zinc-900 mb-1.5">Motivo *</label>
        <input id="edit-aus-motivo" name="motivo" type="text" maxlength="255" value="${detalle.motivo || ''}" required class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
      </div>
      <label class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2.5 cursor-pointer">
        <span class="text-sm font-medium text-zinc-900">Permiso remunerable</span>
        <input type="checkbox" name="remunerable" ${detalle.remunerable ? 'checked' : ''} class="h-4 w-4 rounded border-slate-300 text-marca focus:ring-marca-500" />
      </label>
    `
    : `
      <div>
        ${crearMarkupSelectPersonalizado({
          id: 'edit-aus-tpreposo',
          nombre: 'tpreposo_id',
          etiqueta: 'Tipo de reposo',
          opciones: repososMapa,
          valorSeleccionado: detalle.tpreposo_id || '',
          buscador: true,
        })}
      </div>
      <div>
        <label for="edit-aus-doctor" class="block text-sm font-medium text-zinc-900 mb-1.5">Médico tratante</label>
        <input id="edit-aus-doctor" name="doctor" type="text" maxlength="100" value="${detalle.doctor && detalle.doctor !== 'No especificado' ? detalle.doctor : ''}" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
      </div>
      <div>
        <label for="edit-aus-descripcion" class="block text-sm font-medium text-zinc-900 mb-1.5">Descripción médica *</label>
        <input id="edit-aus-descripcion" name="descripcion" type="text" maxlength="255" value="${detalle.descripcion || ''}" required class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
      </div>
    `;

  return `
    <form id="form-editar-ausencia" class="space-y-4" novalidate>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="edit-aus-inicio" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha inicio *</label>
          <div class="relative">
            <input id="edit-aus-inicio" name="fecha_inicio" type="date" value="${detalle.fecha_inicio || ''}" required class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
          </div>
        </div>
        <div>
          <label for="edit-aus-fin" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha fin *</label>
          <div class="relative">
            <input id="edit-aus-fin" name="fecha_fin" type="date" value="${detalle.fecha_fin || ''}" required class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
          </div>
        </div>
      </div>
      <div>
        <label for="edit-aus-dias" class="block text-sm font-medium text-zinc-900 mb-1.5">Días calculados</label>
        <input id="edit-aus-dias" name="dias_ausencia" type="number" value="${detalle.dias_ausencia || 1}" readonly class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2.5 text-sm font-semibold text-zinc-900 outline-none" />
      </div>
      ${bloqueEspecifico}
    </form>
  `;
};

const renderFormularioReanudarAusencia = (registro) => `
  <form id="form-reanudar-ausencia" class="space-y-4" novalidate>
    ${crearAlertaConfirmacion({
      variante: 'green',
      icono: 'rotate-ccw',
      mensajeHtml: `Usa esta opción si <strong>${registro.nombre_completo}</strong> retomó labores antes del <strong>${formatearFecha(registro.fecha_fin)}</strong> o no llegó a tomar la ausencia. Los días restantes serán devueltos.`,
    })}
    <div>
      <label for="reanudar-fecha" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha efectiva de reincorporación *</label>
      <div class="relative">
        <input id="reanudar-fecha" name="fecha_reincorporacion" type="date" value="${registro.fecha_inicio || ''}" required class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
      </div>
      <p class="mt-1 text-xs text-zinc-500">Si seleccionas la misma fecha de inicio (${formatearFecha(registro.fecha_inicio)}), se devolverán todos los días (${registro.dias_ausencia}).</p>
    </div>
  </form>
`;

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();

  const modalDetalle = new Modal('#modal-detalle-ausencia');
  const modalAccion = new Modal('#modal-accion-ausencia');
  const bloquePermiso = document.getElementById('bloque-campos-permiso');
  const bloqueReposo = document.getElementById('bloque-campos-reposo');
  const inputInicio = document.getElementById('fecha_inicio');
  const inputFin = document.getElementById('fecha_fin');
  const inputDias = document.getElementById('dias_ausencia');
  const nodoTabla = document.getElementById('tabla-ausencias');

  const institucionesMapa = leerMapaOpcionesSelect('tipo_institucion_emisora_id');
  const permisosMapa = leerMapaOpcionesSelect('tppermiso_id');
  const repososMapa = leerMapaOpcionesSelect('tpreposo_id');

  let filtroTipoActual = 'todos';

  const actualizarResumenCards = (resumen) => {
    if (!resumen) return;
    const permEl = document.getElementById('resumen-permisos-registrados');
    const repTotalEl = document.getElementById('resumen-reposos-registrados');
    const repVigEl = document.getElementById('resumen-reposos-vigentes');
    const totalTiposRep = nodoTabla?.dataset?.totalTiposReposo || '0';

    if (permEl) permEl.textContent = `${resumen.permisos_total ?? 0} permisos registrados en BD`;
    if (repTotalEl) repTotalEl.textContent = String(resumen.reposos_total ?? 0);
    if (repVigEl) repVigEl.textContent = `${resumen.reposos_vigentes_hoy ?? 0} activos hoy · ${totalTiposRep} tipos habilitados`;
  };

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

  // inicializarRangoFechas(inputInicio, inputFin, {
  //   alCambiarRango: actualizarDias,
  // });
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
              notificarErrorFormulario(formVal, 'Completa todos los datos de convalidación del reposo.');
              return false;
            }

            const resVal = await Solicitud.post(`/api/ausencias/reposos/${detalle.dtreposo_id}/validar`, payloadVal);
            if (!resVal.ok || !resVal.data?.ok) {
              notificarErrorFormulario(formVal, resVal.message || 'No se pudo convalidar el reposo médico.');
              return false;
            }

            notificarExitoFormulario(formVal, resVal.data.message || 'Reposo médico convalidado.');
            return true;
          }
        : null,
    });
  };

  const abrirModalEditarAusencia = async (registro) => {
    const res = await Solicitud.get(`/api/ausencias/${registro.ausencia_id}`);
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudo cargar la ausencia para editar.');
      return;
    }

    const detalle = res.data.data;
    modalAccion.abrir({
      titulo: `Editar ${detalle.tipo_texto} · ${detalle.nombre_completo}`,
      subtitulo: `Cédula ${detalle.cedula}`,
      contenidoHtml: renderFormularioEditarAusencia(detalle, permisosMapa, repososMapa),
      mostrarConfirmar: true,
      textoConfirmar: 'Guardar cambios',
      varianteConfirmar: 'marca',
      alConfirmar: async (dlg) => {
        const form = dlg.querySelector('#form-editar-ausencia');
        const inicio = form?.elements?.fecha_inicio?.value || '';
        const fin = form?.elements?.fecha_fin?.value || '';
        const dias = calcularDiasEntreFechas(inicio, fin);

        // if (!validarRangoFechas(inicio, fin) || dias <= 0) {
        //   notificarErrorFormulario(form, 'La fecha fin debe ser mayor o igual a la fecha de inicio.');
        //   return false;
        // }

        const payload = {
          fecha_inicio: inicio,
          fecha_fin: fin,
          dias_ausencia: dias,
          tppermiso_id: form?.elements?.tppermiso_id?.value || null,
          motivo: form?.elements?.motivo?.value?.trim() || '',
          remunerable: Boolean(form?.elements?.remunerable?.checked),
          tpreposo_id: form?.elements?.tpreposo_id?.value || null,
          doctor: form?.elements?.doctor?.value?.trim() || '',
          descripcion: form?.elements?.descripcion?.value?.trim() || '',
        };

        const resUpd = await Solicitud.put(`/api/ausencias/${registro.ausencia_id}`, payload);
        if (!resUpd.ok || !resUpd.data?.ok) {
          notificarErrorFormulario(form, resUpd.message || 'No se pudo actualizar la ausencia.');
          return false;
        }

        notificarExitoFormulario(form, resUpd.data.message || 'Ausencia actualizada correctamente.');
        await cargarAusencias();
        return true;
      },
    });

    if (modalAccion.dialogo) {
      const editIni = modalAccion.dialogo.querySelector('#edit-aus-inicio');
      const editFin = modalAccion.dialogo.querySelector('#edit-aus-fin');
      const editDias = modalAccion.dialogo.querySelector('#edit-aus-dias');
      inicializarRangoFechas(editIni, editFin, {
        alCambiarRango: (ini, fn) => {
          if (editDias) editDias.value = String(calcularDiasEntreFechas(ini, fn));
        },
      });
    }
  };

  const abrirModalReanudarAusencia = (registro) => {
    modalAccion.abrir({
      titulo: `Reanudar labores · ${registro.nombre_completo}`,
      subtitulo: `${registro.tipo_texto} (${formatearFecha(registro.fecha_inicio)} al ${formatearFecha(registro.fecha_fin)})`,
      contenidoHtml: renderFormularioReanudarAusencia(registro),
      mostrarConfirmar: true,
      textoConfirmar: 'Reanudar y devolver días',
      varianteConfirmar: 'green',
      alConfirmar: async (dlg) => {
        const form = dlg.querySelector('#form-reanudar-ausencia');
        const fechaReincorp = form?.elements?.fecha_reincorporacion?.value || '';

        if (!fechaReincorp) {
          notificarErrorFormulario(form, 'Indica la fecha efectiva de reincorporación.');
          return false;
        }

        const resRean = await Solicitud.post(`/api/ausencias/${registro.ausencia_id}/reanudar`, {
          fecha_reincorporacion: fechaReincorp,
        });

        if (!resRean.ok || !resRean.data?.ok) {
          notificarErrorFormulario(form, resRean.message || 'No se pudo procesar la reanudación.');
          return false;
        }

        notificarExitoFormulario(form, resRean.data.message || 'Ausencia reanudada correctamente.');
        await cargarAusencias();
        return true;
      },
    });

    // if (modalAccion.dialogo) {
    //   const inputRean = modalAccion.dialogo.querySelector('#reanudar-fecha');
    //   inicializarCalendario(inputRean, {
    //     minDate: registro.fecha_inicio || undefined,
    //     maxDate: registro.fecha_fin || undefined,
    //   });
    // }
  };

  const tabla = new GestorDataTable('#tabla-ausencias', {
    datos: [],
    ordenInicial: [[4, 'desc']],
    columnas: columnasTablaAusencias(),
    alAccionar: (accion, registro) => {
      if (accion === 'mostrar') return verDetalleAusencia(registro);
      if (accion === 'editar') return abrirModalEditarAusencia(registro);
      if (accion === 'reanudar') return abrirModalReanudarAusencia(registro);
    },
  });

  const cargarAusencias = async () => {
    const res = await Solicitud.get('/api/ausencias');
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudieron cargar las ausencias justificadas.');
      return;
    }

    const lista = Array.isArray(res.data.data) ? res.data.data : [];
    actualizarResumenCards(res.data.resumen);
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
    const inicio = inputInicio?.value || '';
    const fin = inputFin?.value || '';
    const dias = calcularDiasEntreFechas(inicio, fin);

    if (!empleadoId) {
      notificarErrorFormulario(formulario, 'Selecciona el trabajador para registrar la ausencia.');
      return;
    }

    if (!validarRangoFechas(inicio, fin) || dias <= 0) {
      notificarErrorFormulario(formulario, 'La fecha fin debe ser mayor o igual a la fecha de inicio.');
      return;
    }

    const payload = {
      tipo_ausencia: tipo,
      empleado_id: empleadoId,
      fecha_inicio: inicio,
      fecha_fin: fin,
      dias_ausencia: dias,
    };

    if (tipo === 1) {
      payload.tppermiso_id = Number(document.getElementById('tppermiso_id')?.value || 0);
      payload.motivo = document.getElementById('motivo')?.value.trim() || '';
      payload.remunerable = Boolean(document.getElementById('remunerable')?.checked);

      if (!payload.tppermiso_id || !payload.motivo) {
        notificarErrorFormulario(formulario, 'Selecciona el tipo de permiso e indica el motivo.');
        return;
      }
    } else {
      payload.tpreposo_id = Number(document.getElementById('tpreposo_id')?.value || 0);
      payload.tipo_institucion_emisora_id = Number(document.getElementById('tipo_institucion_emisora_id')?.value || 0);
      payload.doctor = document.getElementById('doctor')?.value.trim() || '';
      payload.descripcion = document.getElementById('descripcion')?.value.trim() || '';

      if (!payload.tpreposo_id || !payload.descripcion) {
        notificarErrorFormulario(formulario, 'Selecciona el tipo de reposo e indica la descripción médica.');
        return;
      }
    }

    enviando = true;
    const btnSubmit = formulario.querySelector('button[type="submit"]');
    if (btnSubmit) btnSubmit.disabled = true;

    try {
      const res = await Solicitud.post('/api/ausencias', payload);
      if (!res.ok || !res.data?.ok) {
        notificarErrorFormulario(formulario, res.message || 'No se pudo registrar la ausencia justificada.');
        return;
      }

      notificarExitoFormulario(formulario, res.data.message || 'Ausencia justificada registrada correctamente.');
      formulario.reset();
      limpiarAlertaFormulario(formulario);
      alternarBloques();
      await cargarAusencias();
    } finally {
      enviando = false;
      if (btnSubmit) btnSubmit.disabled = false;
    }
  });

  cargarAusencias();
});
