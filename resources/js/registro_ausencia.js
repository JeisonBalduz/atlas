/**
 * registro_ausencia.js
 * Registro de Permisos y Reposos Médicos con DataTables 3.1.5.
 */

import { ControladorModal } from '/js/class/ControladorModal.js';
import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { inicializarSelectsPersonalizados } from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';
import { crearBadge, crearBotonAccion, crearListaDetalle } from '/js/templates/template_ui.js';
import { calcularDiasEntreFechas, escaparHtml, formatearFecha } from '/js/utils/dom.js';

let ausenciasIniciales = [
  {
    ausencia_id: 1,
    cedula: 'V-21456789',
    empleado: 'Carlos Eduardo Mendoza Ríos',
    tipo_texto: 'Permiso',
    detalle: 'Diligencia Personal · Renovación de pasaporte',
    fecha_inicio: '2026-11-12',
    fecha_fin: '2026-11-12',
    dias_ausencia: 1,
    extra: { tipo_catalogo: 'Diligencia Personal', remunerable: true, motivo: 'Renovación de pasaporte' },
  },
  {
    ausencia_id: 2,
    cedula: 'V-24112233',
    empleado: 'Ana Gabriela Torres Morales',
    tipo_texto: 'Reposo',
    detalle: 'Enfermedad Común · Síndrome viral agudo',
    fecha_inicio: '2026-11-15',
    fecha_fin: '2026-11-19',
    dias_ausencia: 5,
    extra: {
      tipo_catalogo: 'Enfermedad Común',
      institucion: 'IVSS',
      doctor: 'Dra. Patricia Gómez',
      descripcion: 'Síndrome viral agudo con indicación de reposo absoluto.',
    },
  },
  {
    ausencia_id: 3,
    cedula: 'V-19876543',
    empleado: 'María Fernanda López Silva',
    tipo_texto: 'Permiso',
    detalle: 'Estudios / Examen Académico · Defensa de tesis',
    fecha_inicio: '2026-11-20',
    fecha_fin: '2026-11-21',
    dias_ausencia: 2,
    extra: { tipo_catalogo: 'Estudios / Examen Académico', remunerable: true, motivo: 'Defensa de tesis de maestría' },
  },
];

const renderDetalleAusencia = (registro) => {
  if (registro.tipo_texto === 'Permiso') {
    return crearListaDetalle([
      { etiqueta: 'Tipo de permiso', valorHtml: escaparHtml(registro.extra.tipo_catalogo) },
      {
        etiqueta: 'Remunerable',
        valorHtml: crearBadge(registro.extra.remunerable ? 'Sí' : 'No', registro.extra.remunerable ? 'green' : 'red'),
      },
      { etiqueta: 'Motivo', valorHtml: escaparHtml(registro.extra.motivo), anchoCompleto: true },
    ]);
  }

  return crearListaDetalle([
    { etiqueta: 'Tipo de reposo', valorHtml: escaparHtml(registro.extra.tipo_catalogo) },
    { etiqueta: 'Institución emisora', valorHtml: escaparHtml(registro.extra.institucion) },
    { etiqueta: 'Médico tratante', valorHtml: escaparHtml(registro.extra.doctor || 'No especificado') },
    { etiqueta: 'Descripción', valorHtml: escaparHtml(registro.extra.descripcion), anchoCompleto: true },
  ]);
};

