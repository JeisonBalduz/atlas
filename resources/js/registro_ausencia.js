/**
 * registro_ausencia.js
 * Registro de Permisos y Reposos Médicos con DataTables 3.1.5 (100% JS Nativo, cero jQuery)
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

const calcularDias = (inicio, fin) => {
  if (!inicio || !fin) return 0;
  const f1 = new Date(`${inicio}T00:00:00`);
  const f2 = new Date(`${fin}T00:00:00`);
  if (Number.isNaN(f1.getTime()) || Number.isNaN(f2.getTime()) || f2 < f1) return 0;
  return Math.round((f2 - f1) / (1000 * 60 * 60 * 24)) + 1;
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();
  const modalDetalle = new ControladorModal('#modal-detalle-ausencia');

  // Alternar campos Permiso vs Reposo
  const bloquePermiso = document.getElementById('bloque-campos-permiso');
  const bloqueReposo = document.getElementById('bloque-campos-reposo');
  const radiosTipo = document.querySelectorAll('input[name="tipo_ausencia"]');

  const alternarBloques = () => {
    const tipo = document.querySelector('input[name="tipo_ausencia"]:checked')?.value || '1';
    const esPermiso = tipo === '1';
    bloquePermiso?.classList.toggle('hidden', !esPermiso);
    bloqueReposo?.classList.toggle('hidden', esPermiso);
  };
  radiosTipo.forEach((radio) => radio.addEventListener('change', alternarBloques));

  // Cálculo automático de días
  const inputInicio = document.getElementById('fecha_inicio');
  const inputFin = document.getElementById('fecha_fin');
  const inputDias = document.getElementById('dias_ausencia');

  const actualizarDias = () => {
    const dias = calcularDias(inputInicio?.value, inputFin?.value);
    if (inputDias) inputDias.value = dias > 0 ? String(dias) : '';
  };
  inputInicio?.addEventListener('change', actualizarDias);
  inputFin?.addEventListener('change', actualizarDias);

  // DataTable 3.1.5 (JS Nativo)
  const tabla = crearTablaCliente('#tabla-ausencias', {
    datos: ausenciasIniciales,
    ordenInicial: [[4, 'desc']],
    columnas: [
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
          crearBotonAccion({
            accion: 'mostrar',
            id: fila.ausencia_id,
            etiqueta: 'Ver',
            icono: 'eye',
            variante: 'marca',
          }),
      },
    ],
  });

  if (!tabla) return;

  // Delegación para ver detalle
  document.getElementById('tabla-ausencias')?.addEventListener('click', (evento) => {
    const boton = evento.target.closest('button[data-accion="mostrar"]');
    if (!boton) return;

    const registro = tabla.row(boton.closest('tr')).data();
    if (!registro) return;

    const detalleEspecifico =
      registro.tipo_texto === 'Permiso'
        ? `
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
            <dt class="text-xs font-medium text-zinc-500">Tipo de permiso</dt>
            <dd class="mt-1 font-medium text-zinc-900">${escaparHtml(registro.extra.tipo_catalogo)}</dd>
          </div>
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
            <dt class="text-xs font-medium text-zinc-500">Remunerable</dt>
            <dd class="mt-1">${crearBadge(registro.extra.remunerable ? 'Sí' : 'No', registro.extra.remunerable ? 'green' : 'red')}</dd>
          </div>
          <div class="sm:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-3.5">
            <dt class="text-xs font-medium text-zinc-500">Motivo</dt>
            <dd class="mt-1 text-zinc-900">${escaparHtml(registro.extra.motivo)}</dd>
          </div>
        `
        : `
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
            <dt class="text-xs font-medium text-zinc-500">Tipo de reposo</dt>
            <dd class="mt-1 font-medium text-zinc-900">${escaparHtml(registro.extra.tipo_catalogo)}</dd>
          </div>
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
            <dt class="text-xs font-medium text-zinc-500">Institución emisora</dt>
            <dd class="mt-1 font-medium text-zinc-900">${escaparHtml(registro.extra.institucion)}</dd>
          </div>
          <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
            <dt class="text-xs font-medium text-zinc-500">Médico tratante</dt>
            <dd class="mt-1 font-medium text-zinc-900">${escaparHtml(registro.extra.doctor || 'No especificado')}</dd>
          </div>
          <div class="sm:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-3.5">
            <dt class="text-xs font-medium text-zinc-500">Descripción</dt>
            <dd class="mt-1 text-zinc-900">${escaparHtml(registro.extra.descripcion)}</dd>
          </div>
        `;

    modalDetalle.abrir({
      titulo: `${registro.tipo_texto} · ${registro.empleado}`,
      subtitulo: `${formatearFecha(registro.fecha_inicio)} al ${formatearFecha(registro.fecha_fin)} (${registro.dias_ausencia} días)`,
      contenidoHtml: `<dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">${detalleEspecifico}</dl>`,
    });
  });

  // Botones de filtro rápido por tipo usando DataTables column().search()
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
      tabla.column(2).search(filtro === 'todos' ? '' : filtro).draw();
    });
  });

  // Registro local usando las opciones reales hidratadas por PHP
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

    const nombreEmp = opcionEmp.textContent.trim();
    const cedulaEmp = opcionEmp.dataset.sublabel || '—';

    let nuevoRegistro;
    if (tipo === 1) {
      const selectPerm = document.getElementById('tppermiso_id');
      const motivo = document.getElementById('motivo')?.value.trim();
      if (!selectPerm?.value || !motivo) {
        mostrarToast('error', 'Selecciona el tipo de permiso e indica el motivo.');
        return;
      }
      const nombrePermiso = selectPerm.options[selectPerm.selectedIndex]?.textContent.trim() || 'Permiso';
      nuevoRegistro = {
        ausencia_id: ausenciasIniciales.length + 1,
        cedula: cedulaEmp,
        empleado: nombreEmp,
        tipo_texto: 'Permiso',
        detalle: `${nombrePermiso} · ${motivo}`,
        fecha_inicio: inputInicio.value,
        fecha_fin: inputFin.value,
        dias_ausencia: dias,
        extra: {
          tipo_catalogo: nombrePermiso,
          remunerable: Boolean(document.getElementById('remunerable')?.checked),
          motivo,
        },
      };
    } else {
      const selectRep = document.getElementById('tpreposo_id');
      const selectInst = document.getElementById('tipo_institucion_emisora_id');
      const descripcion = document.getElementById('descripcion')?.value.trim();
      const doctor = document.getElementById('doctor')?.value.trim();
      if (!selectRep?.value || !descripcion) {
        mostrarToast('error', 'Selecciona el tipo de reposo e indica la descripción médica.');
        return;
      }
      const nombreReposo = selectRep.options[selectRep.selectedIndex]?.textContent.trim() || 'Reposo';
      const nombreInst = selectInst?.value
        ? selectInst.options[selectInst.selectedIndex]?.textContent.trim()
        : 'No especificada';

      nuevoRegistro = {
        ausencia_id: ausenciasIniciales.length + 1,
        cedula: cedulaEmp,
        empleado: nombreEmp,
        tipo_texto: 'Reposo',
        detalle: `${nombreReposo} · ${descripcion}`,
        fecha_inicio: inputInicio.value,
        fecha_fin: inputFin.value,
        dias_ausencia: dias,
        extra: {
          tipo_catalogo: nombreReposo,
          institucion: nombreInst,
          doctor: doctor || 'No especificado',
          descripcion,
        },
      };
    }

    ausenciasIniciales = [nuevoRegistro, ...ausenciasIniciales];
    tabla.clear().rows.add(ausenciasIniciales).draw();
    formulario.reset();
    alternarBloques();
    mostrarToast('success', `${nuevoRegistro.tipo_texto} registrado para ${nombreEmp}.`);
  });
});
