/**
 * registro_vacaciones.js
 * Formulario de solicitud de vacaciones y tabla con DataTables 3.1.5.
 */

import { ControladorModal } from '/js/class/ControladorModal.js';
import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { inicializarSelectsPersonalizados } from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';
import { crearBadge, crearBotonAccion } from '/js/templates/template_ui.js';
import { calcularDiasEntreFechas, escaparHtml, formatearFecha } from '/js/utils/dom.js';

let solicitudesIniciales = [
  {
    solicitud_vacaciones_id: 1,
    empleado: 'Carlos Eduardo Mendoza Ríos',
    cedula: 'V-21456789',
    dias_solicitado: 15,
    fecha_inicio: '2026-12-01',
    fecha_final: '2026-12-15',
    estatus_texto: 'Aprobada por Talento Humano',
    periodos_descontados: [{ periodo: 2024, dia_descontados: 15 }],
    historial: [
      { fecha_registro: '2026-11-10 09:15', estatus: 'Pendiente', observacion: 'Solicitud cargada por el trabajador.' },
      { fecha_registro: '2026-11-12 14:30', estatus: 'Aprobada por Talento Humano', observacion: 'Saldo verificado en período 2024.' },
    ],
  },
  {
    solicitud_vacaciones_id: 2,
    empleado: 'María Fernanda López Silva',
    cedula: 'V-19876543',
    dias_solicitado: 10,
    fecha_inicio: '2026-11-24',
    fecha_final: '2026-12-03',
    estatus_texto: 'Pendiente',
    periodos_descontados: [{ periodo: 2025, dia_descontados: 10 }],
    historial: [
      { fecha_registro: '2026-11-18 10:00', estatus: 'Pendiente', observacion: 'En espera de revisión por Talento Humano.' },
    ],
  },
  {
    solicitud_vacaciones_id: 3,
    empleado: 'José Gregorio Ramírez Pérez',
    cedula: 'V-17654321',
    dias_solicitado: 20,
    fecha_inicio: '2026-10-05',
    fecha_final: '2026-10-24',
    estatus_texto: 'Reposo',
    periodos_descontados: [
      { periodo: 2023, dia_descontados: 15 },
      { periodo: 2024, dia_descontados: 5 },
    ],
    historial: [
      { fecha_registro: '2026-09-20 08:40', estatus: 'Aprobada por Gerencia', observacion: 'Aprobada con firma de gerencia.' },
      { fecha_registro: '2026-10-14 11:20', estatus: 'Reposo', observacion: 'Interrumpida por reposo médico (4 días afectados).' },
    ],
  },
  {
    solicitud_vacaciones_id: 4,
    empleado: 'Ana Gabriela Torres Morales',
    cedula: 'V-24112233',
    dias_solicitado: 12,
    fecha_inicio: '2026-08-01',
    fecha_final: '2026-08-12',
    estatus_texto: 'Terminada',
    periodos_descontados: [{ periodo: 2025, dia_descontados: 12 }],
    historial: [
      { fecha_registro: '2026-07-15 09:00', estatus: 'Aprobada por Talento Humano', observacion: 'Aprobada sin observaciones.' },
      { fecha_registro: '2026-08-13 08:00', estatus: 'Terminada', observacion: 'Trabajadora reincorporada.' },
    ],
  },
];

const varianteEstatusVacaciones = (estatus) => {
  const mapa = {
    Pendiente: 'yellow',
    'Aprobada por Talento Humano': 'green',
    'Aprobada por Gerencia': 'green',
    'Negada por Talento Humano': 'red',
    Reposo: 'sky',
    Terminada: 'marca',
  };
  return mapa[estatus] || 'neutro';
};

const renderDetalleVacaciones = (registro) => {
  const filasPeriodos = (registro.periodos_descontados || [])
    .map(
      (p) => `
        <li class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2 text-sm">
          <span class="font-medium text-zinc-900">Período ${escaparHtml(p.periodo)}</span>
          <span class="font-semibold text-marca-700">${escaparHtml(p.dia_descontados)} días descontados</span>
        </li>
      `
    )
    .join('');

  const filasHistorial = (registro.historial || [])
    .map(
      (h) => `
        <li class="rounded-lg border border-slate-200 bg-white p-3 text-sm">
          <div class="flex flex-wrap items-center justify-between gap-2">
            ${crearBadge(h.estatus, varianteEstatusVacaciones(h.estatus))}
            <span class="text-xs text-zinc-500">${escaparHtml(h.fecha_registro)}</span>
          </div>
          <p class="mt-1.5 text-xs text-zinc-700">${escaparHtml(h.observacion)}</p>
        </li>
      `
    )
    .join('');

  return `
    <div class="space-y-5">
      <section>
        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500">Períodos descontados</h3>
        <ul class="space-y-2">${filasPeriodos || '<li class="text-xs text-zinc-500">Sin desglose registrado.</li>'}</ul>
      </section>
      <section>
        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wider text-zinc-500">Historial de movimientos</h3>
        <ul class="space-y-2">${filasHistorial || '<li class="text-xs text-zinc-500">Sin movimientos registrados.</li>'}</ul>
      </section>
    </div>
  `;
};

