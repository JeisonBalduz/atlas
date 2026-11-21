/**
 * registros_empleados.js
 * Listado Server-Side con DataTables 3.1.5 (100% JS Nativo, cero jQuery),
 * conectado a POST /api/empleados/datatable, DELETE /api/empleados/{id} y GET /api/empleados/{id}/familiares.
 */

import { ControladorModal } from '/js/class/ControladorModal.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { inicializarSelectsPersonalizados } from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';
import {
  crearBadge,
  crearBotonAccion,
  crearTablaServidor,
  escaparHtml,
  formatearFecha,
} from '/js/datatables/configDataTable.js';

const variantePorEstatus = (estatus, activo) => {
  if (!activo) return 'red';
  const normalizado = String(estatus || '').trim().toLowerCase();
  if (normalizado.includes('activo') || normalizado.includes('empleado')) return 'green';
  if (normalizado.includes('vacacion')) return 'sky';
  if (normalizado.includes('reposo') || normalizado.includes('permiso')) return 'yellow';
  if (normalizado.includes('inactivo') || normalizado.includes('jubilad') || normalizado.includes('egres')) return 'red';
  return 'marca';
};

const actualizarResumen = (resumen) => {
  if (!resumen) return;
  const nodoTotal = document.getElementById('resumen-total-empleados');
  const nodoActivos = document.getElementById('resumen-empleados-activos');
  const nodoInactivos = document.getElementById('resumen-empleados-inactivos');

  if (nodoTotal && resumen.total !== undefined) nodoTotal.textContent = String(resumen.total);
  if (nodoActivos && resumen.activos !== undefined) nodoActivos.textContent = String(resumen.activos);
  if (nodoInactivos && resumen.inactivos !== undefined) nodoInactivos.textContent = String(resumen.inactivos);
};

const renderizarEstadoCargaFamiliares = () => `
  <div class="flex flex-col items-center justify-center gap-3 py-12 text-center" role="status" aria-live="polite">
    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-marca-50 text-marca">
      <i data-lucide="loader-2" class="h-5 w-5 animate-spin" aria-hidden="true"></i>
    </div>
    <div class="space-y-1">
      <p class="text-sm font-medium text-zinc-900">Consultando carga familiar...</p>
      <p class="text-xs text-zinc-500">Obteniendo familiares vinculados desde la base de datos.</p>
    </div>
  </div>
`;

const renderizarErrorFamiliares = (mensaje) => `
  <div class="flex flex-col items-center justify-center gap-4 py-10 text-center">
    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-red-50 text-red-600">
      <i data-lucide="alert-circle" class="h-5 w-5" aria-hidden="true"></i>
    </div>
    <div class="space-y-1">
      <p class="text-sm font-semibold text-zinc-900">No se pudo cargar la información</p>
      <p class="text-xs text-zinc-500">${escaparHtml(mensaje)}</p>
    </div>
    <button
      type="button"
      data-reintentar-familiares
      class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-zinc-800 focus-visible:outline focus-visible:outline-offset-2 focus-visible:outline-zinc-950 active:scale-[0.98] cursor-pointer"
    >
      <i data-lucide="refresh-cw" class="h-3.5 w-3.5" aria-hidden="true"></i>
      <span>Reintentar</span>
    </button>
  </div>
`;

