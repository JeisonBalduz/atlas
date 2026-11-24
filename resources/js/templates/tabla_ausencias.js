import { crearBadge } from '/js/components/badge/crearBadge.js';
import { crearBotonAccion } from '/js/components/botones/crearBotonAccion.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

export const columnasTablaAusencias = () => [
  {
    data: 'cedula',
    name: 'cedula',
    render: (v) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${v || '—'}</span>`,
  },
  {
    data: 'nombre_completo',
    name: 'nombre_completo',
    render: (v) => `<span class="font-medium text-zinc-900">${v || '—'}</span>`,
  },
  {
    data: 'tipo_texto',
    name: 'tipo_ausencia_descripcion',
    className: 'text-center',
    render: (v) => crearBadge(v || 'Permiso', v === 'Permiso' ? 'sky' : 'yellow'),
  },
  {
    data: 'detalle',
    name: 'detalle',
    orderable: false,
    render: (v) => v || '—',
  },
  {
    data: 'fecha_inicio',
    name: 'fecha_inicio',
    render: (v) => `<span class="whitespace-nowrap">${formatearFecha(v)}</span>`,
  },
  {
    data: 'fecha_fin',
    name: 'fecha_fin',
    render: (v) => `<span class="whitespace-nowrap">${formatearFecha(v)}</span>`,
  },
  {
    data: 'dias_ausencia',
    name: 'dias_ausencia',
    className: 'text-center',
    render: (v) => `<span class="font-semibold text-zinc-900">${v ?? 0}</span>`,
  },
  {
    data: null,
    orderable: false,
    searchable: false,
    className: 'text-right',
    render: (_d, _t, fila) =>
      crearBotonAccion({ accion: 'mostrar', id: fila.ausencia_id, etiqueta: 'Ver', icono: 'eye', variante: 'marca' }),
  },
];
