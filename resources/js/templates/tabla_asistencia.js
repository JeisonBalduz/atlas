import { crearBadge } from '/js/components/badge/crearBadge.js';
import { crearBotonAccion } from '/js/components/botones/crearBotonAccion.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

export const columnasTablaAsistencia = () => [
  {
    data: 'cedula',
    render: (v) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${v || '—'}</span>`,
  },
  {
    data: 'nombre_completo',
    render: (v) => `<span class="font-medium text-zinc-900">${v || '—'}</span>`,
  },
  {
    data: 'departamento',
    render: (v) => `<span class="text-zinc-600">${v || '—'}</span>`,
  },
  {
    data: 'turno',
    className: 'text-center',
    render: (v) => crearBadge(v || 'Diurno', 'sky'),
  },
  {
    data: 'fecha',
    render: (v) => `<span class="whitespace-nowrap">${formatearFecha(v)}</span>`,
  },
  {
    data: 'hora_entrada',
    className: 'text-center',
    render: (v) => `<span class="font-semibold text-zinc-900">${v || '—'}</span>`,
  },
  {
    data: 'hora_salida',
    className: 'text-center',
    render: (v) =>
      v ? `<span class="font-semibold text-zinc-900">${v}</span>` : '<span class="text-xs text-zinc-400">Pendiente</span>',
  },
  {
    data: 'estado_jornada',
    className: 'text-center',
    render: (v) => crearBadge(v || 'En jornada', v === 'Completada' ? 'green' : 'yellow'),
  },
  {
    data: null,
    orderable: false,
    searchable: false,
    className: 'text-right',
    render: (_d, _t, fila) =>
      crearBotonAccion({
        accion: 'mostrar',
        id: fila.dtasistencia_id,
        etiqueta: 'Ver',
        icono: 'eye',
        variante: 'marca',
      }),
  },
];
