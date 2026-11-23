/**
 * registro_beneficios.js
 * Asignación y seguimiento de beneficios con DataTables 3.1.5.
 */

import { ControladorModal } from '/js/class/ControladorModal.js';
import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { inicializarSelectsPersonalizados } from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';
import {
  crearBadge,
  crearBotonAccion,
  crearGrupoBotones,
  crearListaDetalle,
} from '/js/templates/template_ui.js';
import { escaparHtml, formatearFecha } from '/js/utils/dom.js';

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

const renderAccionesBeneficio = (fila) =>
  crearGrupoBotones([
    crearBotonAccion({ accion: 'mostrar', id: fila.dtbeneficio_id, etiqueta: 'Ver', icono: 'eye', variante: 'marca' }),
    fila.estatus === 'Pendiente'
      ? crearBotonAccion({ accion: 'editar', id: fila.dtbeneficio_id, etiqueta: 'Revisar', icono: 'pencil', variante: 'zinc' })
      : '',
  ]);

const renderDetalleBeneficio = (registro) => {
  const esPendiente = registro.estatus === 'Pendiente';
  return crearListaDetalle([
    { etiqueta: 'Cédula', valorHtml: `<span class="font-semibold">${escaparHtml(registro.cedula)}</span>` },
    { etiqueta: 'Estatus actual', valorHtml: crearBadge(registro.estatus, esPendiente ? 'yellow' : 'green') },
    { etiqueta: 'Fecha de solicitud', valorHtml: escaparHtml(formatearFecha(registro.fecha_solicitud)) },
    {
      etiqueta: 'Fecha de aprobación',
      valorHtml: registro.fecha_aprobada ? escaparHtml(formatearFecha(registro.fecha_aprobada)) : 'Pendiente',
    },
  ]);
};

const obtenerColumnasBeneficios = () => [
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
    render: (_d, _t, fila) => renderAccionesBeneficio(fila),
  },
];

const abrirModalBeneficio = ({ modalDetalle, registro, aprobarRegistro }) => {
  const esPendiente = registro.estatus === 'Pendiente';

  modalDetalle.abrir({
    titulo: registro.tipo_beneficio,
    subtitulo: `${registro.beneficiario} · ${registro.rol_parentesco}`,
    contenidoHtml: renderDetalleBeneficio(registro),
    mostrarConfirmar: esPendiente,
    textoConfirmar: 'Aprobar hoy',
    alConfirmar: esPendiente ? () => aprobarRegistro(registro) : null,
  });
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();
  const modalDetalle = new ControladorModal('#modal-detalle-beneficio');

  let tabla = null;
  const aprobarRegistro = (registro) => {
    const hoy = new Date().toISOString().slice(0, 10);
    beneficiosIniciales = beneficiosIniciales.map((item) =>
      item.dtbeneficio_id === registro.dtbeneficio_id
        ? { ...item, fecha_aprobada: hoy, estatus: 'Aprobado' }
        : item
    );
    tabla.actualizarDatos(beneficiosIniciales);
    mostrarToast('success', `Beneficio "${registro.tipo_beneficio}" aprobado para ${registro.beneficiario}.`);
  };

  tabla = new GestorDataTable('#tabla-beneficios', {
    datos: beneficiosIniciales,
    ordenInicial: [[4, 'desc']],
    columnas: obtenerColumnasBeneficios(),
    alAccionar: (_accion, registro) => abrirModalBeneficio({ modalDetalle, registro, aprobarRegistro }),
  });

  if (!tabla) return;

  document.getElementById('filtro-estatus-beneficio')?.addEventListener('change', (evento) => {
    tabla.filtrarColumna(6, evento.target.value);
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
    const nombrePersona = opcionPersona.textContent.trim();
    const nuevo = {
      dtbeneficio_id: beneficiosIniciales.length + 1,
      cedula: sublabelPartes[0] || '—',
      beneficiario: nombrePersona,
      rol_parentesco: sublabelPartes[1] || 'Beneficiario',
      tipo_beneficio: opcionBeneficio?.textContent.trim() || 'Beneficio',
      fecha_solicitud: fechaSolicitud,
      fecha_aprobada: fechaAprobada,
      estatus: fechaAprobada ? 'Aprobado' : 'Pendiente',
    };

    beneficiosIniciales = [nuevo, ...beneficiosIniciales];
    tabla.actualizarDatos(beneficiosIniciales);
    formulario.reset();
    mostrarToast('success', `Beneficio "${nuevo.tipo_beneficio}" asignado a ${nombrePersona}.`);
  });
});
