import { crearBadge, crearBotonAccion, crearGrupoBotones } from '/js/templates/template_ui.js';
import { escaparHtml, formatearFecha } from '/js/utils/dom.js';

const renderNombreFamiliar = (valor, fila) => {
  const fechaHtml = fila.fecha_nacimiento
    ? `<span class="block text-xs text-zinc-500">Nac: ${escaparHtml(formatearFecha(fila.fecha_nacimiento))}</span>`
    : '';
  return `<div class="min-w-44"><span class="font-semibold text-zinc-900">${escaparHtml(valor || '—')}</span>${fechaHtml}</div>`;
};

const renderAcciones = (fila) =>
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

export const columnasTablaFamiliares = () => [
  {
    data: 'cedula_titular',
    name: 'cedula_titular',
    render: (v) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${escaparHtml(v || '—')}</span>`,
  },
  {
    data: 'empleado_titular',
    name: 'empleado_titular',
    render: (v) => `<span class="font-medium text-zinc-900">${escaparHtml(v || '—')}</span>`,
  },
  {
    data: 'cedula_familiar',
    name: 'cedula_familiar',
    render: (v) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${escaparHtml(v || '—')}</span>`,
  },
  { data: 'nombre_familiar', name: 'nombre_familiar', render: (v, _t, fila) => renderNombreFamiliar(v, fila) },
  { data: 'parentesco', name: 'parentesco', render: (v) => crearBadge(v || 'Sin definir', 'marca') },
  {
    data: 'edad',
    name: 'edad',
    className: 'text-center',
    render: (v) => (v !== null && v !== undefined ? `<span class="whitespace-nowrap">${escaparHtml(v)} años</span>` : '—'),
  },
  {
    data: 'genero',
    name: 'genero',
    className: 'text-center',
    render: (v) => `<span class="whitespace-nowrap">${escaparHtml(v || '—')}</span>`,
  },
  {
    data: 'tomo_folio',
    name: 'tomo_folio',
    orderable: false,
    searchable: false,
    className: 'text-center',
    render: (v) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${escaparHtml(v || '—')}</span>`,
  },
  {
    data: null,
    orderable: false,
    searchable: false,
    className: 'text-right',
    render: (_d, _t, fila) => renderAcciones(fila),
  },
];
