/**
 * registro_beneficios.js
 * Asignación y seguimiento de beneficios con DataTables 3.1.5 (100% JS Nativo, cero jQuery)
 * preservando los <select> hidratados desde PHP.
 */

import { ControladorModal } from '/js/class/ControladorModal.js';
import { inicializarSelectsPersonalizados } from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';
import {
  crearBadge,
  crearBotonAccion,
  crearTablaCliente,
  escaparHtml,
  formatearFecha,
} from '/js/datatables/configDataTable.js';

let beneficiosIniciales = [
  {
    dtbeneficio_id: 1,
    cedula: 'V-31245678',
    beneficiario: 'Sofía Valentina Mendoza Herrera',
    rol_parentesco: 'Hija',
    tipo_beneficio: 'Beca Escolar',
    fecha_solicitud: '2026-09-10',
    fecha_aprobada: '2026-09-18',
    estatus: 'Aprobado',
  },
  {
    dtbeneficio_id: 2,
    cedula: 'V-19876543',
    beneficiario: 'María Fernanda López Silva',
    rol_parentesco: 'Titular',
    tipo_beneficio: 'Lentes Correctivos',
    fecha_solicitud: '2026-11-12',
    fecha_aprobada: '',
    estatus: 'Pendiente',
  },
  {
    dtbeneficio_id: 3,
    cedula: 'V-6123456',
    beneficiario: 'Carmen Teresa Silva de López',
    rol_parentesco: 'Madre',
    tipo_beneficio: 'Ayuda de Medicamentos',
    fecha_solicitud: '2026-11-05',
    fecha_aprobada: '2026-11-09',
    estatus: 'Aprobado',
  },
];

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();
  const modalDetalle = new ControladorModal('#modal-detalle-beneficio');

  const tabla = crearTablaCliente('#tabla-beneficios', {
    datos: beneficiosIniciales,
    ordenInicial: [[4, 'desc']],
    columnas: [
      { data: 'cedula', render: (v) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${escaparHtml(v || '—')}</span>` },
      { data: 'beneficiario', render: (v) => `<span class="font-medium text-zinc-900">${escaparHtml(v)}</span>` },
      { data: 'rol_parentesco', render: (v) => escaparHtml(v || '—') },
      { data: 'tipo_beneficio', render: (v) => escaparHtml(v || '—') },
      { data: 'fecha_solicitud', render: (v) => `<span class="whitespace-nowrap">${escaparHtml(formatearFecha(v))}</span>` },
      {
        data: 'fecha_aprobada',
        render: (v) => `<span class="whitespace-nowrap">${v ? escaparHtml(formatearFecha(v)) : 'Por aprobar'}</span>`,
      },
      {
        data: 'estatus',
        className: 'text-center',
        render: (v) => crearBadge(v, v === 'Aprobado' ? 'green' : 'yellow'),
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-right',
        render: (_d, _t, fila) => {
          const btnVer = crearBotonAccion({
            accion: 'mostrar',
            id: fila.dtbeneficio_id,
            etiqueta: 'Ver',
            icono: 'eye',
            variante: 'marca',
          });
          const btnAprobar =
            fila.estatus === 'Pendiente'
              ? crearBotonAccion({
                  accion: 'editar',
                  id: fila.dtbeneficio_id,
                  etiqueta: 'Revisar',
                  icono: 'pencil',
                  variante: 'zinc',
                })
              : '';
          return `<div class="inline-flex items-center justify-end gap-1.5">${btnVer}${btnAprobar}</div>`;
        },
      },
    ],
  });

  if (!tabla) return;

  document.getElementById('tabla-beneficios')?.addEventListener('click', (evento) => {
    const boton = evento.target.closest('button[data-accion]');
    if (!boton) return;

    const registro = tabla.row(boton.closest('tr')).data();
    if (!registro) return;

    const esPendiente = registro.estatus === 'Pendiente';
    const botonConfirmar = document.querySelector('#modal-detalle-beneficio [data-modal-confirmar]');
    botonConfirmar?.classList.toggle('hidden', !esPendiente);

    modalDetalle.abrir({
      titulo: registro.tipo_beneficio,
      subtitulo: `${registro.beneficiario} · ${registro.rol_parentesco}`,
      contenidoHtml: `
        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
            <dt class="text-xs font-medium text-zinc-500">Cédula</dt>
            <dd class="mt-1 font-semibold text-zinc-900">${escaparHtml(registro.cedula)}</dd>
          </div>
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
            <dt class="text-xs font-medium text-zinc-500">Estatus actual</dt>
            <dd class="mt-1">${crearBadge(registro.estatus, esPendiente ? 'yellow' : 'green')}</dd>
          </div>
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
            <dt class="text-xs font-medium text-zinc-500">Fecha de solicitud</dt>
            <dd class="mt-1 font-medium text-zinc-900">${escaparHtml(formatearFecha(registro.fecha_solicitud))}</dd>
          </div>
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
            <dt class="text-xs font-medium text-zinc-500">Fecha de aprobación</dt>
            <dd class="mt-1 font-medium text-zinc-900">${registro.fecha_aprobada ? escaparHtml(formatearFecha(registro.fecha_aprobada)) : 'Pendiente'}</dd>
          </div>
        </dl>
      `,
      textoConfirmar: 'Aprobar hoy',
      alConfirmar: esPendiente
        ? () => {
            const hoy = new Date().toISOString().slice(0, 10);
            beneficiosIniciales = beneficiosIniciales.map((item) =>
              item.dtbeneficio_id === registro.dtbeneficio_id
                ? { ...item, fecha_aprobada: hoy, estatus: 'Aprobado' }
                : item
            );
            tabla.clear().rows.add(beneficiosIniciales).draw();
            mostrarToast('success', `Beneficio "${registro.tipo_beneficio}" aprobado para ${registro.beneficiario}.`);
          }
        : null,
    });
  });

  document.getElementById('filtro-estatus-beneficio')?.addEventListener('change', (evento) => {
    const val = evento.target.value;
    tabla.column(6).search(val === 'todos' ? '' : val).draw();
  });

  const formulario = document.getElementById('formulario-beneficios');
  formulario?.addEventListener('submit', (evento) => {
    evento.preventDefault();

    const selectPersona = document.getElementById('persona_id');
    const selectBeneficio = document.getElementById('beneficio_id');
    const fechaSolicitud = document.getElementById('fecha_solicitud')?.value;
    const fechaAprobada = document.getElementById('fecha_aprobada')?.value || '';

    const opcionPersona = selectPersona?.options[selectPersona.selectedIndex];
    const opcionBeneficio = selectBeneficio?.options[selectBeneficio.selectedIndex];

    if (!selectPersona?.value || !opcionPersona || !selectBeneficio?.value || !fechaSolicitud) {
      mostrarToast('error', 'Selecciona beneficiario, tipo de beneficio y fecha de solicitud.');
      return;
    }

    const sublabelPartes = (opcionPersona.dataset.sublabel || '').split('·').map((s) => s.trim());
    const cedulaPersona = sublabelPartes[0] || '—';
    const rolPersona = sublabelPartes[1] || 'Beneficiario';
    const nombrePersona = opcionPersona.textContent.trim();
    const nombreBeneficio = opcionBeneficio?.textContent.trim() || 'Beneficio';

    const nuevo = {
      dtbeneficio_id: beneficiosIniciales.length + 1,
      cedula: cedulaPersona,
      beneficiario: nombrePersona,
      rol_parentesco: rolPersona,
      tipo_beneficio: nombreBeneficio,
      fecha_solicitud: fechaSolicitud,
      fecha_aprobada: fechaAprobada,
      estatus: fechaAprobada ? 'Aprobado' : 'Pendiente',
    };

    beneficiosIniciales = [nuevo, ...beneficiosIniciales];
    tabla.clear().rows.add(beneficiosIniciales).draw();
    formulario.reset();
    mostrarToast('success', `Beneficio "${nuevo.tipo_beneficio}" asignado a ${nombrePersona}.`);
  });
});
