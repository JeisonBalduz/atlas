import { crearBadge } from '/js/components/badge/crearBadge.js';
import { crearBotonAccion } from '/js/components/botones/crearBotonAccion.js';
import { crearGrupoBotones } from '/js/components/botones/crearGrupoBotones.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

export const varianteEstatusVacaciones = (estatus) => {
  const mapa = {
    Pendiente: 'yellow',
    'Aprobada por Talento Humano': 'green',
    'Aprobada por Gerencia': 'green',
    'Negada por Talento Humano': 'red',
    'Negada por Gerencia': 'red',
    Reposo: 'sky',
    Suspendida: 'sky',
    Terminada: 'marca',
  };
  return mapa[estatus] || 'neutro';
};

const renderAccionesVacaciones = (fila, esAdmin = false) => {
  const id = fila.solicitud_vacaciones_id;
  const estatusNum = Number(fila.estatus);
  const botones = [
    crearBotonAccion({
      accion: 'mostrar',
      id,
      etiqueta: 'Ver',
      icono: 'eye',
      variante: 'marca',
    }),
  ];

  if (!esAdmin) {
    return crearGrupoBotones(botones);
  }

  botones.push(
    crearBotonAccion({
      accion: 'aprobar-rrhh',
      id,
      etiqueta: 'RRHH',
      titulo: 'Aprobar (Recursos Humanos)',
      icono: 'check',
      variante: 'green',
      deshabilitado: estatusNum === 2 || estatusNum === 4,
    }),
    crearBotonAccion({
      accion: 'aprobar-gerencia',
      id,
      etiqueta: 'Gerencia',
      titulo: 'Aprobar (Gerencia)',
      icono: 'shield-check',
      variante: 'sky',
      deshabilitado: estatusNum === 4,
    }),
    crearBotonAccion({
      accion: 'suspender',
      id,
      etiqueta: 'Suspender',
      titulo: 'Suspender vacaciones y reintegrar días',
      icono: 'pause-circle',
      variante: 'yellow',
      deshabilitado: estatusNum === 6 || estatusNum === 0,
    }),
    crearBotonAccion({
      accion: 'editar',
      id,
      etiqueta: 'Editar',
      icono: 'pencil',
      variante: 'zinc',
    })
  );

  return crearGrupoBotones(botones);
};

export const columnasTablaVacaciones = (esAdmin = false) => [
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
    render: (_d, _t, fila) => renderAccionesVacaciones(fila, esAdmin),
  },
];
