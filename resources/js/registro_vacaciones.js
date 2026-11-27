import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import {
  crearAlertaConfirmacion,
  limpiarAlertaFormulario,
  notificarErrorFormulario,
  notificarExitoFormulario,
} from '/js/components/alerta/alerta.js';
import { crearBadge } from '/js/components/badge/crearBadge.js';
import { refrescarReglasCalendario } from '/js/components/calendario/calendario.js';
import { Modal } from '/js/components/modal/Modal.js';
import { inicializarSelectsPersonalizados } from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import {
  columnasTablaVacaciones,
  varianteEstatusVacaciones,
} from '/js/templates/tabla_vacaciones.js';
import {
  calcularDiasDisfrutadosPorCorte,
  calcularFechaFinalHabiles,
  esDiaHabilInces,
  esFechaIsoValida,
  establecerFeriadosRemotos,
} from '/js/utils/calcularDiasHabiles.js';
import {
  fechaIsoAVisual,
  fechaVisualAIso,
  formatearFecha,
  inicializarCampoFechaDobleFormato,
  parsearFechaFlexible,
} from '/js/utils/formatearFecha.js';

const toggleMensajeCampo = (nodoError, mensaje = '') => {
  if (!nodoError) return;
  if (mensaje) {
    nodoError.textContent = mensaje;
    nodoError.classList.remove('hidden');
  } else {
    nodoError.textContent = '';
    nodoError.classList.add('hidden');
  }
};

const actualizarCalculoVacaciones = ({
  inputInicio,
  inputInicioIso = null,
  inputDias,
  inputFin,
  inputFinIso = null,
  nodoReincorporacion,
  nodoErrorInicio,
  nodoErrorDias,
  saldoMaximo = null,
}) => {
  const inicioTexto = String(inputInicio?.value || '').trim();
  const inicio = fechaVisualAIso(inputInicioIso?.value || inicioTexto);
  const diasTexto = String(inputDias?.value || '').trim();

  toggleMensajeCampo(nodoErrorInicio, '');
  toggleMensajeCampo(nodoErrorDias, '');

  let hayError = false;

  if (inicioTexto !== '' && (!inicio || !esFechaIsoValida(inicio) || !esDiaHabilInces(inicio))) {
    toggleMensajeCampo(
      nodoErrorInicio,
      'La fecha de inicio debe ser un día hábil laborable (lunes a viernes, no feriado).'
    );
    hayError = true;
  }

  let dias = 0;
  if (diasTexto !== '') {
    if (!/^\d+$/.test(diasTexto) || Number(diasTexto) < 1) {
      toggleMensajeCampo(nodoErrorDias, 'Ingresa una cantidad entera de días mayor o igual a 1.');
      hayError = true;
    } else {
      dias = Number(diasTexto);
      if (saldoMaximo !== null && dias > saldoMaximo) {
        toggleMensajeCampo(
          nodoErrorDias,
          `Los días ingresados (${dias}) superan el saldo disponible (${saldoMaximo} días).`
        );
        hayError = true;
      }
    }
  }

  if (hayError || inicio === '' || dias < 1) {
    if (inputFin) inputFin.value = '';
    if (inputFinIso) inputFinIso.value = '';
    if (nodoReincorporacion) {
      nodoReincorporacion.textContent = '';
      nodoReincorporacion.classList.add('hidden');
    }
    return { valido: false, inicio, dias, fechaFinal: '', fechaReincorporacion: '' };
  }

  const { fechaFinal, fechaReincorporacion } = calcularFechaFinalHabiles(inicio, dias);
  if (!fechaFinal) {
    if (inputFin) inputFin.value = '';
    if (inputFinIso) inputFinIso.value = '';
    if (nodoReincorporacion) {
      nodoReincorporacion.textContent = '';
      nodoReincorporacion.classList.add('hidden');
    }
    return { valido: false, inicio, dias, fechaFinal: '', fechaReincorporacion: '' };
  }

  if (inputFin) inputFin.value = fechaIsoAVisual(fechaFinal);
  if (inputFinIso) inputFinIso.value = fechaFinal;
  if (nodoReincorporacion && fechaReincorporacion) {
    nodoReincorporacion.textContent = `Reincorporación estimada: ${formatearFecha(fechaReincorporacion)} (siguiente día hábil)`;
    nodoReincorporacion.classList.remove('hidden');
  }

  return { valido: true, inicio, dias, fechaFinal, fechaReincorporacion };
};

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