const renderizarContenidoFamiliares = (payload, empleadoIdFallback) => {
  const empleado = payload?.empleado || {};
  const empleadoId = empleado.empleado_id || empleadoIdFallback;
  const lista = Array.isArray(payload?.data) ? payload.data : [];

  if (lista.length === 0) {
    return `
      <div class="flex flex-col items-center justify-center gap-4 py-10 text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-zinc-500">
          <i data-lucide="users" class="h-6 w-6" aria-hidden="true"></i>
        </div>
        <div class="max-w-sm space-y-1">
          <p class="text-sm font-semibold text-zinc-900">Este trabajador no tiene familiares registrados</p>
          <p class="text-xs text-zinc-500">Puedes asociar su carga familiar ahora mismo usando el botón inferior.</p>
        </div>
        <a
          href="/empleado/registrar/familiar?empleado=${escaparHtml(empleadoId)}"
          class="inline-flex items-center gap-1.5 rounded-lg bg-marca px-4 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-marca-700 focus-visible:outline focus-visible:outline-offset-2 focus-visible:outline-marca active:scale-[0.98]"
        >
          <i data-lucide="user-plus" class="h-3.5 w-3.5" aria-hidden="true"></i>
          <span>Registrar familiar</span>
        </a>
      </div>
    `;
  }

  const filasHtml = lista
    .map((fam) => {
      const edadTexto = fam.edad !== null && fam.edad !== undefined ? `${escaparHtml(fam.edad)} años` : '—';
      const fechaTexto = formatearFecha(fam.fecha_nacimiento);
      const editarBtn = crearBotonAccion({
        etiqueta: 'Editar',
        icono: 'pencil',
        variante: 'zinc',
        href: `/empleado/familiar/editar/${fam.familia_id}`,
        titulo: `Editar a ${fam.nombre_completo || 'familiar'}`,
      });

      return `
        <tr class="hover:bg-slate-50/70 transition-colors">
          <td class="px-3.5 py-2.5 font-medium text-zinc-900 whitespace-nowrap">${escaparHtml(fam.cedula || '—')}</td>
          <td class="px-3.5 py-2.5 font-medium text-zinc-900">${escaparHtml(fam.nombre_completo || '—')}</td>
          <td class="px-3.5 py-2.5 whitespace-nowrap">${crearBadge(fam.parentesco || 'Sin definir', 'marca')}</td>
          <td class="px-3.5 py-2.5 whitespace-nowrap text-zinc-600">
            <span>${escaparHtml(fechaTexto)}</span>
            <span class="block text-xs text-zinc-400">${edadTexto}</span>
          </td>
          <td class="px-3.5 py-2.5 text-center whitespace-nowrap text-zinc-700">${escaparHtml(fam.genero || '—')}</td>
          <td class="px-3.5 py-2.5 text-center whitespace-nowrap font-medium text-zinc-700">T-${escaparHtml(fam.tomo)} / F-${escaparHtml(fam.folio)}</td>
          <td class="px-3.5 py-2.5 text-right whitespace-nowrap">${editarBtn}</td>
        </tr>
      `;
    })
    .join('');

  return `
    <div class="space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
        <div class="text-xs text-zinc-600">
          <span class="font-semibold text-zinc-900">${escaparHtml(lista.length)}</span>
          ${lista.length === 1 ? 'familiar registrado' : 'familiares registrados'}
          ${empleado.departamento ? ` · <span>${escaparHtml(empleado.departamento)}</span>` : ''}
        </div>
        <a
          href="/empleado/registrar/familiar?empleado=${escaparHtml(empleadoId)}"
          class="inline-flex items-center gap-1.5 rounded-lg bg-marca px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-marca-700 focus-visible:outline focus-visible:outline-offset-2 focus-visible:outline-marca active:scale-[0.98]"
        >
          <i data-lucide="user-plus" class="h-3.5 w-3.5" aria-hidden="true"></i>
          <span>Agregar familiar</span>
        </a>
      </div>

      <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-zinc-500">
            <tr>
              <th class="px-3.5 py-2.5">Cédula</th>
              <th class="px-3.5 py-2.5">Nombre completo</th>
              <th class="px-3.5 py-2.5">Parentesco</th>
              <th class="px-3.5 py-2.5">Nacimiento / Edad</th>
              <th class="px-3.5 py-2.5 text-center">Género</th>
              <th class="px-3.5 py-2.5 text-center">Tomo / Folio</th>
              <th class="px-3.5 py-2.5 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">${filasHtml}</tbody>
        </table>
      </div>
    </div>
  `;
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();
  const modalFamiliares = new ControladorModal('#modal-familiares-empleado');
  const modalEliminar = new ControladorModal('#modal-eliminar-empleado');
  const filtroEstatus = document.getElementById('filtro-estatus-empleado');
  const filtroActivo = document.getElementById('filtro-activo-empleado');

  let controladorFamiliares = null;
  let ultimaFilaFamiliares = null;

  const obtenerFiltros = () => {
    const filtros = {};
    if (filtroEstatus && filtroEstatus.value !== 'todos') {
      filtros.estatus_id = filtroEstatus.value;
    }
    if (filtroActivo && filtroActivo.value !== 'todos') {
      filtros.estatus_actual = filtroActivo.value;
    }
    return filtros;
  };

  const tabla = crearTablaServidor('#tabla-empleados', {
    url: '/api/empleados/datatable',
    ordenInicial: [[2, 'asc']],
    obtenerFiltros,
    alCargar: (json) => {
      if (json?.resumen) {
        actualizarResumen(json.resumen);
      }
    },
    columnas: [
      {
        data: 'codigo_empleado',
        name: 'codigo_empleado',
        render: (valor) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${escaparHtml(valor || '—')}</span>`,
      },
      {
        data: 'cedula',
        name: 'cedula',
        render: (valor) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${escaparHtml(valor || '—')}</span>`,
      },
      {
        data: 'nombre_completo',
        name: 'nombre_completo',
        render: (valor, _tipo, fila) => {
          const correoHtml = fila.correo
            ? `<span class="block text-xs text-zinc-500">${escaparHtml(fila.correo)}</span>`
            : '';
          return `<div class="min-w-44"><span class="font-semibold text-zinc-900">${escaparHtml(valor || '—')}</span>${correoHtml}</div>`;
        },
      },
      {
        data: 'cargo',
        name: 'cargo',
        render: (valor) => escaparHtml(valor || '—'),
      },
      {
        data: 'departamento',
        name: 'departamento',
        render: (valor) => escaparHtml(valor || '—'),
      },
      {
        data: 'dependencia',
        name: 'dependencia',
        render: (valor) => escaparHtml(valor || '—'),
      },
      {
        data: 'estatus_actual',
        name: 'estatus_actual',
        className: 'text-center',
        render: (_valor, _tipo, fila) => {
          const badgeLaboral = crearBadge(
            fila.estatus || 'Sin estatus',
            variantePorEstatus(fila.estatus, fila.activo)
          );
          const badgeBaja = !fila.activo ? ` ${crearBadge('Inactivo', 'red')}` : '';
          return `<div class="inline-flex flex-wrap items-center justify-center gap-1">${badgeLaboral}${badgeBaja}</div>`;
        },
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-right',
        render: (_datos, _tipo, fila) => {
          const btnFamiliares = crearBotonAccion({
            accion: 'familiares',
            id: fila.empleado_id,
            etiqueta: 'Familiares',
            icono: 'users',
            variante: 'marca',
            contador: fila.total_familiares ?? 0,
            titulo: `Ver familiares de ${fila.nombre_completo || 'empleado'}`,
          });

          const btnEditar = crearBotonAccion({
            etiqueta: 'Editar',
            icono: 'pencil',
            variante: 'zinc',
            href: `/empleado/editar/${fila.empleado_id}`,
            titulo: `Editar ficha de ${fila.nombre_completo || 'empleado'}`,
          });

          const btnEliminar = crearBotonAccion({
            accion: 'eliminar',
            id: fila.empleado_id,
            etiqueta: fila.activo ? 'Eliminar' : 'Inactivo',
            icono: 'trash-2',
            variante: 'red',
            deshabilitado: !fila.activo,
            titulo: fila.activo
              ? `Desactivar a ${fila.nombre_completo || 'empleado'}`
              : 'Trabajador ya desactivado',
          });

          return `<div class="inline-flex items-center justify-end gap-1.5">${btnFamiliares}${btnEditar}${btnEliminar}</div>`;
        },
      },
    ],
  });

  if (!tabla) return;

  const cargarFamiliaresEmpleado = async (fila) => {
    ultimaFilaFamiliares = fila;

    if (controladorFamiliares) {
      controladorFamiliares.abort();
    }
    controladorFamiliares = new AbortController();

    modalFamiliares.abrir({
      titulo: `Carga familiar · ${fila.nombre_completo || 'Trabajador'}`,
      subtitulo: `${fila.codigo_empleado || ''} · ${fila.cedula || ''}${fila.cargo ? ` · ${fila.cargo}` : ''}`,
      contenidoHtml: renderizarEstadoCargaFamiliares(),
    });

    try {
      const respuesta = await fetch(`/api/empleados/${fila.empleado_id}/familiares`, {
        headers: { Accept: 'application/json' },
        signal: controladorFamiliares.signal,
      });

      if (respuesta.status === 401) {
        window.location.href = '/autenticacion';
        return;
      }

      const json = await respuesta.json().catch(() => null);
      if (!respuesta.ok || !json?.ok) {
        modalFamiliares.establecerContenido(
          renderizarErrorFamiliares(json?.message || 'El servidor no pudo responder la consulta.')
        );
        return;
      }

      modalFamiliares.establecerContenido(renderizarContenidoFamiliares(json, fila.empleado_id));
    } catch (error) {
      if (error?.name === 'AbortError') return;
      modalFamiliares.establecerContenido(
        renderizarErrorFamiliares('Error de conexión al consultar los familiares del trabajador.')
      );
    }
  };

  filtroEstatus?.addEventListener('change', () => tabla.ajax.reload());
  filtroActivo?.addEventListener('change', () => tabla.ajax.reload());

  // Reintentar consulta dentro del modal de familiares
  document.getElementById('modal-familiares-empleado')?.addEventListener('click', (evento) => {
    if (evento.target.closest('[data-reintentar-familiares]') && ultimaFilaFamiliares) {
      cargarFamiliaresEmpleado(ultimaFilaFamiliares);
    }
  });

  // Delegación de eventos de acciones por fila en #tabla-empleados
  document.getElementById('tabla-empleados')?.addEventListener('click', (evento) => {
    const boton = evento.target.closest('button[data-accion]');
    if (!boton || boton.disabled) return;

    const accion = boton.dataset.accion;
    const filaDom = boton.closest('tr');
    const registro = filaDom ? tabla.row(filaDom).data() : null;
    if (!registro) return;

    if (accion === 'familiares') {
      cargarFamiliaresEmpleado(registro);
      return;
    }

    if (accion === 'eliminar') {
      modalEliminar.abrir({
        titulo: 'Confirmar baja de empleado',
        subtitulo: `${registro.codigo_empleado || ''} · ${registro.cedula || ''}`,
        contenidoHtml: `
          <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            ¿Deseas marcar como <strong>Inactivo</strong> a <strong>${escaparHtml(registro.nombre_completo)}</strong>?
            El trabajador permanecerá en el historial con estatus inactivo.
          </div>
        `,
        textoConfirmar: 'Desactivar empleado',
        alConfirmar: (dialogo) => {
          const btnConfirmar = dialogo.querySelector('[data-modal-confirmar]');
          const textoOriginal = btnConfirmar?.textContent || 'Desactivar empleado';

          if (btnConfirmar) {
            btnConfirmar.disabled = true;
            btnConfirmar.textContent = 'Procesando...';
          }

          Solicitud.delete(`/api/empleados/${registro.empleado_id}`)
            .then((res) => {
              if (res.status === 401) {
                window.location.href = '/autenticacion';
                return;
              }
              if (!res.ok || !res.data?.ok) {
                mostrarToast('error', res.message || 'No se pudo desactivar el trabajador.');
                return;
              }

              mostrarToast('success', res.data.message || 'Trabajador desactivado correctamente.');
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
