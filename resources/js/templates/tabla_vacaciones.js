import { crearBadge } from '/js/components/badge/crearBadge.js';
import { crearBotonAccion } from '/js/components/botones/crearBotonAccion.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

export const varianteEstatusVacaciones = (estatus) => {
  const mapa = {
    Pendiente: 'yellow',
    'Aprobada por Talento Humano': 'green',
    'Aprobada por Gerencia': 'green',
    'Negada por Talento Humano': 'red',
    'Negada por Gerencia': 'red',
    Reposo: 'sky',
    Terminada: 'marca',
  };
  return mapa[estatus] || 'neutro';
};

export const columnasTablaVacaciones = () => [
  {
    data: 'nombre_completo',
    render: (v) => `<span class="font-medium text-zinc-900">${v || '—'}</span>`,
  },
  {
    data: 'cedula',
    render: (v) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${v || '—'}</span>`,
  },
  {
    data: 'dias_solicitado',
    className: 'text-center',
    render: (v) => `<span class="font-semibold text-zinc-900">${v ?? 0}</span>`,
  },
  {
    data: 'fecha_inicio',
    render: (v) => `<span class="whitespace-nowrap">${formatearFecha(v)}</span>`,
  },
  {
    data: 'fecha_final',
    render: (v) => `<span class="whitespace-nowrap">${formatearFecha(v)}</span>`,
  },
  {
    data: 'estatus_descripcion',
    className: 'text-center',
    render: (v) => crearBadge(v || 'Pendiente', varianteEstatusVacaciones(v)),
  },
  {
    data: null,
    orderable: false,
    searchable: false,
    className: 'text-right',
    render: (_d, _t, fila) =>
      crearBotonAccion({
        accion: 'mostrar',
        id: fila.solicitud_vacaciones_id,
        etiqueta: 'Ver',
        icono: 'eye',
        variante: 'marca',
      }),
  },
];
