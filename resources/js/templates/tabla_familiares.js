import { crearBadge } from '/js/components/badge/crearBadge.js';
import { crearBotonAccion } from '/js/components/botones/crearBotonAccion.js';
import { crearGrupoBotones } from '/js/components/botones/crearGrupoBotones.js';
import { crearDropdownAcciones } from '/js/components/dropdown/crearDropdownAcciones.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

const renderNombreFamiliar = (valor, fila) => {
  const fechaHtml = fila.fecha_nacimiento
    ? `<span class="block text-xs text-zinc-500">Nac: ${formatearFecha(fila.fecha_nacimiento)}</span>`
    : '';
  return `<div class="min-w-44"><span class="font-semibold text-zinc-900">${valor || '—'}</span>${fechaHtml}</div>`;
};

// Recibe los datos de discapacidad y la fila del familiar; renderiza botón "Ver" o "Asignar".
const renderDiscapacidad = (discapacidad, fila) => {
  if (discapacidad && typeof discapacidad === 'object') {
    return crearBotonAccion({
      accion: 'ver-discapacidad',
      id: fila.familia_id,
      etiqueta: 'Ver',
      icono: 'accessibility',
      variante: 'marca',
      titulo: `Ver o actualizar discapacidad de ${fila.nombre_familiar || 'familiar'}`,
    });
  }

  return crearBotonAccion({
    accion: 'asignar-discapacidad',
    id: fila.familia_id,
    etiqueta: 'Asignar',
    icono: 'accessibility',
    variante: 'zinc',
    titulo: `Asignar discapacidad a ${fila.nombre_familiar || 'familiar'}`,
  });
};

// Función original preservada: recibe fila de familiar y genera el grupo de botones individuales.
export const renderAcciones = (fila) =>
  crearGrupoBotones([
    crearBotonAccion({
      accion: 'mostrar',
      id: fila.familia_id,
      etiqueta: 'Ver',
      icono: 'eye',
      variante: 'marca',
      titulo: `Ver ficha de ${fila.nombre_familiar || 'familiar'}`,
    }),
    crearBotonAccion({
      etiqueta: 'Editar',
      icono: 'pencil',
      variante: 'zinc',
      href: `/empleado/familiar/editar/${fila.familia_id}`,
      titulo: `Editar a ${fila.nombre_familiar || 'familiar'}`,
    }),
    crearBotonAccion({
      accion: 'eliminar',
      id: fila.familia_id,
      etiqueta: 'Eliminar',
      icono: 'trash-2',
      variante: 'red',
      titulo: `Eliminar vínculo de ${fila.nombre_familiar || 'familiar'}`,
    }),
  ]);

// Recibe fila de familiar; construye el menú desplegable unificado de acciones para la tabla.
export const renderAccionesDropdown = (fila) =>
  crearDropdownAcciones({
    etiqueta: 'Acciones',
    items: [
      {
        accion: 'mostrar',
        id: fila.familia_id,
        etiqueta: 'Ver',
        icono: 'eye',
        variante: 'marca',
        titulo: `Ver ficha de ${fila.nombre_familiar || 'familiar'}`,
      },
      {
        etiqueta: 'Editar',
        icono: 'pencil',
        variante: 'zinc',
        href: `/empleado/familiar/editar/${fila.familia_id}`,
        titulo: `Editar a ${fila.nombre_familiar || 'familiar'}`,
      },
      {
        accion: 'eliminar',
        id: fila.familia_id,
        etiqueta: 'Eliminar',
        icono: 'trash-2',
        variante: 'red',
        separadorSuperior: true,
        titulo: `Eliminar vínculo de ${fila.nombre_familiar || 'familiar'}`,
      },
    ],
  });

// Devuelve la configuración de columnas DataTables incluyendo Discapacidad y Dropdown de acciones.
export const columnasTablaFamiliares = () => [
  {
    data: 'cedula_titular',
    name: 'cedula_titular',
    render: (v) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${v || '—'}</span>`,
  },
  {
    data: 'empleado_titular',
    name: 'empleado_titular',
    render: (v) => `<span class="font-medium text-zinc-900">${v || '—'}</span>`,
  },
  {
    data: 'cedula_familiar',
    name: 'cedula_familiar',
    render: (v) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${v || '—'}</span>`,
  },
  { data: 'nombre_familiar', name: 'nombre_familiar', render: (v, _t, fila) => renderNombreFamiliar(v, fila) },
  { data: 'parentesco', name: 'parentesco', render: (v) => crearBadge(v || 'Sin definir', 'marca') },
  {
    data: 'edad',
    name: 'edad',
    className: 'text-center',
    render: (v) => (v !== null && v !== undefined ? `<span class="whitespace-nowrap">${v} años</span>` : '—'),
  },
  {
    data: 'genero',
    name: 'genero',
    className: 'text-center',
    render: (v) => `<span class="whitespace-nowrap">${v || '—'}</span>`,
  },
  {
    data: 'tomo_folio',
    name: 'tomo_folio',
    orderable: false,
    searchable: false,
    className: 'text-center',
    render: (v) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${v || '—'}</span>`,
  },
  {
    data: 'discapacidad',
    name: 'discapacidad',
    orderable: false,
    searchable: false,
    className: 'text-center',
    render: (v, _t, fila) => renderDiscapacidad(v, fila),
  },
  {
    data: null,
    orderable: false,
    searchable: false,
    className: 'text-right',
    render: (_d, _t, fila) => renderAccionesDropdown(fila),
  },
];
