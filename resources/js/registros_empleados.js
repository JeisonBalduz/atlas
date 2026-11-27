import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import {
  crearAlertaConfirmacion,
  notificarErrorFormulario,
  notificarExitoFormulario,
} from '/js/components/alerta/alerta.js';
import { crearBadge } from '/js/components/badge/crearBadge.js';
import { inicializarDropdownsAcciones } from '/js/components/dropdown/crearDropdownAcciones.js';
import { abrirModalFamiliaresEmpleado } from '/js/components/modal/abrirModalFamiliaresEmpleado.js';
import { Modal } from '/js/components/modal/Modal.js';
import {
  crearMarkupSelectPersonalizado,
  inicializarSelectsPersonalizados,
} from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import { columnasTablaEmpleados } from '/js/templates/tabla_empleados.js';
import {
  fechaIsoAVisual,
  inicializarRangoFechasDobleFormato,
  obtenerFechaHoyLocalIso,
  parsearFechaFlexible,
} from '/js/utils/formatearFecha.js';

const actualizarContadores = (resumen) => {
  if (!resumen) return;

  const totalEl = document.getElementById('resumen-total-empleados');
  const activosEl = document.getElementById('resumen-empleados-activos');
  const inactivosEl = document.getElementById('resumen-empleados-inactivos');

  if (totalEl && resumen.total !== undefined) totalEl.textContent = String(resumen.total);
  if (activosEl && resumen.activos !== undefined) activosEl.textContent = String(resumen.activos);
  if (inactivosEl && resumen.inactivos !== undefined) inactivosEl.textContent = String(resumen.inactivos);
};