const renderFormularioEdicionVacaciones = (registro) => {
  const editInicioIso = parsearFechaFlexible(registro.fecha_inicio)?.iso || '';
  const editFinIso = parsearFechaFlexible(registro.fecha_final)?.iso || '';

  return `
  <form id="form-editar-vacaciones" class="space-y-4" novalidate>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div>
        <label for="edit-vac-inicio" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha de inicio *</label>
        <div class="relative">
          <input id="edit-vac-inicio" type="text" value="${fechaIsoAVisual(editInicioIso)}" placeholder="dd-mm-yyyy" autocomplete="off" required class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
          <input type="hidden" id="edit-vac-inicio-iso" name="fecha_inicio" value="${editInicioIso}" />
        </div>
        <p id="edit-vac-inicio-error" class="hidden mt-1 text-xs font-medium text-red-600" role="alert"></p>
      </div>
      <div>
        <label for="edit-vac-dias" class="block text-sm font-medium text-zinc-900 mb-1.5">Días a disfrutar *</label>
        <input id="edit-vac-dias" name="dias_solicitados" type="number" min="1" step="1" inputmode="numeric" value="${registro.dias_solicitado || 1}" required class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
        <p id="edit-vac-dias-error" class="hidden mt-1 text-xs font-medium text-red-600" role="alert"></p>
      </div>
    </div>
    <div>
      <label for="edit-vac-fin" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha final (último día hábil) *</label>
      <input id="edit-vac-fin" type="text" value="${fechaIsoAVisual(editFinIso)}" placeholder="dd-mm-yyyy" readonly required class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2.5 text-sm font-semibold text-zinc-700 outline-none cursor-not-allowed" />
      <input type="hidden" id="edit-vac-fin-iso" name="fecha_final" value="${editFinIso}" />
      <p id="edit-vac-reincorporacion" class="hidden mt-1.5 text-xs font-medium text-zinc-500"></p>
    </div>
    <label class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 cursor-pointer">
      <span class="text-sm font-medium text-zinc-900">Requiere firma de Gerencia</span>
      <input type="checkbox" name="gerencia" ${registro.requiere_gerencia ? 'checked' : ''} class="h-4 w-4 rounded border-slate-300 text-marca focus:ring-marca-500" />
    </label>
  </form>
`;
};

