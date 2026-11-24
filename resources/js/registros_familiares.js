import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { crearAlertaConfirmacion } from '/js/components/alerta/alerta.js';
import { crearListaDetalle, Modal } from '/js/components/modal/Modal.js';
import { inicializarSelectsPersonalizados } from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import { columnasTablaFamiliares } from '/js/templates/tabla_familiares.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

const abrirDetalleFamiliar = (modalDetalle, registro) => {
  const edadTexto =
    registro.edad !== null && registro.edad !== undefined ? ` (${registro.edad} años)` : '';

  modalDetalle.abrir({
    titulo: registro.nombre_familiar || 'Familiar',
    subtitulo: `${registro.parentesco || ''} de ${registro.empleado_titular || ''}`,
    contenidoHtml: crearListaDetalle([
      {
        etiqueta: 'Empleado titular',
        valorHtml: `<span class="font-semibold">${registro.empleado_titular || '—'}</span>`,
        subvalorHtml: registro.cedula_titular || '—',
      },
      {
        etiqueta: 'Cédula del familiar',
        valorHtml: `<span class="font-semibold">${registro.cedula_familiar || '—'}</span>`,
      },
      {
        etiqueta: 'Fecha de nacimiento y edad',
        valorHtml: `${formatearFecha(registro.fecha_nacimiento)}${edadTexto}`,
      },
      {
        etiqueta: 'Datos de acta y género',
        valorHtml: `Tomo ${registro.tomo} · Folio ${registro.folio} · ${registro.genero || '—'}`,
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
      mensajeHtml: `¿Deseas eliminar el vínculo familiar de <strong>${registro.nombre_familiar}</strong>?`,
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
  const modalDetalle = new Modal('#modal-detalle-familiar');
  const modalEliminar = new Modal('#modal-eliminar-familiar');

  const obtenerFiltros = () =>
    filtroParentesco && filtroParentesco.value !== 'todos'
      ? { parentesco_id: filtroParentesco.value }
      : {};

  const tabla = new GestorDataTable('#tabla-familiares', {
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
          recargarTabla: () => tabla.recargar(true),
        });
      }
    },
  });

  filtroParentesco?.addEventListener('change', () => tabla.recargar(false));
});