// Recibe catálogos de discapacidad y registro opcional existente; construye el formulario del modal precargado.
const renderFormularioDiscapacidad = (catalogos, existente = null) => {
  const hoy = obtenerFechaHoyLocalIso();
  const baseHoy = parsearFechaFlexible(hoy);
  const enCincoAnios = baseHoy
    ? `${String(baseHoy.anio + 5).padStart(4, '0')}-${String(baseHoy.mes).padStart(2, '0')}-${String(baseHoy.dia).padStart(2, '0')}`
    : hoy;
  const emisionIso = parsearFechaFlexible(existente?.fecha_emision_carnet)?.iso || hoy;
  const vencimientoIso = parsearFechaFlexible(existente?.fecha_vencimiento_carnet)?.iso || enCincoAnios;

  const mapaTipos = {};
  Object.values(catalogos?.tipos || {}).forEach((nombre) => {
    mapaTipos[String(nombre)] = String(nombre);
  });

  const selectTipoHtml = crearMarkupSelectPersonalizado({
    id: 'disc-tipo',
    nombre: 'tipo_discapacidad',
    etiqueta: 'Tipo de discapacidad',
    opciones: mapaTipos,
    valorSeleccionado: existente?.tipo || '',
    placeholder: 'Seleccionar tipo...',
  });

  const selectGradoHtml = crearMarkupSelectPersonalizado({
    id: 'disc-grado',
    nombre: 'grado_discapacidad_id',
    etiqueta: 'Grado de discapacidad *',
    opciones: catalogos?.grados || {},
    valorSeleccionado: existente?.grado_discapacidad_id ? String(existente.grado_discapacidad_id) : '',
    placeholder: 'Seleccionar grado...',
    requerido: true,
  });

  const docActualHtml = existente?.documento?.url_descarga
    ? `
      <div class="mb-2 flex items-center justify-between gap-2 rounded-xl border border-green-200 bg-green-50 px-3 py-2 text-xs text-green-800">
        <span class="truncate font-medium">Documento vigente: ${existente.documento.nombre_original || 'Soporte cargado'}</span>
        <a href="${existente.documento.url_descarga}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 font-semibold text-marca hover:underline whitespace-nowrap">
          <i data-lucide="external-link" class="h-3.5 w-3.5" aria-hidden="true"></i>
          <span>Ver</span>
        </a>
      </div>`
    : '';

  return `
    <form id="form-asignar-discapacidad" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm" enctype="multipart/form-data">
      <div>${selectTipoHtml}</div>
      <div>${selectGradoHtml}</div>
      <div>
        <label for="disc-carnet" class="block text-sm font-medium text-zinc-900 mb-1.5">N.° de Carnet CONAPDIS *</label>
        <input id="disc-carnet" name="numero_carnet" type="text" maxlength="30" required value="${existente?.numero_carnet || ''}" placeholder="Ej. CONAPDIS-123456" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2" />
      </div>
      <div>
        <label for="disc-entidad" class="block text-sm font-medium text-zinc-900 mb-1.5">Entidad emisora *</label>
        <input id="disc-entidad" name="entidad_emisora" type="text" maxlength="100" required value="${existente?.entidad_emisora || 'CONAPDIS'}" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2" />
      </div>
      <div>
        <label for="disc-emision" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha de emisión</label>
        <div class="relative">
          <input id="disc-emision" type="text" value="${fechaIsoAVisual(emisionIso)}" placeholder="dd-mm-yyyy" autocomplete="off" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2" />
          <input type="hidden" id="disc-emision-iso" name="fecha_emision_carnet" value="${emisionIso}" />
        </div>
      </div>
      <div>
        <label for="disc-vencimiento" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha de vencimiento</label>
        <div class="relative">
          <input id="disc-vencimiento" type="text" value="${fechaIsoAVisual(vencimientoIso)}" placeholder="dd-mm-yyyy" autocomplete="off" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2" />
          <input type="hidden" id="disc-vencimiento-iso" name="fecha_vencimiento_carnet" value="${vencimientoIso}" />
        </div>
      </div>
      <div class="md:col-span-2">
        <label for="disc-descripcion" class="block text-sm font-medium text-zinc-900 mb-1.5">Descripción / Observaciones</label>
        <textarea id="disc-descripcion" name="descripcion" rows="2" placeholder="Detalles médicos o indicaciones relevantes..." class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2">${existente?.descripcion || ''}</textarea>
      </div>
      <div class="md:col-span-2">
        ${docActualHtml}
        <label for="disc-archivo" class="block text-sm font-medium text-zinc-900 mb-1.5">Certificado CONAPDIS / Informe Médico IVSS (PDF o imagen, opcional)</label>
        <input id="disc-archivo" name="archivo" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-xs text-zinc-700 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-zinc-800 cursor-pointer" />
      </div>
    </form>
  `;
};

