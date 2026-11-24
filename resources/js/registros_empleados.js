import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { crearAlertaConfirmacion } from '/js/components/alerta/alerta.js';
import { Modal } from '/js/components/modal/Modal.js';
import {
  crearMarkupSelectPersonalizado,
  inicializarSelectsPersonalizados,
} from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import {
  columnasTablaEmpleados,
  renderTablaFamiliaresEmpleado,
} from '/js/templates/tabla_empleados.js';

const actualizarContadores = (resumen) => {
  if (!resumen) return;

  const totalEl = document.getElementById('resumen-total-empleados');
  const activosEl = document.getElementById('resumen-empleados-activos');
  const inactivosEl = document.getElementById('resumen-empleados-inactivos');

  if (totalEl && resumen.total !== undefined) totalEl.textContent = String(resumen.total);
  if (activosEl && resumen.activos !== undefined) activosEl.textContent = String(resumen.activos);
  if (inactivosEl && resumen.inactivos !== undefined) inactivosEl.textContent = String(resumen.inactivos);
};

const renderCargaFamiliares = () => `
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

const renderErrorFamiliares = (mensaje) => `
  <div class="flex flex-col items-center justify-center gap-4 py-10 text-center">
    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-red-50 text-red-600">
      <i data-lucide="alert-circle" class="h-5 w-5" aria-hidden="true"></i>
    </div>
    <div class="space-y-1">
      <p class="text-sm font-semibold text-zinc-900">No se pudo cargar la información</p>
      <p class="text-xs text-zinc-500">${mensaje}</p>
    </div>
    <button type="button" data-reintentar-familiares class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-zinc-800 cursor-pointer">
      <i data-lucide="refresh-cw" class="h-3.5 w-3.5" aria-hidden="true"></i>
      <span>Reintentar</span>
    </button>
  </div>