const obtenerColumnasAusencias = () => [
  { data: 'cedula', render: (v) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${escaparHtml(v || '—')}</span>` },
  { data: 'empleado', render: (v) => `<span class="font-medium text-zinc-900">${escaparHtml(v)}</span>` },
  {
    data: 'tipo_texto',
    className: 'text-center',
    render: (v) => crearBadge(v, v === 'Permiso' ? 'sky' : 'yellow'),
  },
  { data: 'detalle', render: (v) => escaparHtml(v) },
  { data: 'fecha_inicio', render: (v) => `<span class="whitespace-nowrap">${escaparHtml(formatearFecha(v))}</span>` },
  { data: 'fecha_fin', render: (v) => `<span class="whitespace-nowrap">${escaparHtml(formatearFecha(v))}</span>` },
  {
    data: 'dias_ausencia',
    className: 'text-center',
    render: (v) => `<span class="font-semibold text-zinc-900">${escaparHtml(v)}</span>`,
  },
  {
    data: null,
    orderable: false,
    searchable: false,
    className: 'text-right',
    render: (_d, _t, fila) =>
      crearBotonAccion({ accion: 'mostrar', id: fila.ausencia_id, etiqueta: 'Ver', icono: 'eye', variante: 'marca' }),
  },
];

const construirRegistroPermiso = ({ cedulaEmp, nombreEmp, fechaInicio, fechaFin, dias }) => {
  const selectPerm = document.getElementById('tppermiso_id');
  const motivo = document.getElementById('motivo')?.value.trim();
  if (!selectPerm?.value || !motivo) {
    mostrarToast('error', 'Selecciona el tipo de permiso e indica el motivo.');
    return null;
  }

  const nombrePermiso = selectPerm.options[selectPerm.selectedIndex]?.textContent.trim() || 'Permiso';
  return {
    ausencia_id: ausenciasIniciales.length + 1,
    cedula: cedulaEmp,
    empleado: nombreEmp,
    tipo_texto: 'Permiso',
    detalle: `${nombrePermiso} · ${motivo}`,
    fecha_inicio: fechaInicio,
    fecha_fin: fechaFin,
    dias_ausencia: dias,
    extra: {
      tipo_catalogo: nombrePermiso,
      remunerable: Boolean(document.getElementById('remunerable')?.checked),
      motivo,
    },
  };
};

const construirRegistroReposo = ({ cedulaEmp, nombreEmp, fechaInicio, fechaFin, dias }) => {
  const selectRep = document.getElementById('tpreposo_id');
  const selectInst = document.getElementById('tipo_institucion_emisora_id');
  const descripcion = document.getElementById('descripcion')?.value.trim();
  const doctor = document.getElementById('doctor')?.value.trim();
  if (!selectRep?.value || !descripcion) {
    mostrarToast('error', 'Selecciona el tipo de reposo e indica la descripción médica.');
    return null;
  }

  const nombreReposo = selectRep.options[selectRep.selectedIndex]?.textContent.trim() || 'Reposo';
  const nombreInst = selectInst?.value
    ? selectInst.options[selectInst.selectedIndex]?.textContent.trim()
    : 'No especificada';

  return {
    ausencia_id: ausenciasIniciales.length + 1,
    cedula: cedulaEmp,
    empleado: nombreEmp,
    tipo_texto: 'Reposo',
    detalle: `${nombreReposo} · ${descripcion}`,
    fecha_inicio: fechaInicio,
    fecha_fin: fechaFin,
    dias_ausencia: dias,
    extra: {
      tipo_catalogo: nombreReposo,
      institucion: nombreInst,
      doctor: doctor || 'No especificado',
      descripcion,
    },
  };
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();
  const modalDetalle = new ControladorModal('#modal-detalle-ausencia');
  const bloquePermiso = document.getElementById('bloque-campos-permiso');
  const bloqueReposo = document.getElementById('bloque-campos-reposo');
  const inputInicio = document.getElementById('fecha_inicio');
  const inputFin = document.getElementById('fecha_fin');
  const inputDias = document.getElementById('dias_ausencia');

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

  const tabla = new GestorDataTable('#tabla-ausencias', {
    datos: ausenciasIniciales,
    ordenInicial: [[4, 'desc']],
    columnas: obtenerColumnasAusencias(),
    alAccionar: (accion, registro) => {
      if (accion !== 'mostrar') return;
      modalDetalle.abrir({
        titulo: `${registro.tipo_texto} · ${registro.empleado}`,
        subtitulo: `${formatearFecha(registro.fecha_inicio)} al ${formatearFecha(registro.fecha_fin)} (${registro.dias_ausencia} días)`,
        contenidoHtml: renderDetalleAusencia(registro),
      });
    },
  });

  if (!tabla) return;

  const botonesFiltro = document.querySelectorAll('.btn-filtro-ausencia');
  botonesFiltro.forEach((boton) => {
    boton.addEventListener('click', () => {
      const filtro = boton.dataset.filtroTipo || 'todos';
      botonesFiltro.forEach((btn) => {
        const activo = btn === boton;
        btn.classList.toggle('bg-marca', activo);
        btn.classList.toggle('text-white', activo);
        btn.classList.toggle('text-zinc-500', !activo);
      });
      tabla.filtrarColumna(2, filtro);
    });
  });

  const formulario = document.getElementById('formulario-ausencia');
  formulario?.addEventListener('submit', (evento) => {
    evento.preventDefault();

    const tipo = Number(document.querySelector('input[name="tipo_ausencia"]:checked')?.value || 1);
    const selectEmp = document.getElementById('empleado_id');
    const opcionEmp = selectEmp?.options[selectEmp.selectedIndex];
    const dias = Number(inputDias?.value || 0);

    if (!selectEmp?.value || !opcionEmp || !inputInicio?.value || !inputFin?.value || dias <= 0) {
      mostrarToast('error', 'Completa el empleado y el rango de fechas de la ausencia.');
      return;
    }

    const base = {
      nombreEmp: opcionEmp.textContent.trim(),
      cedulaEmp: opcionEmp.dataset.sublabel || '—',
      fechaInicio: inputInicio.value,
      fechaFin: inputFin.value,
      dias,
    };

    const nuevoRegistro = tipo === 1 ? construirRegistroPermiso(base) : construirRegistroReposo(base);
    if (!nuevoRegistro) return;

    ausenciasIniciales = [nuevoRegistro, ...ausenciasIniciales];
    tabla.actualizarDatos(ausenciasIniciales);
    formulario.reset();
    alternarBloques();
    mostrarToast('success', `${nuevoRegistro.tipo_texto} registrado para ${base.nombreEmp}.`);
  });
});