// Recibe el arreglo de teléfonos del empleado; construye la lista compacta para el modal de Teléfonos.
const renderListaTelefonosModal = (telefonos = []) => {
  if (!Array.isArray(telefonos) || telefonos.length === 0) {
    return `
      <div class="flex flex-col items-center justify-center gap-2.5 py-6 text-center">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-zinc-500">
          <i data-lucide="phone-off" class="h-5 w-5" aria-hidden="true"></i>
        </div>
        <p class="text-sm font-medium text-zinc-900">Sin teléfonos registrados</p>
        <p class="text-xs text-zinc-500">Puedes registrar líneas de contacto desde la vista de edición del trabajador.</p>
      </div>
    `;
  }

  return `
    <ul class="space-y-2.5">
      ${telefonos
        .map(
          (tel) => `
            <li class="flex items-center justify-between gap-3 rounded-xl border ${tel.emergencia ? 'border-marca-200 bg-marca-50/50' : 'border-slate-200 bg-slate-50'} px-4 py-3">
              <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg ${tel.emergencia ? 'bg-marca text-white' : 'bg-white border border-slate-200 text-zinc-700'}">
                  <i data-lucide="phone" class="h-4 w-4" aria-hidden="true"></i>
                </span>
                <span class="text-sm font-semibold text-zinc-900 tracking-wide">${tel.numero_telefono}</span>
              </div>
              ${crearBadge(tel.emergencia ? 'Emergencia' : 'Personal', tel.emergencia ? 'marca' : 'neutro')}
            </li>
          `
        )
        .join('')}
    </ul>
  `;
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();
  inicializarDropdownsAcciones();

  const filtroEstatus = document.getElementById('filtro-estatus-empleado');
  const filtroActivo = document.getElementById('filtro-activo-empleado');
  const modalFamiliares = new Modal('#modal-familiares-empleado');
  const modalTelefonos = new Modal('#modal-telefonos-empleado');
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
    await abrirModalFamiliaresEmpleado(modalFamiliares, empleado);
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

  // Recibe el registro de empleado; despliega el modal compacto con sus teléfonos de contacto.
  const verTelefonos = (empleado) => {
    modalTelefonos.abrir({
      titulo: `Teléfonos · ${empleado.nombre_completo || 'Trabajador'}`,
      subtitulo: `${empleado.codigo_empleado || ''} · ${empleado.cedula || ''}`,
      contenidoHtml: renderListaTelefonosModal(empleado.telefonos || []),
    });
  };

  // Recibe el empleado; abre el modal de discapacidad vacío (Asignar) o precargado (Ver/Actualizar).
  const abrirModalDiscapacidad = async (empleado) => {
    const catalogos = await cargarCatalogosDiscapacidad();
    const existente = empleado.discapacidad && typeof empleado.discapacidad === 'object' ? empleado.discapacidad : null;

    modalDiscapacidad.abrir({
      titulo: existente
        ? `Discapacidad registrada · ${empleado.nombre_completo || 'Trabajador'}`
        : `Asignar discapacidad · ${empleado.nombre_completo || 'Trabajador'}`,
      subtitulo: `${empleado.codigo_empleado || ''} · ${empleado.cedula || ''}`,
      contenidoHtml: renderFormularioDiscapacidad(catalogos, existente),
      textoConfirmar: existente ? 'Actualizar discapacidad' : 'Guardar discapacidad',
      textoCargando: 'Guardando...',
      alConfirmar: async (dialogo) => {
        const form = dialogo.querySelector('#form-asignar-discapacidad');
        const grado = form?.elements?.grado_discapacidad_id?.value;
        const carnet = form?.elements?.numero_carnet?.value?.trim();
        const entidad = form?.elements?.entidad_emisora?.value?.trim();
        const emision = form?.elements?.fecha_emision_carnet?.value?.trim();
        const vencimiento = form?.elements?.fecha_vencimiento_carnet?.value?.trim();

        if (!grado || !carnet || !entidad) {
          notificarErrorFormulario(form, 'Completa el grado de discapacidad, número de carnet y entidad emisora.');
          return false;
        }
        if (emision && vencimiento && vencimiento < emision) {
          notificarErrorFormulario(form, 'La fecha de vencimiento del carnet no puede ser menor que la fecha de emisión.');
          return false;
        }

        const res = await Solicitud.post(`/api/empleados/${empleado.empleado_id}/discapacidad`, new FormData(form));
        if (!res.ok || !res.data?.ok) {
          notificarErrorFormulario(form, res.message || 'No se pudo asignar la discapacidad.');
          return false;
        }

        notificarExitoFormulario(form, res.data.message || 'Discapacidad guardada correctamente.');
        tabla?.recargar(true);
        return true;
      },
    });

    if (modalDiscapacidad.dialogo) {
      inicializarRangoFechasDobleFormato('#disc-emision', '#disc-vencimiento');
    }
  };

  tabla = new GestorDataTable('#tabla-empleados', {
    url: '/api/empleados/datatable',
    ordenInicial: [[2, 'asc']],
    obtenerFiltros: leerFiltros,
    alCargar: (json) => actualizarContadores(json?.resumen),
    columnas: columnasTablaEmpleados(),
    alAccionar: (accion, empleado) => {
      if (accion === 'familiares') return verFamiliares(empleado);
      if (accion === 'telefonos') return verTelefonos(empleado);
      if (accion === 'asignar-discapacidad' || accion === 'ver-discapacidad') {
        return abrirModalDiscapacidad(empleado);
      }
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
