/**
 * registros_familiares.js
 * Controlador de la vista de familiares: filtros, tabla DataTables y modales.
 */

import { ControladorModal } from '/js/class/ControladorModal.js';
import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { inicializarSelectsPersonalizados } from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';
import { columnasTablaFamiliares } from '/js/templates/tabla_familiares.js';
import { crearAlertaConfirmacion, crearListaDetalle } from '/js/templates/template_ui.js';
import { escaparHtml, formatearFecha } from '/js/utils/dom.js';

const abrirDetalleFamiliar = (modalDetalle, registro) => {
  const edadTexto =
    registro.edad !== null && registro.edad !== undefined ? ` (${registro.edad} años)` : '';

  modalDetalle.abrir({
    titulo: registro.nombre_familiar || 'Familiar',
    subtitulo: `${registro.parentesco || ''} de ${registro.empleado_titular || ''}`,
    contenidoHtml: crearListaDetalle([
      {
        etiqueta: 'Empleado titular',
        valorHtml: `<span class="font-semibold">${escaparHtml(registro.empleado_titular || '—')}</span>`,
        subvalorHtml: escaparHtml(registro.cedula_titular || '—'),
      },
      {
        etiqueta: 'Cédula del familiar',
        valorHtml: `<span class="font-semibold">${escaparHtml(registro.cedula_familiar || '—')}</span>`,
      },
      {
        etiqueta: 'Fecha de nacimiento y edad',
        valorHtml: `${escaparHtml(formatearFecha(registro.fecha_nacimiento))}${escaparHtml(edadTexto)}`,
      },
      {
        etiqueta: 'Datos de acta y género',
        valorHtml: `Tomo ${escaparHtml(registro.tomo)} · Folio ${escaparHtml(registro.folio)} · ${escaparHtml(registro.genero || '—')}`,
      },
    ]),
  });
};

const confirmarEliminarFamiliar = ({ modalEliminar, registro, recargarTabla }) => {
  modalEliminar.abrir({
    titulo: 'Eliminar carga familiar',
    subtitulo: `${registro.parentesco || ''} · ${registro.cedula_familiar || ''}`,
    contenidoHtml: crearAlertaConfirmacion({
      variante: 'red',
      mensajeHtml: `¿Deseas eliminar el vínculo familiar de <strong>${escaparHtml(registro.nombre_familiar)}</strong>?`,
    }),
    textoConfirmar: 'Eliminar registro',
    textoCargando: 'Eliminando...',
    alConfirmar: async () => {
      const res = await Solicitud.delete(`/api/familiares/${registro.familia_id}`);
      if (!res.ok || !res.data?.ok) {
        mostrarToast('error', res.message || 'No se pudo eliminar el registro familiar.');
        return false;
      }

      mostrarToast('success', res.data.message || 'Carga familiar eliminada correctamente.');
      recargarTabla();
      return true;
    },
  });
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();

  const filtroParentesco = document.getElementById('filtro-parentesco');
  const modalDetalle = new ControladorModal('#modal-detalle-familiar');
  const modalEliminar = new ControladorModal('#modal-eliminar-familiar');
  let tabla = null;

  const obtenerFiltros = () =>
    filtroParentesco && filtroParentesco.value !== 'todos'
      ? { parentesco_id: filtroParentesco.value }
      : {};

  tabla = new GestorDataTable('#tabla-familiares', {
    url: '/api/familiares/datatable',
    ordenInicial: [[3, 'asc']],
    obtenerFiltros,
    columnas: columnasTablaFamiliares(),
    alAccionar: (accion, registro) => {
      if (accion === 'mostrar') return abrirDetalleFamiliar(modalDetalle, registro);
      if (accion === 'eliminar') {
        confirmarEliminarFamiliar({
          modalEliminar,
          registro,
          recargarTabla: () => tabla?.recargar(true),
        });
      }
    },
  });

  if (!tabla) return;
  filtroParentesco?.addEventListener('change', () => tabla.recargar(false));
});
