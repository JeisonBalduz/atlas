import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import {
  crearAlertaConfirmacion,
  limpiarAlertaFormulario,
  notificarErrorFormulario,
  notificarExitoFormulario,
} from '/js/components/alerta/alerta.js';
import { crearBadge } from '/js/components/badge/crearBadge.js';
import {
  inicializarRangoFechas,
  validarRangoFechas,
} from '/js/components/calendario/calendario.js';
import { Modal } from '/js/components/modal/Modal.js';
import { inicializarSelectsPersonalizados } from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import {
  columnasTablaVacaciones,
  varianteEstatusVacaciones,
} from '/js/templates/tabla_vacaciones.js';
import { calcularDiasHabiles } from '/js/utils/calcularDiasHabiles.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

const renderDetalleVacaciones = (detalle) => {
  const filasPeriodos = (detalle.periodos_descontados || [])
    .map(
      (p) => `
        <li class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm">
          <span class="font-medium text-zinc-900">Período ${p.periodo}</span>
          <span class="font-semibold text-marca-700">${p.dias_descontados ?? p.dia_descontados ?? 0} días descontados</span>
        </li>
      `
    )
    .join('');

  const filasReposos = (detalle.reposos_afectados || [])
    .map(
      (r) => `
        <li class="flex items-center justify-between rounded-lg border border-sky-200 bg-sky-50 px-3.5 py-2 text-xs text-sky-900">
          <span>Reposo: ${r.tipo_reposo || 'Médico'} (${formatearFecha(r.fecha_inicio_reposo)} al ${formatearFecha(r.fecha_fin_reposo)})</span>
          <span class="font-semibold">${r.dias_afectados} días reintegrados</span>
        </li>
      `
    )
    .join('');

  const filasHistorial = (detalle.historial || [])
    .map(
      (h) => `
        <li class="rounded-lg border border-slate-200 bg-white p-3 text-sm">
          <div class="flex flex-wrap items-center justify-between gap-2">
            ${crearBadge(h.estatus_descripcion || h.estatus || 'Movimiento', varianteEstatusVacaciones(h.estatus_descripcion || h.estatus))}
            <span class="text-xs text-zinc-500">${formatearFecha(h.fecha_registro)}</span>
          </div>
          <p class="mt-1.5 text-xs text-zinc-700">${h.observacion || 'Sin observación'}</p>
        </li>
      `
    )
    .join('');

  return `
    <div class="space-y-5">
      <section>
        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500">Períodos descontados</h3>
        <ul class="space-y-2">${filasPeriodos || '<li class="text-xs text-zinc-500">Sin desglose registrado aún (se aplica al aprobar).</li>'}</ul>
      </section>
      ${filasReposos ? `<section><h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500">Reposos que interrumpieron disfrute</h3><ul class="space-y-2">${filasReposos}</ul></section>` : ''}
      <section>
        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500">Historial de movimientos</h3>
        <ul class="space-y-2">${filasHistorial || '<li class="text-xs text-zinc-500">Sin movimientos registrados.</li>'}</ul>
      </section>
    </div>
  `;
};

const renderFormularioEdicionVacaciones = (registro) => `
  <form id="form-editar-vacaciones" class="space-y-4" novalidate>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div>
        <label for="edit-vac-inicio" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha de inicio *</label>
        <div class="relative">
          <input id="edit-vac-inicio" name="fecha_inicio" type="date" value="${registro.fecha_inicio || ''}" required class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
        </div>
      </div>
      <div>
        <label for="edit-vac-fin" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha final *</label>
        <div class="relative">
          <input id="edit-vac-fin" name="fecha_final" type="date" value="${registro.fecha_final || ''}" required class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
        </div>
      </div>
    </div>
    <div>
      <label for="edit-vac-dias" class="block text-sm font-medium text-zinc-900 mb-1.5">Días hábiles calculados</label>
      <input id="edit-vac-dias" name="dias_solicitados" type="number" value="${registro.dias_solicitado || 0}" readonly class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2.5 text-sm font-semibold text-zinc-900 outline-none" />
    </div>
    <label class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 cursor-pointer">
      <span class="text-sm font-medium text-zinc-900">Requiere firma de Gerencia</span>
      <input type="checkbox" name="gerencia" ${registro.requiere_gerencia ? 'checked' : ''} class="h-4 w-4 rounded border-slate-300 text-marca focus:ring-marca-500" />
    </label>
  </form>
`;

const renderFormularioSuspenderVacaciones = (registro) => `
  <form id="form-suspender-vacaciones" class="space-y-4" novalidate>
    ${crearAlertaConfirmacion({
      variante: 'yellow',
      icono: 'pause-circle',
      mensajeHtml: `Al suspender la solicitud de <strong>${registro.nombre_completo}</strong> (${registro.dias_solicitado} días), los días no disfrutados se reintegrarán automáticamente a sus períodos abiertos.`,
    })}
    <div>
      <label for="susp-dias-disfrutados" class="block text-sm font-medium text-zinc-900 mb-1.5">Días efectivamente disfrutados antes de suspender</label>
      <input id="susp-dias-disfrutados" name="dias_disfrutados" type="number" min="0" max="${registro.dias_solicitado || 0}" value="0" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
      <p class="mt-1 text-xs text-zinc-500">Deja <strong>0</strong> si no llegó a iniciar el disfrute (se devuelven los ${registro.dias_solicitado || 0} días).</p>
    </div>
    <div>
      <label for="susp-observacion" class="block text-sm font-medium text-zinc-900 mb-1.5">Motivo de suspensión</label>
      <input id="susp-observacion" name="observacion" type="text" maxlength="200" placeholder="Ej: Necesidad del servicio / Interrupción administrativa" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
    </div>
  </form>
`;

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();

  const nodoTabla = document.getElementById('tabla-vacaciones');
  const esAdmin = nodoTabla?.dataset?.esAdmin === '1';
  const modalDetalle = new Modal('#modal-detalle-vacaciones');
  const modalAccion = new Modal('#modal-accion-vacaciones');

  const selectEmpleado = document.getElementById('empleado_id');
  const tarjetaSaldo = document.getElementById('tarjeta-saldo-vacaciones');
  const nodoDiasPendientes = document.getElementById('saldo-dias-pendientes');
  const nodoPeriodosActivos = document.getElementById('saldo-periodos-activos');
  const inputInicio = document.getElementById('fecha_inicio');
  const inputFin = document.getElementById('fecha_final');
  const inputDias = document.getElementById('dias_solicitado');
  const filtroEstatus = document.getElementById('filtro-estatus-vacaciones');

  let saldoActualEmpleado = null;

  const actualizarResumenCards = (resumen) => {
    if (!resumen) return;
    const aprobadasEl = document.getElementById('resumen-vacaciones-aprobadas');
    const pendientesEl = document.getElementById('resumen-vacaciones-pendientes');
    if (aprobadasEl && resumen.aprobadas !== undefined) aprobadasEl.textContent = String(resumen.aprobadas);
    if (pendientesEl) {
      pendientesEl.textContent = `${resumen.pendientes ?? 0} pendientes · ${resumen.suspendidas ?? 0} suspendidas por reposo`;
    }
  };

  const consultarSaldoEmpleado = async (empleadoId) => {
    saldoActualEmpleado = null;
    if (!empleadoId) {
      tarjetaSaldo?.classList.add('hidden');
      return;
    }

    const res = await Solicitud.get(`/api/vacaciones/saldo/${empleadoId}`, {
      claveCancelacion: 'saldo-vacaciones',
    });

    if (res.aborted) return;
    if (!res.ok || !res.data?.ok) {
      tarjetaSaldo?.classList.add('hidden');
      return;
    }

    const saldo = res.data.data || {};
    const diasPendientes = Number(saldo.dias_pendientes || 0);
    saldoActualEmpleado = diasPendientes;
    const periodosTexto = saldo.resumen_periodos || 'Sin períodos abiertos';

    if (nodoDiasPendientes) {
      nodoDiasPendientes.textContent = `${diasPendientes} días`;
      nodoDiasPendientes.dataset.dias = String(diasPendientes);
    }
    if (nodoPeriodosActivos) nodoPeriodosActivos.textContent = periodosTexto;
    tarjetaSaldo?.classList.remove('hidden');
  };

  selectEmpleado?.addEventListener('change', () => consultarSaldoEmpleado(selectEmpleado.value));

  const actualizarDiasHabiles = () => {
    const dias = calcularDiasHabiles(inputInicio?.value, inputFin?.value);
    if (inputDias) inputDias.value = dias > 0 ? String(dias) : '';
  };

  inicializarRangoFechas(inputInicio, inputFin, {
    soloHabiles: true,
    alCambiarRango: actualizarDiasHabiles,
  });
  inputInicio?.addEventListener('change', actualizarDiasHabiles);
  inputFin?.addEventListener('change', actualizarDiasHabiles);

  const ejecutarCambioEstatus = async (id, payload) => {
    const res = await Solicitud.post(`/api/vacaciones/${id}/estatus`, payload);
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudo actualizar la solicitud de vacaciones.');
      return false;
    }

    mostrarToast('success', res.data.message || 'Solicitud de vacaciones actualizada.');
    await cargarSolicitudes();
    if (selectEmpleado?.value) consultarSaldoEmpleado(selectEmpleado.value);
    return true;
  };

  const abrirConfirmacionAprobacion = (registro, porGerencia) => {
    const id = registro.solicitud_vacaciones_id;
    const etiquetaNivel = porGerencia ? 'Gerencia' : 'Recursos Humanos';
    const nuevoEstatus = porGerencia ? 4 : 2;

    modalAccion.abrir({
      titulo: `Aprobar por ${etiquetaNivel}`,
      subtitulo: `Solicitud #${id} · ${registro.nombre_completo}`,
      contenidoHtml: crearAlertaConfirmacion({
        variante: 'green',
        icono: 'check-circle',
        mensajeHtml: `¿Confirmas aprobar por <strong>${etiquetaNivel}</strong> los <strong>${registro.dias_solicitado} días hábiles</strong> (${formatearFecha(registro.fecha_inicio)} al ${formatearFecha(registro.fecha_final)})? Los días se descontarán desde los períodos abiertos más antiguos.`,
      }),
      mostrarConfirmar: true,
      textoConfirmar: `Aprobar (${etiquetaNivel})`,
      varianteConfirmar: 'green',
      alConfirmar: () => ejecutarCambioEstatus(id, { estatus: nuevoEstatus }),
    });
  };

  const abrirModalSuspender = (registro) => {
    const id = registro.solicitud_vacaciones_id;
    modalAccion.abrir({
      titulo: `Suspender vacaciones · #${id}`,
      subtitulo: `${registro.nombre_completo} · ${formatearFecha(registro.fecha_inicio)} al ${formatearFecha(registro.fecha_final)}`,
      contenidoHtml: renderFormularioSuspenderVacaciones(registro),
      mostrarConfirmar: true,
      textoConfirmar: 'Suspender y reintegrar',
      varianteConfirmar: 'marca',
      alConfirmar: async (dlg) => {
        const form = dlg.querySelector('#form-suspender-vacaciones');
        const disfrutados = Number(form?.elements?.dias_disfrutados?.value || 0);
        const observacion = form?.elements?.observacion?.value?.trim() || '';

        if (disfrutados < 0 || disfrutados > Number(registro.dias_solicitado || 0)) {
          notificarErrorFormulario(form, 'Los días disfrutados deben estar entre 0 y los días solicitados.');
          return false;
        }

        const res = await Solicitud.post(`/api/vacaciones/${id}/estatus`, {
          estatus: 6,
          dias_disfrutados: disfrutados,
          observacion,
        });

        if (!res.ok || !res.data?.ok) {
          notificarErrorFormulario(form, res.message || 'No se pudo suspender la vacación.');
          return false;
        }

        notificarExitoFormulario(form, res.data.message || 'Vacación suspendida y días reintegrados.');
        await cargarSolicitudes();
        if (selectEmpleado?.value) consultarSaldoEmpleado(selectEmpleado.value);
        return true;
      },
    });
  };

  const abrirModalEditar = (registro) => {
    const id = registro.solicitud_vacaciones_id;
    modalAccion.abrir({
      titulo: `Editar solicitud #${id}`,
      subtitulo: `${registro.nombre_completo} · ${registro.cedula}`,
      contenidoHtml: renderFormularioEdicionVacaciones(registro),
      mostrarConfirmar: true,
      textoConfirmar: 'Guardar cambios',
      varianteConfirmar: 'marca',
      alConfirmar: async (dlg) => {
        const form = dlg.querySelector('#form-editar-vacaciones');
        const inicio = form?.elements?.fecha_inicio?.value || '';
        const fin = form?.elements?.fecha_final?.value || '';
        const dias = calcularDiasHabiles(inicio, fin);
        const gerencia = Boolean(form?.elements?.gerencia?.checked);

        if (!validarRangoFechas(inicio, fin)) {
          notificarErrorFormulario(form, 'La fecha final debe ser mayor o igual a la fecha de inicio.');
          return false;
        }
        if (dias <= 0) {
          notificarErrorFormulario(form, 'El rango seleccionado debe contener al menos un día hábil INCES.');
          return false;
        }

        const res = await Solicitud.put(`/api/vacaciones/${id}`, {
          fecha_inicio: inicio,
          fecha_final: fin,
          dias_solicitados: dias,
          gerencia,
        });

        if (!res.ok || !res.data?.ok) {
          notificarErrorFormulario(form, res.message || 'No se pudo actualizar la solicitud.');
          return false;
        }

        notificarExitoFormulario(form, res.data.message || 'Solicitud actualizada correctamente.');
        await cargarSolicitudes();
        if (selectEmpleado?.value) consultarSaldoEmpleado(selectEmpleado.value);
        return true;
      },
    });

    if (modalAccion.dialogo) {
      const editInicio = modalAccion.dialogo.querySelector('#edit-vac-inicio');
      const editFin = modalAccion.dialogo.querySelector('#edit-vac-fin');
      const editDias = modalAccion.dialogo.querySelector('#edit-vac-dias');
      const recalcularEdit = () => {
        if (editDias) editDias.value = String(calcularDiasHabiles(editInicio?.value, editFin?.value));
      };
      inicializarRangoFechas(editInicio, editFin, {
        soloHabiles: true,
        alCambiarRango: recalcularEdit,
      });
    }
  };

  const verDetalleSolicitud = async (registro) => {
    const id = registro.solicitud_vacaciones_id;
    modalDetalle.abrir({
      titulo: `Solicitud #${id} · ${registro.nombre_completo || 'Trabajador'}`,
      subtitulo: `${formatearFecha(registro.fecha_inicio)} al ${formatearFecha(registro.fecha_final)} (${registro.dias_solicitado} días hábiles)`,
      contenidoHtml: '<p class="py-8 text-center text-sm text-zinc-500">Consultando períodos e historial...</p>',
      mostrarConfirmar: false,
    });

    const res = await Solicitud.get(`/api/vacaciones/${id}`);
    if (!res.ok || !res.data?.ok) {
      modalDetalle.establecerContenido('<p class="py-6 text-center text-sm text-red-600">No se pudo cargar el detalle de la solicitud.</p>');
      return;
    }

    const detalle = res.data.data || {};
    const esPendiente = Number(registro.estatus) === 1 || registro.estatus_descripcion === 'Pendiente';

    modalDetalle.abrir({
      titulo: `Solicitud #${id} · ${registro.nombre_completo || 'Trabajador'}`,
      subtitulo: `${formatearFecha(registro.fecha_inicio)} al ${formatearFecha(registro.fecha_final)} (${registro.dias_solicitado} días hábiles)`,
      contenidoHtml: renderDetalleVacaciones(detalle),
      mostrarConfirmar: esPendiente,
      textoConfirmar: registro.requiere_gerencia ? 'Aprobar por Gerencia' : 'Aprobar por RRHH',
      varianteConfirmar: 'green',
      alConfirmar: esPendiente
        ? () => ejecutarCambioEstatus(id, { estatus: registro.requiere_gerencia ? 4 : 2 })
        : null,
    });
  };

  const tabla = new GestorDataTable('#tabla-vacaciones', {
    datos: [],
    ordenInicial: [[3, 'desc']],
    columnas: columnasTablaVacaciones(esAdmin),
    alAccionar: (accion, registro) => {
      if (accion === 'mostrar') return verDetalleSolicitud(registro);
      if (accion === 'aprobar-rrhh') return abrirConfirmacionAprobacion(registro, false);
      if (accion === 'aprobar-gerencia') return abrirConfirmacionAprobacion(registro, true);
      if (accion === 'suspender') return abrirModalSuspender(registro);
      if (accion === 'editar') return abrirModalEditar(registro);
    },
  });

  const cargarSolicitudes = async () => {
    const res = await Solicitud.get('/api/vacaciones');
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudieron consultar las solicitudes de vacaciones.');
      return;
    }

    const lista = Array.isArray(res.data.data) ? res.data.data : [];
    actualizarResumenCards(res.data.resumen);
    tabla.actualizarDatos(lista);
    if (filtroEstatus?.value && filtroEstatus.value !== 'todos') {
      tabla.filtrarColumna(5, filtroEstatus.value);
    }
  };

  filtroEstatus?.addEventListener('change', (evento) => {
    tabla.filtrarColumna(5, evento.target.value);
  });

  const formulario = document.getElementById('formulario-vacaciones');
  let enviando = false;

  formulario?.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    if (enviando) return;

    const empleadoId = Number(selectEmpleado?.value || 0);
    const inicio = inputInicio?.value || '';
    const fin = inputFin?.value || '';
    const dias = calcularDiasHabiles(inicio, fin);
    const gerencia = Boolean(document.getElementById('gerencia')?.checked);

    if (!empleadoId) {
      notificarErrorFormulario(formulario, 'Debes seleccionar el trabajador solicitante.');
      return;
    }

    if (!validarRangoFechas(inicio, fin)) {
      notificarErrorFormulario(formulario, 'La fecha final debe ser mayor o igual a la fecha de inicio.');
      return;
    }

    if (dias <= 0) {
      notificarErrorFormulario(formulario, 'El rango elegido no contiene días hábiles laborables según la normativa INCES.');
      return;
    }

    if (saldoActualEmpleado !== null && saldoActualEmpleado < dias) {
      notificarErrorFormulario(
        formulario,
        `El trabajador dispone de ${saldoActualEmpleado} días pendientes y está solicitando ${dias} días hábiles.`
      );
      return;
    }

    enviando = true;
    const btnSubmit = formulario.querySelector('button[type="submit"]');
    if (btnSubmit) btnSubmit.disabled = true;

    try {
      const res = await Solicitud.post('/api/vacaciones', {
        empleado_id: empleadoId,
        dias_solicitados: dias,
        fecha_inicio: inicio,
        fecha_final: fin,
        gerencia,
      });

      if (!res.ok || !res.data?.ok) {
        notificarErrorFormulario(formulario, res.message || 'No se pudo registrar la solicitud de vacaciones.');
        return;
      }

      notificarExitoFormulario(formulario, res.data.message || 'Solicitud de vacaciones registrada correctamente.');
      formulario.reset();
      limpiarAlertaFormulario(formulario);
      tarjetaSaldo?.classList.add('hidden');
      saldoActualEmpleado = null;
      await cargarSolicitudes();
    } finally {
      enviando = false;
      if (btnSubmit) btnSubmit.disabled = false;
    }
  });

  cargarSolicitudes();
});