const renderFormularioSuspenderVacaciones = (registro) => {
  const fechaInicio = String(registro.fecha_inicio || '').slice(0, 10);
  const fechaFinal = String(registro.fecha_final || '').slice(0, 10);
  const totalDias = Number(registro.dias_solicitado || 0);

  return `
  <form id="form-suspender-vacaciones" class="space-y-4" novalidate>
    ${crearAlertaConfirmacion({
      variante: 'yellow',
      icono: 'pause-circle',
      mensajeHtml: `Al suspender la solicitud de <strong>${registro.nombre_completo}</strong> (${totalDias} días hábiles del <strong>${formatearFecha(fechaInicio)}</strong> al <strong>${formatearFecha(fechaFinal)}</strong>), los días no disfrutados se reintegrarán automáticamente a sus períodos abiertos.`,
    })}
    <div>
      <label for="susp-fecha-corte" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha hasta donde disfrutó</label>
      <div class="relative">
        <input id="susp-fecha-corte" type="text" value="${fechaIsoAVisual(fechaInicio)}" placeholder="dd-mm-yyyy" autocomplete="off" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
        <input type="hidden" id="susp-fecha-corte-iso" name="fecha_corte" value="${fechaInicio}" />
      </div>
      <p id="susp-fecha-corte-error" class="mt-1 text-xs text-red-600 hidden"></p>
      <p class="mt-1 text-xs text-zinc-500">Rango válido: ${formatearFecha(fechaInicio)} al ${formatearFecha(fechaFinal)}. Selecciona la fecha de inicio (${formatearFecha(fechaInicio)}) si no llegó a disfrutar días.</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="susp-dias-disfrutados" class="block text-sm font-medium text-zinc-900 mb-1.5">Días disfrutados</label>
        <input id="susp-dias-disfrutados" name="dias_disfrutados" type="text" readonly value="0" class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2.5 text-sm font-semibold text-zinc-700 cursor-not-allowed outline-none" />
      </div>
      <div>
        <label for="susp-dias-reintegrar" class="block text-sm font-medium text-zinc-900 mb-1.5">Días a reintegrar</label>
        <input id="susp-dias-reintegrar" name="dias_reintegrar" type="text" readonly value="${totalDias}" class="w-full rounded-lg border border-slate-300 bg-slate-100 px-3 py-2.5 text-sm font-semibold text-emerald-700 cursor-not-allowed outline-none" />
      </div>
    </div>
    <div>
      <label for="susp-observacion" class="block text-sm font-medium text-zinc-900 mb-1.5">Motivo de suspensión</label>
      <input id="susp-observacion" name="observacion" type="text" maxlength="200" placeholder="Ej: Necesidad del servicio / Reposo médico" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
    </div>
  </form>
`;
};

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
  const inputInicioIso = document.getElementById('fecha_inicio_iso');
  const inputDias = document.getElementById('dias_solicitado');
  const inputFin = document.getElementById('fecha_final');
  const inputFinIso = document.getElementById('fecha_final_iso');
  const nodoReincorporacion = document.getElementById('texto-reincorporacion-vacaciones');
  const nodoErrorInicio = document.getElementById('fecha_inicio-error');
  const nodoErrorDias = document.getElementById('dias_solicitado-error');
  const filtroEstatus = document.getElementById('filtro-estatus-vacaciones');

  let saldoActualEmpleado = null;
  let calEditInicioActivo = null;
  let recalcularEditActivo = null;

  if (inputDias) {
    inputDias.min = '1';
    inputDias.step = '1';
    inputDias.inputMode = 'numeric';
  }
  if (inputFin) {
    inputFin.readOnly = true;
    inputFin.classList.remove('bg-slate-50');
    inputFin.classList.add('bg-slate-100', 'font-semibold', 'text-zinc-700', 'cursor-not-allowed');
  }

  const actualizarResumenCards = (resumen) => {
    if (!resumen) return;
    const aprobadasEl = document.getElementById('resumen-vacaciones-aprobadas');
    const pendientesEl = document.getElementById('resumen-vacaciones-pendientes');
    if (aprobadasEl && resumen.aprobadas !== undefined) aprobadasEl.textContent = String(resumen.aprobadas);
    if (pendientesEl) {
      pendientesEl.textContent = `${resumen.pendientes ?? 0} pendientes · ${resumen.suspendidas ?? 0} suspendidas por reposo`;
    }
  };

  const recalcularPrincipal = () =>
    actualizarCalculoVacaciones({
      inputInicio,
      inputInicioIso,
      inputDias,
      inputFin,
      inputFinIso,
      nodoReincorporacion,
      nodoErrorInicio,
      nodoErrorDias,
      saldoMaximo: saldoActualEmpleado,
    });

  const consultarSaldoEmpleado = async (empleadoId) => {
    saldoActualEmpleado = null;
    if (inputDias) inputDias.removeAttribute('max');

    if (!empleadoId) {
      tarjetaSaldo?.classList.add('hidden');
      recalcularPrincipal();
      return;
    }

    const res = await Solicitud.get(`/api/vacaciones/saldo/${empleadoId}`, {
      claveCancelacion: 'saldo-vacaciones',
    });

    if (res.aborted) return;
    if (!res.ok || !res.data?.ok) {
      tarjetaSaldo?.classList.add('hidden');
      recalcularPrincipal();
      return;
    }

    const saldo = res.data.data || {};
    const diasPendientes = Number(saldo.dias_pendientes || 0);
    saldoActualEmpleado = diasPendientes;
    const periodosTexto = saldo.resumen_periodos || 'Sin períodos abiertos';

    if (inputDias && diasPendientes > 0) {
      inputDias.max = String(diasPendientes);
    }
    if (nodoDiasPendientes) {
      nodoDiasPendientes.textContent = `${diasPendientes} días`;
      nodoDiasPendientes.dataset.dias = String(diasPendientes);
    }
    if (nodoPeriodosActivos) nodoPeriodosActivos.textContent = periodosTexto;
    tarjetaSaldo?.classList.remove('hidden');
    recalcularPrincipal();
  };

  selectEmpleado?.addEventListener('change', () => consultarSaldoEmpleado(selectEmpleado.value));

  const ctrlInicio = inicializarCampoFechaDobleFormato(inputInicio, {
    inputIso: inputInicioIso,
    soloHabiles: true,
    alSeleccionar: recalcularPrincipal,
  });
  const calInicio = ctrlInicio?.cal || null;

  inputInicio?.addEventListener('input', recalcularPrincipal);
  inputInicio?.addEventListener('change', recalcularPrincipal);
  inputDias?.addEventListener('input', recalcularPrincipal);
  inputDias?.addEventListener('change', recalcularPrincipal);

  const cargarFeriadosLocales = async () => {
    const anioActual = new Date().getFullYear();
    const res = await Solicitud.get('/api/vacaciones/feriados', {
      params: { anio_inicio: anioActual - 1, anio_fin: anioActual + 2 },
      claveCancelacion: 'feriados-vacaciones',
    });

    if (!res.ok || !res.data?.ok) return;
    const fechas = Array.isArray(res.data.data?.fechas) ? res.data.data.fechas : [];
    if (fechas.length === 0) return;

    establecerFeriadosRemotos(fechas);
    refrescarReglasCalendario(calInicio);
    if (calEditInicioActivo) {
      refrescarReglasCalendario(calEditInicioActivo);
    }
    recalcularPrincipal();
    recalcularEditActivo?.();
  };

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
    const fechaInicioSol = String(registro.fecha_inicio || '').slice(0, 10);
    const fechaFinalSol = String(registro.fecha_final || '').slice(0, 10);
    const totalSolicitado = Math.max(0, Number(registro.dias_solicitado || 0));

    modalAccion.abrir({
      titulo: `Suspender vacaciones · #${id}`,
      subtitulo: `${registro.nombre_completo} · ${formatearFecha(fechaInicioSol)} al ${formatearFecha(fechaFinalSol)}`,
      contenidoHtml: renderFormularioSuspenderVacaciones(registro),
      mostrarConfirmar: true,
      textoConfirmar: 'Suspender y reintegrar',
      varianteConfirmar: 'marca',
      alAbrir: (dlg) => {
        const inputCorte = dlg.querySelector('#susp-fecha-corte');
        const inputCorteIso = dlg.querySelector('#susp-fecha-corte-iso');
        const inputDisfrutados = dlg.querySelector('#susp-dias-disfrutados');
        const inputReintegrar = dlg.querySelector('#susp-dias-reintegrar');
        const nodoErrorCorte = dlg.querySelector('#susp-fecha-corte-error');

        let ctrlCorte = null;

        const recalcularSuspension = () => {
          const fechaCorte = ctrlCorte?.obtenerIso() || fechaVisualAIso(inputCorteIso?.value || inputCorte?.value);
          toggleMensajeCampo(nodoErrorCorte, '');

          if (!esFechaIsoValida(fechaCorte) || fechaCorte < fechaInicioSol || fechaCorte > fechaFinalSol) {
            if (inputDisfrutados) inputDisfrutados.value = '0';
            if (inputReintegrar) inputReintegrar.value = String(totalSolicitado);
            toggleMensajeCampo(
              nodoErrorCorte,
              `La fecha debe estar entre ${formatearFecha(fechaInicioSol)} y ${formatearFecha(fechaFinalSol)}.`
            );
            return false;
          }

          const disfrutados = Math.max(
            0,
            Math.min(totalSolicitado, calcularDiasDisfrutadosPorCorte(fechaInicioSol, fechaCorte))
          );
          const reintegrar = Math.max(0, totalSolicitado - disfrutados);

          if (inputDisfrutados) inputDisfrutados.value = String(disfrutados);
          if (inputReintegrar) inputReintegrar.value = String(reintegrar);
          return true;
        };

        ctrlCorte = inicializarCampoFechaDobleFormato(inputCorte, {
          inputIso: inputCorteIso,
          soloHabiles: true,
          minDate: fechaInicioSol,
          maxDate: fechaFinalSol,
          alSeleccionar: recalcularSuspension,
        });

        inputCorte?.addEventListener('input', recalcularSuspension);
        inputCorte?.addEventListener('change', recalcularSuspension);
        recalcularSuspension();
      },
      alConfirmar: async (dlg) => {
        const form = dlg.querySelector('#form-suspender-vacaciones');
        const fechaCorte = String(form?.elements?.fecha_corte?.value || '').trim();
        const observacion = form?.elements?.observacion?.value?.trim() || '';

        if (!esFechaIsoValida(fechaCorte) || fechaCorte < fechaInicioSol || fechaCorte > fechaFinalSol) {
          notificarErrorFormulario(
            form,
            `La fecha hasta donde disfrutó debe estar entre ${formatearFecha(fechaInicioSol)} y ${formatearFecha(fechaFinalSol)}.`
          );
          return false;
        }

        const disfrutados = Math.max(
          0,
          Math.min(totalSolicitado, calcularDiasDisfrutadosPorCorte(fechaInicioSol, fechaCorte))
        );

        const res = await Solicitud.post(`/api/vacaciones/${id}/estatus`, {
          estatus: 6,
          fecha_corte: fechaCorte,
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
    let saldoEditMaximo = null;

    modalAccion.abrir({
      titulo: `Editar solicitud #${id}`,
      subtitulo: `${registro.nombre_completo} · ${registro.cedula}`,
      contenidoHtml: renderFormularioEdicionVacaciones(registro),
      mostrarConfirmar: true,
      textoConfirmar: 'Guardar cambios',
      varianteConfirmar: 'marca',
      alConfirmar: async (dlg) => {
        const form = dlg.querySelector('#form-editar-vacaciones');
        const editInicio = dlg.querySelector('#edit-vac-inicio');
        const editInicioIso = dlg.querySelector('#edit-vac-inicio-iso');
        const editDias = form?.elements?.dias_solicitados;
        const editFin = dlg.querySelector('#edit-vac-fin');
        const editFinIso = dlg.querySelector('#edit-vac-fin-iso');
        const editReinc = dlg.querySelector('#edit-vac-reincorporacion');
        const editErrIni = dlg.querySelector('#edit-vac-inicio-error');
        const editErrDias = dlg.querySelector('#edit-vac-dias-error');

        const calculo = actualizarCalculoVacaciones({
          inputInicio: editInicio,
          inputInicioIso: editInicioIso,
          inputDias: editDias,
          inputFin: editFin,
          inputFinIso: editFinIso,
          nodoReincorporacion: editReinc,
          nodoErrorInicio: editErrIni,
          nodoErrorDias: editErrDias,
          saldoMaximo: saldoEditMaximo,
        });

        if (!calculo.valido) {
          const msg =
            editErrIni?.textContent ||
            editErrDias?.textContent ||
            'Verifica la fecha de inicio hábil y la cantidad de días a disfrutar.';
          notificarErrorFormulario(form, msg);
          return false;
        }

        const gerencia = Boolean(form?.elements?.gerencia?.checked);

        const res = await Solicitud.put(`/api/vacaciones/${id}`, {
          fecha_inicio: calculo.inicio,
          fecha_final: calculo.fechaFinal,
          dias_solicitados: calculo.dias,
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
      const editInicioIso = modalAccion.dialogo.querySelector('#edit-vac-inicio-iso');
      const editDias = modalAccion.dialogo.querySelector('#edit-vac-dias');
      const editFin = modalAccion.dialogo.querySelector('#edit-vac-fin');
      const editFinIso = modalAccion.dialogo.querySelector('#edit-vac-fin-iso');
      const editReinc = modalAccion.dialogo.querySelector('#edit-vac-reincorporacion');
      const editErrIni = modalAccion.dialogo.querySelector('#edit-vac-inicio-error');
      const editErrDias = modalAccion.dialogo.querySelector('#edit-vac-dias-error');

      const recalcularEdit = () =>
        actualizarCalculoVacaciones({
          inputInicio: editInicio,
          inputInicioIso: editInicioIso,
          inputDias: editDias,
          inputFin: editFin,
          inputFinIso: editFinIso,
          nodoReincorporacion: editReinc,
          nodoErrorInicio: editErrIni,
          nodoErrorDias: editErrDias,
          saldoMaximo: saldoEditMaximo,
        });

      recalcularEditActivo = recalcularEdit;
      const ctrlEditInicio = inicializarCampoFechaDobleFormato(editInicio, {
        inputIso: editInicioIso,
        soloHabiles: true,
        alSeleccionar: recalcularEdit,
      });
      calEditInicioActivo = ctrlEditInicio?.cal || null;

      editInicio?.addEventListener('input', recalcularEdit);
      editInicio?.addEventListener('change', recalcularEdit);
      editDias?.addEventListener('input', recalcularEdit);
      editDias?.addEventListener('change', recalcularEdit);

      recalcularEdit();

      if (registro.empleado_id) {
        Solicitud.get(`/api/vacaciones/saldo/${registro.empleado_id}`, {
          claveCancelacion: 'saldo-vacaciones-editar',
        }).then((resSaldo) => {
          if (resSaldo.aborted || !resSaldo.ok || !resSaldo.data?.ok) return;
          const disponibles = Number(resSaldo.data.data?.dias_pendientes || 0);
          const estatusNum = Number(registro.estatus);
          const yaDescontada = estatusNum === 2 || estatusNum === 4;
          saldoEditMaximo = yaDescontada
            ? disponibles + Number(registro.dias_solicitado || 0)
            : disponibles;
          if (editDias && saldoEditMaximo > 0) {
            editDias.max = String(saldoEditMaximo);
          }
          recalcularEdit();
        });
      }
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
    const gerencia = Boolean(document.getElementById('gerencia')?.checked);

    if (!empleadoId) {
      notificarErrorFormulario(formulario, 'Debes seleccionar el trabajador solicitante.');
      return;
    }

    const calculo = recalcularPrincipal();
    if (!calculo.inicio) {
      notificarErrorFormulario(formulario, 'Selecciona una fecha de inicio válida.');
      return;
    }

    if (!esFechaIsoValida(calculo.inicio) || !esDiaHabilInces(calculo.inicio)) {
      notificarErrorFormulario(
        formulario,
        'La fecha de inicio debe ser un día hábil laborable (lunes a viernes, no feriado).'
      );
      return;
    }

    const diasRaw = String(inputDias?.value || '').trim();
    if (!/^\d+$/.test(diasRaw) || Number(diasRaw) < 1) {
      notificarErrorFormulario(formulario, 'Ingresa una cantidad entera de días mayor o igual a 1.');
      return;
    }

    const dias = Number(diasRaw);
    if (saldoActualEmpleado !== null && saldoActualEmpleado < dias) {
      notificarErrorFormulario(
        formulario,
        `El trabajador dispone de ${saldoActualEmpleado} días pendientes y está solicitando ${dias} días hábiles.`
      );
      return;
    }

    if (!calculo.valido || !calculo.fechaFinal) {
      notificarErrorFormulario(formulario, 'No se pudo calcular la fecha final de vacaciones.');
      return;
    }

    enviando = true;
    const btnSubmit = formulario.querySelector('button[type="submit"]');
    if (btnSubmit) btnSubmit.disabled = true;

    try {
      const res = await Solicitud.post('/api/vacaciones', {
        empleado_id: empleadoId,
        dias_solicitados: dias,
        fecha_inicio: calculo.inicio,
        fecha_final: calculo.fechaFinal,
        gerencia,
      });

      if (!res.ok || !res.data?.ok) {
        notificarErrorFormulario(formulario, res.message || 'No se pudo registrar la solicitud de vacaciones.');
        return;
      }

      notificarExitoFormulario(formulario, res.data.message || 'Solicitud de vacaciones registrada correctamente.');
      formulario.reset();
      ctrlInicio?.limpiar();
      if (inputFin) inputFin.value = '';
      if (inputFinIso) inputFinIso.value = '';
      if (inputDias) inputDias.removeAttribute('max');
      if (nodoReincorporacion) {
        nodoReincorporacion.textContent = '';
        nodoReincorporacion.classList.add('hidden');
      }
      toggleMensajeCampo(nodoErrorInicio, '');
      toggleMensajeCampo(nodoErrorDias, '');
      limpiarAlertaFormulario(formulario);
      tarjetaSaldo?.classList.add('hidden');
      saldoActualEmpleado = null;
      await cargarSolicitudes();
    } finally {
      enviando = false;
      if (btnSubmit) btnSubmit.disabled = false;
    }
  });

  cargarFeriadosLocales();
  cargarSolicitudes();
});
