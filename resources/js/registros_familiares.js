import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import {
  crearAlertaConfirmacion,
  notificarErrorFormulario,
  notificarExitoFormulario,
} from '/js/components/alerta/alerta.js';
import { inicializarRangoFechas } from '/js/components/calendario/calendario.js';
import { inicializarDropdownsAcciones } from '/js/components/dropdown/crearDropdownAcciones.js';
import { crearListaDetalle, Modal } from '/js/components/modal/Modal.js';
import {
  crearMarkupSelectPersonalizado,
  inicializarSelectsPersonalizados,
} from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import { columnasTablaFamiliares } from '/js/templates/tabla_familiares.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

// Recibe catálogos de discapacidad y registro existente opcional; construye el formulario modal de discapacidad familiar.
const renderFormularioDiscapacidadFamiliar = (catalogos, existente = null) => {
  const hoy = new Date().toISOString().slice(0, 10);
  const enCincoAnios = new Date(Date.now() + 5 * 365 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10);

  const mapaTipos = {};
  Object.values(catalogos?.tipos || {}).forEach((nombre) => {
    mapaTipos[String(nombre)] = String(nombre);
  });

  const selectTipoHtml = crearMarkupSelectPersonalizado({
    id: 'disc-fam-tipo',
    nombre: 'tipo_discapacidad',
    etiqueta: 'Tipo de discapacidad',
    opciones: mapaTipos,
    valorSeleccionado: existente?.tipo || '',
    placeholder: 'Seleccionar tipo...',
  });

  const selectGradoHtml = crearMarkupSelectPersonalizado({
    id: 'disc-fam-grado',
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
    <form id="form-asignar-discapacidad-familiar" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm" enctype="multipart/form-data">
      <div>${selectTipoHtml}</div>
      <div>${selectGradoHtml}</div>
      <div>
        <label for="disc-fam-carnet" class="block text-sm font-medium text-zinc-900 mb-1.5">N.° de Carnet CONAPDIS *</label>
        <input id="disc-fam-carnet" name="numero_carnet" type="text" maxlength="30" required value="${existente?.numero_carnet || ''}" placeholder="Ej. CONAPDIS-123456" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2" />
      </div>
      <div>
        <label for="disc-fam-entidad" class="block text-sm font-medium text-zinc-900 mb-1.5">Entidad emisora *</label>
        <input id="disc-fam-entidad" name="entidad_emisora" type="text" maxlength="100" required value="${existente?.entidad_emisora || 'CONAPDIS'}" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2" />
      </div>
      <div>
        <label for="disc-fam-emision" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha de emisión</label>
        <input id="disc-fam-emision" name="fecha_emision_carnet" type="date" value="${existente?.fecha_emision_carnet || hoy}" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2" />
      </div>
      <div>
        <label for="disc-fam-vencimiento" class="block text-sm font-medium text-zinc-900 mb-1.5">Fecha de vencimiento</label>
        <input id="disc-fam-vencimiento" name="fecha_vencimiento_carnet" type="date" value="${existente?.fecha_vencimiento_carnet || enCincoAnios}" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2" />
      </div>
      <div class="md:col-span-2">
        <label for="disc-fam-descripcion" class="block text-sm font-medium text-zinc-900 mb-1.5">Descripción / Observaciones</label>
        <textarea id="disc-fam-descripcion" name="descripcion" rows="2" placeholder="Detalles médicos o indicaciones relevantes..." class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2">${existente?.descripcion || ''}</textarea>
      </div>
      <div class="md:col-span-2">
        ${docActualHtml}
        <label for="disc-fam-archivo" class="block text-sm font-medium text-zinc-900 mb-1.5">Certificado CONAPDIS / Informe Médico IVSS (PDF o imagen, opcional)</label>
        <input id="disc-fam-archivo" name="archivo" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-xs text-zinc-700 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-zinc-800 cursor-pointer" />
      </div>
    </form>
  `;
};

const abrirDetalleFamiliar = (modalDetalle, registro) => {
  const edadTexto =
    registro.edad !== null && registro.edad !== undefined ? ` (${registro.edad} años)` : '';
  const actaTexto =
    Number(registro.tomo || 0) > 0 || Number(registro.folio || 0) > 0
      ? `Tomo ${registro.tomo} · Folio ${registro.folio}`
      : 'Acta no requerida';

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
        valorHtml: `${actaTexto} · ${registro.genero || '—'}`,
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
    alConfirmar: async (dlg) => {
      const res = await Solicitud.delete(`/api/familiares/${registro.familia_id}`);
      if (!res.ok || !res.data?.ok) {
        const msg = res.message || 'No se pudo eliminar el registro familiar.';
        const cuerpo = dlg.querySelector('[data-modal-cuerpo]');
        if (cuerpo) {
          cuerpo.innerHTML = crearAlertaConfirmacion({
            variante: 'red',
            icono: 'shield-alert',
            mensajeHtml: `<strong>Eliminación bloqueada:</strong> ${msg}`,
          });
          window.lucide?.createIcons();
        }
        mostrarToast('error', msg);
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
  inicializarDropdownsAcciones();

  const filtroParentesco = document.getElementById('filtro-parentesco');
  const modalDetalle = new Modal('#modal-detalle-familiar');
  const modalEliminar = new Modal('#modal-eliminar-familiar');
  const modalDiscapacidad = new Modal('#modal-discapacidad-familiar');

  let catalogosDiscapacidad = null;
  let tabla = null;

  // Consulta y cachea los catálogos de tipos y grados de discapacidad desde el backend.
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

  // Recibe el registro de familiar; despliega el modal para asignar o consultar/actualizar su discapacidad.
  const abrirModalDiscapacidadFamiliar = async (familiar) => {
    const catalogos = await cargarCatalogosDiscapacidad();
    const existente = familiar.discapacidad && typeof familiar.discapacidad === 'object' ? familiar.discapacidad : null;

    modalDiscapacidad.abrir({
      titulo: existente
        ? `Discapacidad registrada · ${familiar.nombre_familiar || 'Familiar'}`
        : `Asignar discapacidad · ${familiar.nombre_familiar || 'Familiar'}`,
      subtitulo: `${familiar.parentesco || ''} · ${familiar.cedula_familiar || ''}`,
      contenidoHtml: renderFormularioDiscapacidadFamiliar(catalogos, existente),
      textoConfirmar: existente ? 'Actualizar discapacidad' : 'Guardar discapacidad',
      textoCargando: 'Guardando...',
      alConfirmar: async (dialogo) => {
        const form = dialogo.querySelector('#form-asignar-discapacidad-familiar');
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

        const res = await Solicitud.post(`/api/familiares/${familiar.familia_id}/discapacidad`, new FormData(form));
        if (!res.ok || !res.data?.ok) {
          notificarErrorFormulario(form, res.message || 'No se pudo guardar la discapacidad del familiar.');
          return false;
        }

        notificarExitoFormulario(form, res.data.message || 'Discapacidad guardada correctamente.');
        tabla?.recargar(true);
        return true;
      },
    });

    if (modalDiscapacidad.dialogo) {
      inicializarRangoFechas('#disc-fam-emision', '#disc-fam-vencimiento');
    }
  };

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
      if (accion === 'asignar-discapacidad' || accion === 'ver-discapacidad') {
        return abrirModalDiscapacidadFamiliar(registro);
      }
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
