import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import {
  limpiarAlertaFormulario,
  notificarErrorFormulario,
  notificarExitoFormulario,
} from '/js/components/alerta/alerta.js';
import { crearBadge } from '/js/components/badge/crearBadge.js';
// import { inicializarCalendario } from '/js/components/calendario/calendario.js';
import { crearListaDetalle, Modal } from '/js/components/modal/Modal.js';
import { inicializarSelectsPersonalizados } from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import { columnasTablaAsistencia } from '/js/templates/tabla_asistencia.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();

  const modalDetalle = new Modal('#modal-detalle-asistencia');
  const formulario = document.getElementById('formulario-asistencia');
  const selectEmpleado = document.getElementById('empleado_id');
  const inputFecha = document.getElementById('fecha_asistencia');
  const inputHora = document.getElementById('hora_asistencia');
  const selectTurno = document.getElementById('turno');
  const btnEntrada = document.getElementById('btn-marcar-entrada');
  const btnSalida = document.getElementById('btn-marcar-salida');

  const tarjetaEstado = document.getElementById('tarjeta-estado-empleado-asistencia');
  const nodoNombre = document.getElementById('asis-emp-nombre');
  const nodoBadge = document.getElementById('asis-emp-badge');
  const nodoMeta = document.getElementById('asis-emp-meta');
  const nodoAviso = document.getElementById('asis-emp-aviso');

  const inputFiltroFecha = document.getElementById('filtro-fecha-asistencia');
  const btnLimpiarFiltro = document.getElementById('btn-limpiar-filtro-asistencia');

  let estadoActualTrabajador = null;
  let procesando = false;

  // inicializarCalendario(inputFecha);
  // const calFiltro = inicializarCalendario(inputFiltroFecha, {
  //   placeholder: 'Filtrar por fecha...',
  //   alSeleccionar: () => cargarAsistencias(),
  // });

  const actualizarResumenCards = (resumen) => {
    if (!resumen) return;
    const presEl = document.getElementById('resumen-asistencia-presentes');
    const jornEl = document.getElementById('resumen-asistencia-jornada');
    const repEl = document.getElementById('resumen-asistencia-reposos');
    const nomEl = document.getElementById('resumen-asistencia-nomina');

    if (presEl) presEl.textContent = String(resumen.presentes_hoy ?? 0);
    if (jornEl) {
      jornEl.textContent = `${resumen.en_jornada_hoy ?? 0} en jornada · ${resumen.cerradas_hoy ?? 0} salidas marcadas`;
    }
    if (repEl) repEl.textContent = String(resumen.en_reposo_hoy ?? 0);
    if (nomEl) {
      nomEl.textContent = `Bloqueados para marcar entrada · ${resumen.nomina_activa ?? 0} activos en nómina`;
    }
  };

  const consultarEstadoEmpleado = async () => {
    estadoActualTrabajador = null;
    limpiarAlertaFormulario(formulario);

    const empleadoId = Number(selectEmpleado?.value || 0);
    const fecha = inputFecha?.value || '';

    if (!empleadoId || !fecha) {
      tarjetaEstado?.classList.add('hidden');
      if (btnEntrada) btnEntrada.disabled = false;
      if (btnSalida) btnSalida.disabled = false;
      return;
    }

    const res = await Solicitud.get(`/api/asistencia/empleado/${empleadoId}`, {
      params: { fecha },
      claveCancelacion: 'estado-asistencia-empleado',
    });

    if (res.aborted || !res.ok || !res.data?.ok) {
      tarjetaEstado?.classList.add('hidden');
      return;
    }

    const info = res.data.data || {};
    estadoActualTrabajador = info;

    if (nodoNombre) nodoNombre.textContent = info.nombre_completo || 'Trabajador';
    if (nodoMeta) {
      nodoMeta.textContent = [info.codigo_empleado, info.cedula, info.cargo, info.departamento].filter(Boolean).join(' · ');
    }

    if (info.en_reposo && info.reposo) {
      tarjetaEstado.className = 'rounded-2xl border border-red-200 bg-red-50 p-4 space-y-2 text-xs';
      if (nodoBadge) nodoBadge.innerHTML = crearBadge('En Reposo Médico', 'red');
      if (nodoAviso) {
        nodoAviso.className = 'font-semibold text-red-800';
        nodoAviso.textContent = `Bloqueado hasta el ${formatearFecha(info.reposo.fecha_fin)} (${info.reposo.tipo_reposo}). Solo podrá marcar asistencia después de esa fecha.`;
      }
      if (btnEntrada) btnEntrada.disabled = true;
      if (btnSalida) btnSalida.disabled = true;
      return;
    }

    tarjetaEstado.className = 'rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-2 text-xs';
    const asis = info.asistencia_hoy;

    if (asis && !asis.hora_salida) {
      if (nodoBadge) nodoBadge.innerHTML = crearBadge('En jornada', 'yellow');
      if (nodoAviso) {
        nodoAviso.className = 'font-medium text-zinc-700';
        nodoAviso.textContent = `Entrada marcada a las ${asis.hora_entrada} (${asis.turno}). Listo para marcar salida.`;
      }
      if (btnEntrada) btnEntrada.disabled = true;
      if (btnSalida) btnSalida.disabled = false;
    } else if (asis && asis.hora_salida) {
      if (nodoBadge) nodoBadge.innerHTML = crearBadge('Jornada completada', 'green');
      if (nodoAviso) {
        nodoAviso.className = 'font-medium text-green-800';
        nodoAviso.textContent = `Entrada: ${asis.hora_entrada} · Salida: ${asis.hora_salida}.`;
      }
      if (btnEntrada) btnEntrada.disabled = false;
      if (btnSalida) btnSalida.disabled = true;
    } else {
      if (nodoBadge) nodoBadge.innerHTML = crearBadge('Habilitado', 'green');
      if (nodoAviso) {
        nodoAviso.className = 'font-medium text-zinc-600';
        nodoAviso.textContent = 'Sin marcaje en la fecha seleccionada. Habilitado para registrar entrada.';
      }
      if (btnEntrada) btnEntrada.disabled = false;
      if (btnSalida) btnSalida.disabled = true;
    }
  };

  selectEmpleado?.addEventListener('change', consultarEstadoEmpleado);
  inputFecha?.addEventListener('change', consultarEstadoEmpleado);

  const verDetalleJornada = (registro) => {
    modalDetalle.abrir({
      titulo: `${registro.nombre_completo || 'Trabajador'}`,
      subtitulo: `${registro.codigo_empleado || ''} · ${registro.cedula || ''}`,
      contenidoHtml: crearListaDetalle([
        { etiqueta: 'Cargo', valorHtml: registro.cargo || '—' },
        { etiqueta: 'Departamento', valorHtml: registro.departamento || '—' },
        { etiqueta: 'Fecha de jornada', valorHtml: formatearFecha(registro.fecha) },
        { etiqueta: 'Turno', valorHtml: crearBadge(registro.turno || 'Diurno', 'sky') },
        { etiqueta: 'Hora de entrada', valorHtml: `<span class="font-semibold">${registro.hora_entrada || '—'}</span>` },
        { etiqueta: 'Hora de salida', valorHtml: registro.hora_salida || 'Pendiente por marcar' },
      ]),
    });
  };

  const tabla = new GestorDataTable('#tabla-asistencia', {
    datos: [],
    ordenInicial: [[4, 'desc']],
    columnas: columnasTablaAsistencia(),
    alAccionar: (accion, registro) => {
      if (accion === 'mostrar') verDetalleJornada(registro);
    },
  });

  const cargarAsistencias = async () => {
    const params = {};
    if (inputFiltroFecha?.value) params.fecha = inputFiltroFecha.value;

    const res = await Solicitud.get('/api/asistencia', {
      params,
      claveCancelacion: 'listar-asistencia',
    });

    if (res.aborted) return;
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudieron consultar las asistencias.');
      return;
    }

    const lista = Array.isArray(res.data.data) ? res.data.data : [];
    actualizarResumenCards(res.data.resumen);
    tabla.actualizarDatos(lista);
  };

  btnLimpiarFiltro?.addEventListener('click', () => {
    if (inputFiltroFecha) inputFiltroFecha.value = '';
    calFiltro?.set({ selectedDates: [] }, { dates: true });
    cargarAsistencias();
  });

  const ejecutarMarcaje = async (tipoMarcaje) => {
    if (procesando) return;

    const empleadoId = Number(selectEmpleado?.value || 0);
    const fecha = inputFecha?.value || '';
    const hora = inputHora?.value ? `${inputHora.value}:00` : '';
    const turno = selectTurno?.value || 'Diurno';

    if (!empleadoId || !fecha) {
      notificarErrorFormulario(formulario, 'Selecciona el trabajador y la fecha de la jornada.');
      return;
    }

    if (tipoMarcaje === 'entrada' && estadoActualTrabajador?.en_reposo) {
      const finRep = formatearFecha(estadoActualTrabajador.reposo?.fecha_fin);
      notificarErrorFormulario(
        formulario,
        `El trabajador debe permanecer de reposo médico hasta el ${finRep}. Solo podrá registrar asistencia después de esa fecha.`
      );
      return;
    }

    procesando = true;
    if (btnEntrada) btnEntrada.disabled = true;
    if (btnSalida) btnSalida.disabled = true;

    try {
      const url = tipoMarcaje === 'entrada' ? '/api/asistencia/entrada' : '/api/asistencia/salida';
      const res = await Solicitud.post(url, {
        empleado_id: empleadoId,
        fecha,
        hora,
        turno,
      });

      if (!res.ok || !res.data?.ok) {
        notificarErrorFormulario(formulario, res.message || 'No se pudo registrar el marcaje de asistencia.');
        return;
      }

      notificarExitoFormulario(formulario, res.data.message || 'Asistencia registrada correctamente.');
      await Promise.all([cargarAsistencias(), consultarEstadoEmpleado()]);
    } finally {
      procesando = false;
      if (!estadoActualTrabajador) {
        if (btnEntrada) btnEntrada.disabled = false;
        if (btnSalida) btnSalida.disabled = false;
      }
    }
  };

  btnEntrada?.addEventListener('click', () => ejecutarMarcaje('entrada'));
  btnSalida?.addEventListener('click', () => ejecutarMarcaje('salida'));

  cargarAsistencias();
});
