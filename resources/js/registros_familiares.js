/**
 * registros_familiares.js
 * Listado Server-Side con DataTables 3.1.5 (100% JS Nativo, cero jQuery),
 * conectado a POST /api/familiares/datatable y DELETE /api/familiares/{id}.
 */

import { ControladorModal } from '/js/class/ControladorModal.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { mostrarToast } from '/js/components/toast.js';
import {
  crearBadge,
  crearBotonAccion,
  crearTablaServidor,
  escaparHtml,
  formatearFecha,
} from '/js/datatables/configDataTable.js';

document.addEventListener('DOMContentLoaded', () => {
  const modalDetalle = new ControladorModal('#modal-detalle-familiar');
  const modalEliminar = new ControladorModal('#modal-eliminar-familiar');
  const filtroParentesco = document.getElementById('filtro-parentesco');

  const obtenerFiltros = () => {
    const filtros = {};
    if (filtroParentesco && filtroParentesco.value !== 'todos') {
      filtros.parentesco_id = filtroParentesco.value;
    }
    return filtros;
  };

  const tabla = crearTablaServidor('#tabla-familiares', {
    url: '/api/familiares/datatable',
    ordenInicial: [[3, 'asc']],
    obtenerFiltros,
    columnas: [
      {
        data: 'cedula_titular',
        name: 'cedula_titular',
        render: (valor) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${escaparHtml(valor || '—')}</span>`,
      },
      {
        data: 'empleado_titular',
        name: 'empleado_titular',
        render: (valor) => `<span class="font-medium text-zinc-900">${escaparHtml(valor || '—')}</span>`,
      },
      {
        data: 'cedula_familiar',
        name: 'cedula_familiar',
        render: (valor) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${escaparHtml(valor || '—')}</span>`,
      },
      {
        data: 'nombre_familiar',
        name: 'nombre_familiar',
        render: (valor, _tipo, fila) => {
          const fechaHtml = fila.fecha_nacimiento
            ? `<span class="block text-xs text-zinc-500">Nac: ${escaparHtml(formatearFecha(fila.fecha_nacimiento))}</span>`
            : '';
          return `<div class="min-w-44"><span class="font-semibold text-zinc-900">${escaparHtml(valor || '—')}</span>${fechaHtml}</div>`;
        },
      },
      {
        data: 'parentesco',
        name: 'parentesco',
        render: (valor) => crearBadge(valor || 'Sin definir', 'marca'),
      },
      {
        data: 'edad',
        name: 'edad',
        className: 'text-center',
        render: (valor) =>
          valor !== null && valor !== undefined
            ? `<span class="whitespace-nowrap">${escaparHtml(valor)} años</span>`
            : '—',
      },
      {
        data: 'genero',
        name: 'genero',
        className: 'text-center',
        render: (valor) => `<span class="whitespace-nowrap">${escaparHtml(valor || '—')}</span>`,
      },
      {
        data: 'tomo_folio',
        name: 'tomo_folio',
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: (valor) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${escaparHtml(valor || '—')}</span>`,
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-right',
        render: (_datos, _tipo, fila) => {
          const btnVer = crearBotonAccion({
            accion: 'mostrar',
            id: fila.familia_id,
            etiqueta: 'Ver',
            icono: 'eye',
            variante: 'marca',
            titulo: `Ver ficha de ${fila.nombre_familiar || 'familiar'}`,
          });

          const btnEditar = crearBotonAccion({
            etiqueta: 'Editar',
            icono: 'pencil',
            variante: 'zinc',
            href: `/empleado/familiar/editar/${fila.familia_id}`,
            titulo: `Editar a ${fila.nombre_familiar || 'familiar'}`,
          });

          const btnEliminar = crearBotonAccion({
            accion: 'eliminar',
            id: fila.familia_id,
            etiqueta: 'Eliminar',
            icono: 'trash-2',
            variante: 'red',
            titulo: `Eliminar vínculo de ${fila.nombre_familiar || 'familiar'}`,
          });

          return `<div class="inline-flex items-center justify-end gap-1.5">${btnVer}${btnEditar}${btnEliminar}</div>`;
        },
      },
    ],
  });

  if (!tabla) return;

  filtroParentesco?.addEventListener('change', () => tabla.ajax.reload());

  document.getElementById('tabla-familiares')?.addEventListener('click', (evento) => {
    const boton = evento.target.closest('button[data-accion]');
    if (!boton || boton.disabled) return;

    const accion = boton.dataset.accion;
    const filaDom = boton.closest('tr');
    const registro = filaDom ? tabla.row(filaDom).data() : null;
    if (!registro) return;

    if (accion === 'mostrar') {
      const edadTexto =
        registro.edad !== null && registro.edad !== undefined ? ` (${registro.edad} años)` : '';

      modalDetalle.abrir({
        titulo: registro.nombre_familiar || 'Familiar',
        subtitulo: `${registro.parentesco || ''} de ${registro.empleado_titular || ''}`,
        contenidoHtml: `
          <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
              <dt class="text-xs font-medium text-zinc-500">Empleado titular</dt>
              <dd class="mt-1 font-semibold text-zinc-900">${escaparHtml(registro.empleado_titular || '—')}</dd>
              <dd class="text-xs text-zinc-500">${escaparHtml(registro.cedula_titular || '—')}</dd>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
              <dt class="text-xs font-medium text-zinc-500">Cédula del familiar</dt>
              <dd class="mt-1 font-semibold text-zinc-900">${escaparHtml(registro.cedula_familiar || '—')}</dd>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
              <dt class="text-xs font-medium text-zinc-500">Fecha de nacimiento y edad</dt>
              <dd class="mt-1 font-medium text-zinc-900">${escaparHtml(formatearFecha(registro.fecha_nacimiento))}${escaparHtml(edadTexto)}</dd>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
              <dt class="text-xs font-medium text-zinc-500">Datos de acta y género</dt>
              <dd class="mt-1 font-medium text-zinc-900">Tomo ${escaparHtml(registro.tomo)} · Folio ${escaparHtml(registro.folio)} · ${escaparHtml(registro.genero || '—')}</dd>
            </div>
          </dl>
        `,
      });
      return;
    }

    if (accion === 'eliminar') {
      modalEliminar.abrir({
        titulo: 'Eliminar carga familiar',
        subtitulo: `${registro.parentesco || ''} · ${registro.cedula_familiar || ''}`,
        contenidoHtml: `
          <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            ¿Deseas eliminar el vínculo familiar de <strong>${escaparHtml(registro.nombre_familiar)}</strong>?
          </div>
        `,
        textoConfirmar: 'Eliminar registro',
        alConfirmar: (dialogo) => {
          const btnConfirmar = dialogo.querySelector('[data-modal-confirmar]');
          const textoOriginal = btnConfirmar?.textContent || 'Eliminar registro';

          if (btnConfirmar) {
            btnConfirmar.disabled = true;
            btnConfirmar.textContent = 'Eliminando...';
          }

          Solicitud.delete(`/api/familiares/${registro.familia_id}`)
            .then((res) => {
              if (res.status === 401) {
                window.location.href = '/autenticacion';
                return;
              }
              if (!res.ok || !res.data?.ok) {
                mostrarToast('error', res.message || 'No se pudo eliminar el registro familiar.');
                return;
              }

              mostrarToast('success', res.data.message || 'Carga familiar eliminada correctamente.');
              modalEliminar.cerrar();
              tabla.ajax.reload(null, false);
            })
            .finally(() => {
              if (btnConfirmar) {
                btnConfirmar.disabled = false;
                btnConfirmar.textContent = textoOriginal;
              }
            });

          return false;
        },
      });
    }
  });
});
