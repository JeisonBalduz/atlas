import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { crearBadge } from '/js/components/badge/crearBadge.js';
import { Modal } from '/js/components/modal/Modal.js';
import { inicializarSelectsPersonalizados } from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import {
  columnasTablaVacaciones,
  varianteEstatusVacaciones,
} from '/js/templates/tabla_vacaciones.js';
import { calcularDiasEntreFechas } from '/js/utils/calcularDiasEntreFechas.js';
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

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();

  const modalDetalle = new Modal('#modal-detalle-vacaciones');
  const selectEmpleado = document.getElementById('empleado_id');
  const tarjetaSaldo = document.getElementById('tarjeta-saldo-vacaciones');
  const nodoDiasPendientes = document.getElementById('saldo-dias-pendientes');
  const nodoPeriodosActivos = document.getElementById('saldo-periodos-activos');
  const inputInicio = document.getElementById('fecha_inicio');
  const inputFin = document.getElementById('fecha_final');
  const inputDias = document.getElementById('dias_solicitado');
  const filtroEstatus = document.getElementById('filtro-estatus-vacaciones');

  const consultarSaldoEmpleado = async (empleadoId) => {
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
    const periodosTexto = saldo.resumen_periodos || 'Sin períodos abiertos';

    if (nodoDiasPendientes) nodoDiasPendientes.textContent = `${diasPendientes} días`;
    if (nodoPeriodosActivos) nodoPeriodosActivos.textContent = periodosTexto;
    tarjetaSaldo?.classList.remove('hidden');
  };

  selectEmpleado?.addEventListener('change', () => consultarSaldoEmpleado(selectEmpleado.value));

  const actualizarDias = () => {
    const dias = calcularDiasEntreFechas(inputInicio?.value, inputFin?.value);
    if (inputDias) inputDias.value = dias > 0 ? String(dias) : '';
  };
  inputInicio?.addEventListener('change', actualizarDias);
  inputFin?.addEventListener('change', actualizarDias);

  const verDetalleSolicitud = async (registro) => {
    const id = registro.solicitud_vacaciones_id;
    modalDetalle.abrir({
      titulo: `Solicitud #${id} · ${registro.nombre_completo || 'Trabajador'}`,
      subtitulo: `${formatearFecha(registro.fecha_inicio)} al ${formatearFecha(registro.fecha_final)} (${registro.dias_solicitado} días solicitados)`,
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
      subtitulo: `${formatearFecha(registro.fecha_inicio)} al ${formatearFecha(registro.fecha_final)} (${registro.dias_solicitado} días solicitados)`,
      contenidoHtml: renderDetalleVacaciones(detalle),
      mostrarConfirmar: esPendiente,
      textoConfirmar: registro.requiere_gerencia ? 'Aprobar por Gerencia' : 'Aprobar por RRHH',
      varianteConfirmar: 'green',
      alConfirmar: esPendiente
        ? async () => {
            const nuevoEstatus = registro.requiere_gerencia ? 4 : 2;
            const resAprob = await Solicitud.post(`/api/vacaciones/${id}/estatus`, { estatus: nuevoEstatus });
            if (!resAprob.ok || !resAprob.data?.ok) {
              mostrarToast('error', resAprob.message || 'No se pudo aprobar la solicitud.');
              return false;
            }
            mostrarToast('success', resAprob.data.message || 'Solicitud de vacaciones aprobada.');
            await cargarSolicitudes();
            if (selectEmpleado?.value) consultarSaldoEmpleado(selectEmpleado.value);
            return true;
          }
        : null,
    });
  };

  const tabla = new GestorDataTable('#tabla-vacaciones', {
    datos: [],
    ordenInicial: [[3, 'desc']],
    columnas: columnasTablaVacaciones(),
    alAccionar: (accion, registro) => {
      if (accion === 'mostrar') verDetalleSolicitud(registro);
    },
  });

  const cargarSolicitudes = async () => {
    const res = await Solicitud.get('/api/vacaciones');
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudieron consultar las solicitudes de vacaciones.');
      return;
    }

    const lista = Array.isArray(res.data.data) ? res.data.data : [];
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
    const dias = Number(inputDias?.value || 0);
    const gerencia = Boolean(document.getElementById('gerencia')?.checked);

    if (!empleadoId || !inputInicio?.value || !inputFin?.value || dias <= 0) {
      mostrarToast('error', 'Selecciona el empleado y un rango de fechas válido.');
      return;
    }

    enviando = true;
    const btnSubmit = formulario.querySelector('button[type="submit"]');
    if (btnSubmit) btnSubmit.disabled = true;

    try {
      const res = await Solicitud.post('/api/vacaciones', {
        empleado_id: empleadoId,
        dias_solicitados: dias,
        fecha_inicio: inputInicio.value,
        fecha_final: inputFin.value,
        gerencia,
      });

      if (!res.ok || !res.data?.ok) {
        mostrarToast('error', res.message || 'No se pudo registrar la solicitud de vacaciones.');
        return;
      }

      mostrarToast('success', res.data.message || 'Solicitud de vacaciones registrada.');
      formulario.reset();
      tarjetaSaldo?.classList.add('hidden');
      await cargarSolicitudes();
    } finally {
      enviando = false;
      if (btnSubmit) btnSubmit.disabled = false;
    }
  });

  cargarSolicitudes();
});