const obtenerColumnasVacaciones = () => [
  { data: 'empleado', render: (v) => `<span class="font-medium text-zinc-900">${escaparHtml(v)}</span>` },
  { data: 'cedula', render: (v) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${escaparHtml(v || '—')}</span>` },
  {
    data: 'dias_solicitado',
    className: 'text-center',
    render: (v) => `<span class="font-semibold text-zinc-900">${escaparHtml(v)}</span>`,
  },
  { data: 'fecha_inicio', render: (v) => `<span class="whitespace-nowrap">${escaparHtml(formatearFecha(v))}</span>` },
  { data: 'fecha_final', render: (v) => `<span class="whitespace-nowrap">${escaparHtml(formatearFecha(v))}</span>` },
  {
    data: 'estatus_texto',
    className: 'text-center',
    render: (v) => crearBadge(v, varianteEstatusVacaciones(v)),
  },
  {
    data: null,
    orderable: false,
    searchable: false,
    className: 'text-right',
    render: (_d, _t, fila) =>
      crearBotonAccion({ accion: 'mostrar', id: fila.solicitud_vacaciones_id, etiqueta: 'Ver', icono: 'eye', variante: 'marca' }),
  },
];

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();
  const modalDetalle = new ControladorModal('#modal-detalle-vacaciones');

  const selectEmpleado = document.getElementById('empleado_id');
  const tarjetaSaldo = document.getElementById('tarjeta-saldo-vacaciones');
  const inputInicio = document.getElementById('fecha_inicio');
  const inputFin = document.getElementById('fecha_final');
  const inputDias = document.getElementById('dias_solicitado');

  selectEmpleado?.addEventListener('change', () => {
    const opcion = selectEmpleado.options[selectEmpleado.selectedIndex];
    if (!selectEmpleado.value || !opcion) {
      tarjetaSaldo?.classList.add('hidden');
      return;
    }
    document.getElementById('saldo-dias-pendientes').textContent = '15 días';
    document.getElementById('saldo-periodos-activos').textContent = opcion.dataset.sublabel || 'Período vigente';
    tarjetaSaldo?.classList.remove('hidden');
  });

  const actualizarDias = () => {
    const dias = calcularDiasEntreFechas(inputInicio?.value, inputFin?.value);
    if (inputDias) inputDias.value = dias > 0 ? String(dias) : '';
  };
  inputInicio?.addEventListener('change', actualizarDias);
  inputFin?.addEventListener('change', actualizarDias);

  const tabla = new GestorDataTable('#tabla-vacaciones', {
    datos: solicitudesIniciales,
    ordenInicial: [[3, 'desc']],
    columnas: obtenerColumnasVacaciones(),
    alAccionar: (accion, registro) => {
      if (accion !== 'mostrar') return;
      modalDetalle.abrir({
        titulo: `Solicitud #${registro.solicitud_vacaciones_id} · ${registro.empleado}`,
        subtitulo: `${formatearFecha(registro.fecha_inicio)} al ${formatearFecha(registro.fecha_final)} (${registro.dias_solicitado} días solicitados)`,
        contenidoHtml: renderDetalleVacaciones(registro),
      });
    },
  });

  if (!tabla) return;

  document.getElementById('filtro-estatus-vacaciones')?.addEventListener('change', (evento) => {
    tabla.filtrarColumna(5, evento.target.value);
  });

  const formulario = document.getElementById('formulario-vacaciones');
  formulario?.addEventListener('submit', (evento) => {
    evento.preventDefault();

    const opcionEmp = selectEmpleado?.options[selectEmpleado.selectedIndex];
    const dias = Number(inputDias?.value || 0);

    if (!selectEmpleado?.value || !opcionEmp || !inputInicio?.value || !inputFin?.value || dias <= 0) {
      mostrarToast('error', 'Selecciona el empleado y un rango de fechas válido.');
      return;
    }

    const nombreEmp = opcionEmp.textContent.trim();
    const nuevaSolicitud = {
      solicitud_vacaciones_id: solicitudesIniciales.length + 1,
      empleado: nombreEmp,
      cedula: opcionEmp.dataset.sublabel || '—',
      dias_solicitado: dias,
      fecha_inicio: inputInicio.value,
      fecha_final: inputFin.value,
      estatus_texto: 'Pendiente',
      periodos_descontados: [{ periodo: new Date().getFullYear(), dia_descontados: dias }],
      historial: [
        {
          fecha_registro: new Date().toISOString().slice(0, 16).replace('T', ' '),
          estatus: 'Pendiente',
          observacion: 'Solicitud registrada desde la interfaz.',
        },
      ],
    };

    solicitudesIniciales = [nuevaSolicitud, ...solicitudesIniciales];
    tabla.actualizarDatos(solicitudesIniciales);
    formulario.reset();
    tarjetaSaldo?.classList.add('hidden');
    mostrarToast('success', `Solicitud de ${dias} días registrada para ${nombreEmp}.`);
  });
});