`;

const renderFormularioDiscapacidad = (catalogos) => {
  const hoy = new Date().toISOString().slice(0, 10);
  const enCincoAnios = new Date(Date.now() + 5 * 365 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10);

  const mapaTipos = {};
  Object.values(catalogos?.tipos || {}).forEach((nombre) => {
    mapaTipos[String(nombre)] = String(nombre);
  });

  const selectTipoHtml = crearMarkupSelectPersonalizado({
    id: 'disc-tipo',
    nombre: 'tipo_discapacidad',
    etiqueta: 'Tipo de discapacidad',
    opciones: mapaTipos,
    placeholder: 'Seleccionar tipo...',
  });

  const selectGradoHtml = crearMarkupSelectPersonalizado({
    id: 'disc-grado',
    nombre: 'grado_discapacidad_id',
    etiqueta: 'Grado de discapacidad *',
    opciones: catalogos?.grados || {},
    placeholder: 'Seleccionar grado...',
    requerido: true,
  });

  return `
    <form id="form-asignar-discapacidad" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm" enctype="multipart/form-data">
      <div>${selectTipoHtml}</div>
      <div>${selectGradoHtml}</div>
      <div>
        <label for="disc-carnet" class="block text-sm font-medium text-zinc-900 mb-1.5">N.° de Carnet CONAPDIS *</label>
        <input id="disc-carnet" name="numero_carnet" type="text" maxlength="30" required placeholder="Ej. CONAPDIS-123456" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2" />
      </div>
      <div>
        <label for="disc-entidad" class="block text-sm font-medium text-zinc-900 mb-1.5">Entidad emisora *</label>
        <input id="disc-entidad" name="entidad_emisora" type="text" maxlength="100" required value="CONAPDIS" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2" />
      </div>
      <div>
        <label for="disc-emision" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha de emisión</label>
        <input id="disc-emision" name="fecha_emision_carnet" type="date" value="${hoy}" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2" />
      </div>
      <div>
        <label for="disc-vencimiento" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha de vencimiento</label>
        <input id="disc-vencimiento" name="fecha_vencimiento_carnet" type="date" value="${enCincoAnios}" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2" />
      </div>
      <div class="md:col-span-2">
        <label for="disc-descripcion" class="block text-sm font-medium text-zinc-900 mb-1.5">Descripción / Observaciones</label>
        <textarea id="disc-descripcion" name="descripcion" rows="2" placeholder="Detalles médicos o indicaciones relevantes..." class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2"></textarea>
      </div>
      <div class="md:col-span-2">
        <label for="disc-archivo" class="block text-sm font-medium text-zinc-900 mb-1.5">Certificado / Carnet CONAPDIS (PDF o imagen, opcional)</label>
        <input id="disc-archivo" name="archivo" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-xs text-zinc-700 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-zinc-800 cursor-pointer" />
      </div>
    </form>
  `;
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();

  const filtroEstatus = document.getElementById('filtro-estatus-empleado');
  const filtroActivo = document.getElementById('filtro-activo-empleado');
  const modalFamiliares = new Modal('#modal-familiares-empleado');
  const modalEstado = new Modal('#modal-eliminar-empleado');
  const modalDiscapacidad = new Modal('#modal-discapacidad-empleado');

  let ultimaFilaFamiliares = null;
  let catalogosDiscapacidad = null;
  let tabla = null;

  const leerFiltros = () => {
    const filtros = {};
    if (filtroEstatus?.value && filtroEstatus.value !== 'todos') filtros.estatus_id = filtroEstatus.value;
    if (filtroActivo?.value && filtroActivo.value !== 'todos') filtros.estatus_actual = filtroActivo.value;
    return filtros;
  };

  const cargarCatalogosDiscapacidad = async () => {
    if (catalogosDiscapacidad) return catalogosDiscapacidad;

    const res = await Solicitud.get('/api/discapacidad/catalogos');
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudieron cargar los catálogos de discapacidad.');
      return { tipos: {}, grados: {} };
    }

    catalogosDiscapacidad = res.data.data || { tipos: {}, grados: {} };
    return catalogosDiscapacidad;
  };

  const verFamiliares = async (empleado) => {
    ultimaFilaFamiliares = empleado;

    modalFamiliares.abrir({
      titulo: `Carga familiar · ${empleado.nombre_completo || 'Trabajador'}`,
      subtitulo: `${empleado.codigo_empleado || ''} · ${empleado.cedula || ''}${empleado.cargo ? ` · ${empleado.cargo}` : ''}`,
      contenidoHtml: renderCargaFamiliares(),
    });

    const res = await Solicitud.get(`/api/empleados/${empleado.empleado_id}/familiares`);
    if (!res.ok || !res.data?.ok) {
      modalFamiliares.establecerContenido(renderErrorFamiliares(res.message || 'No se pudo consultar la carga familiar.'));
      return;
    }

    modalFamiliares.establecerContenido(renderTablaFamiliaresEmpleado(res.data, empleado.empleado_id));
  };

  const confirmarCambioEstado = (empleado, activar) => {
    modalEstado.abrir({
      titulo: activar ? 'Confirmar activación de empleado' : 'Confirmar baja de empleado',
      subtitulo: `${empleado.codigo_empleado || ''} · ${empleado.cedula || ''}`,
      contenidoHtml: crearAlertaConfirmacion({
        variante: activar ? 'green' : 'red',
        icono: activar ? 'user-check' : '',
        mensajeHtml: activar
          ? `¿Estás seguro de que deseas activar a <strong>${empleado.nombre_completo}</strong>? El trabajador volverá a figurar como <strong>Activo</strong> en nómina.`
          : `¿Deseas marcar como <strong>Inactivo</strong> a <strong>${empleado.nombre_completo}</strong>? El trabajador permanecerá en el historial con estatus inactivo.`,
      }),
      textoConfirmar: activar ? 'Activar empleado' : 'Desactivar empleado',
      varianteConfirmar: activar ? 'green' : 'red',
      alConfirmar: async () => {
        const res = activar
          ? await Solicitud.post('/api/empleados/activar', { empleado_id: empleado.empleado_id })
          : await Solicitud.delete(`/api/empleados/${empleado.empleado_id}`);

        if (!res.ok || !res.data?.ok) {
          mostrarToast('error', res.message || 'No se pudo actualizar el estado del trabajador.');
          return false;
        }

        mostrarToast('success', res.data.message || 'Estado actualizado correctamente.');
        tabla?.recargar(true);
        return true;
      },
    });
  };

  const abrirModalDiscapacidad = async (empleado) => {
    const catalogos = await cargarCatalogosDiscapacidad();

    modalDiscapacidad.abrir({
      titulo: `Asignar discapacidad · ${empleado.nombre_completo || 'Trabajador'}`,
      subtitulo: `${empleado.codigo_empleado || ''} · ${empleado.cedula || ''}`,
      contenidoHtml: renderFormularioDiscapacidad(catalogos),
      textoConfirmar: 'Guardar discapacidad',
      textoCargando: 'Guardando...',
      alConfirmar: async (dialogo) => {
        const form = dialogo.querySelector('#form-asignar-discapacidad');
        const grado = form?.elements?.grado_discapacidad_id?.value;
        const carnet = form?.elements?.numero_carnet?.value?.trim();
        const entidad = form?.elements?.entidad_emisora?.value?.trim();

        if (!grado || !carnet || !entidad) {
          mostrarToast('error', 'Completa el grado de discapacidad, número de carnet y entidad emisora.');
          return false;
        }

        const res = await Solicitud.post(`/api/empleados/${empleado.empleado_id}/discapacidad`, new FormData(form));
        if (!res.ok || !res.data?.ok) {
          mostrarToast('error', res.message || 'No se pudo asignar la discapacidad.');
          return false;
        }

        mostrarToast('success', res.data.message || 'Discapacidad asignada correctamente.');
        tabla?.recargar(true);
        return true;
      },
    });
  };

  tabla = new GestorDataTable('#tabla-empleados', {
    url: '/api/empleados/datatable',
    ordenInicial: [[2, 'asc']],
    obtenerFiltros: leerFiltros,
    alCargar: (json) => actualizarContadores(json?.resumen),
    columnas: columnasTablaEmpleados(),
    alAccionar: (accion, empleado) => {
      if (accion === 'familiares') return verFamiliares(empleado);
      if (accion === 'asignar-discapacidad') return abrirModalDiscapacidad(empleado);
      if (accion === 'activar') return confirmarCambioEstado(empleado, true);
      if (accion === 'eliminar') return confirmarCambioEstado(empleado, false);
    },
  });

  filtroEstatus?.addEventListener('change', () => tabla.recargar(false));
  filtroActivo?.addEventListener('change', () => tabla.recargar(false));

  document.getElementById('modal-familiares-empleado')?.addEventListener('click', (evento) => {
    if (evento.target.closest('[data-reintentar-familiares]') && ultimaFilaFamiliares) {
      verFamiliares(ultimaFilaFamiliares);
    }
  });
});
