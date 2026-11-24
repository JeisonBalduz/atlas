import { crearBadge } from '/js/components/badge/crearBadge.js';
import { crearBotonAccion } from '/js/components/botones/crearBotonAccion.js';
import { crearGrupoBotones } from '/js/components/botones/crearGrupoBotones.js';

export const columnasTablaRoles = () => [
  {
    data: 'rol',
    render: (v) => `<span class="font-semibold text-zinc-900">${v || '—'}</span>`,
  },
  {
    data: 'usuarios_count',
    className: 'text-center',
    render: (v) => crearBadge(`${v ?? 0}`, 'neutro'),
  },
  {
    data: 'permisos_count',
    className: 'text-center',
    render: (v) => crearBadge(`${v ?? 0}`, 'marca'),
  },
  {
    data: null,
    orderable: false,
    searchable: false,
    className: 'text-right',
    render: (_d, _t, fila) =>
      crearGrupoBotones([
        crearBotonAccion({
          accion: 'configurar-permisos',
          id: fila.rol_id,
          etiqueta: 'Permisos',
          icono: 'key-round',
          variante: 'marca',
        }),
        crearBotonAccion({
          accion: 'editar',
          id: fila.rol_id,
          etiqueta: 'Editar',
          icono: 'pencil',
          variante: 'zinc',
        }),
      ]),
  },
];
