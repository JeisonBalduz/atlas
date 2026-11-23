/**
 * registros_empleados.js
 * Controlador principal de la vista de empleados:
 * - Inicializa filtros, tabla DataTables y los 3 modales (familiares, estado y discapacidad).
 * - Carga los catálogos de discapacidad vía API (/api/discapacidad/catalogos).
 */

import { ControladorModal } from '/js/class/ControladorModal.js';
import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { inicializarSelectsPersonalizados } from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';
import {
  htmlAlertaEstadoEmpleado,
  htmlCargaFamiliares,
  htmlErrorFamiliares,
  htmlFormularioDiscapacidad,
  htmlTablaFamiliares,
} from '/js/templates/modales_empleados.js';
import { columnasTablaEmpleados } from '/js/templates/tabla_empleados.js';

/**
 * Actualiza los 3 contadores superiores (Total, Activos, Inactivos) cuando responde la tabla.
 */
const actualizarContadores = (resumen) => {
  if (!resumen) return;

  const totalEl = document.getElementById('resumen-total-empleados');
  const activosEl = document.getElementById('resumen-empleados-activos');
  const inactivosEl = document.getElementById('resumen-empleados-inactivos');

  if (totalEl && resumen.total !== undefined) totalEl.textContent = String(resumen.total);
  if (activosEl && resumen.activos !== undefined) activosEl.textContent = String(resumen.activos);
  if (inactivosEl && resumen.inactivos !== undefined) inactivosEl.textContent = String(resumen.inactivos);
};

document.addEventListener('DOMContentLoaded', () => {
  // 1. Inicializar los selects personalizados de los filtros
  inicializarSelectsPersonalizados();

  // 2. Referencias a los selects de filtro y creación de los 3 modales de la vista
  const filtroEstatus = document.getElementById('filtro-estatus-empleado');
  const filtroActivo = document.getElementById('filtro-activo-empleado');
  const modalFamiliares = new ControladorModal('#modal-familiares-empleado');
  const modalEstado = new ControladorModal('#modal-eliminar-empleado');
  const modalDiscapacidad = new ControladorModal('#modal-discapacidad-empleado');

  let ultimaFilaFamiliares = null;
  let catalogosDiscapacidad = null;
  let tabla = null;

  // Lee los valores actuales de los filtros para enviarlos al servidor
  const leerFiltros = () => {
    const filtros = {};
    if (filtroEstatus?.value && filtroEstatus.value !== 'todos') filtros.estatus_id = filtroEstatus.value;
    if (filtroActivo?.value && filtroActivo.value !== 'todos') filtros.estatus_actual = filtroActivo.value;
    return filtros;
  };

  // Descarga tipos y grados de discapacidad una sola vez desde la API
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

  // Abre el modal de familiares y consulta la carga familiar del empleado seleccionado
  const verFamiliares = async (empleado) => {
    ultimaFilaFamiliares = empleado;

    modalFamiliares.abrir({
      titulo: `Carga familiar · ${empleado.nombre_completo || 'Trabajador'}`,
      subtitulo: `${empleado.codigo_empleado || ''} · ${empleado.cedula || ''}${empleado.cargo ? ` · ${empleado.cargo}` : ''}`,
      contenidoHtml: htmlCargaFamiliares(),
    });

    const res = await Solicitud.get(`/api/empleados/${empleado.empleado_id}/familiares`);
    if (!res.ok || !res.data?.ok) {
      modalFamiliares.establecerContenido(htmlErrorFamiliares(res.message || 'No se pudo consultar la carga familiar.'));
      return;
    }

    modalFamiliares.establecerContenido(htmlTablaFamiliares(res.data, empleado.empleado_id));
  };

  // Abre el modal de confirmación para activar (true) o dar de baja (false) al empleado
  const confirmarCambioEstado = (empleado, activar) => {
    modalEstado.abrir({
      titulo: activar ? 'Confirmar activación de empleado' : 'Confirmar baja de empleado',
      subtitulo: `${empleado.codigo_empleado || ''} · ${empleado.cedula || ''}`,
      contenidoHtml: htmlAlertaEstadoEmpleado(empleado.nombre_completo, activar),
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

  // Carga los catálogos desde la API y abre el modal para registrar discapacidad
  const abrirModalDiscapacidad = async (empleado) => {
    const catalogos = await cargarCatalogosDiscapacidad();

    modalDiscapacidad.abrir({
      titulo: `Asignar discapacidad · ${empleado.nombre_completo || 'Trabajador'}`,
      subtitulo: `${empleado.codigo_empleado || ''} · ${empleado.cedula || ''}`,
      contenidoHtml: htmlFormularioDiscapacidad(catalogos),
      textoConfirmar: 'Guardar discapacidad',
      textoCargando: 'Guardando...',
      alConfirmar: async (dialogo) => {
        const form = dialogo.querySelector('#form-asignar-discapacidad');
        if (!form?.reportValidity()) return false;

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

  // 3. Inicializar DataTables conectado al servidor y conectar los botones de cada fila
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

  if (!tabla) return;

  // 4. Recargar tabla al cambiar los filtros y permitir reintentar dentro del modal de familiares
  filtroEstatus?.addEventListener('change', () => tabla.recargar(false));
  filtroActivo?.addEventListener('change', () => tabla.recargar(false));

  document.getElementById('modal-familiares-empleado')?.addEventListener('click', (evento) => {
    if (evento.target.closest('[data-reintentar-familiares]') && ultimaFilaFamiliares) {
      verFamiliares(ultimaFilaFamiliares);
    }
  });
});
