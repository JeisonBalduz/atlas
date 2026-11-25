import { crearBadge } from '/js/components/badge/crearBadge.js';
import { crearBotonAccion } from '/js/components/botones/crearBotonAccion.js';
import { crearGrupoBotones } from '/js/components/botones/crearGrupoBotones.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

// Recibe una fila de beneficio asignado; construye los botones de acción (Ver, Editar y Aprobar si está pendiente).
const renderAccionesBeneficio = (fila) =>
  crearGrupoBotones([
    crearBotonAccion({ accion: 'mostrar', id: fila.dtbeneficio_id, etiqueta: 'Ver', icono: 'eye', variante: 'marca' }),
    crearBotonAccion({
      accion: 'editar',
      id: fila.dtbeneficio_id,
      etiqueta: 'Editar',
      icono: 'pencil',
      variante: 'zinc',
      titulo: `Editar beneficio de ${fila.beneficiario || 'beneficiario'}`,
    }),
    fila.estatus === 'Pendiente'
      ? crearBotonAccion({ accion: 'aprobar', id: fila.dtbeneficio_id, etiqueta: 'Aprobar', icono: 'check', variante: 'green' })
      : '',
  ]);

export const columnasTablaBeneficios = () => [
  {
    data: 'cedula',
    render: (v) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${v || '—'}</span>`,
  },
  {
    data: 'beneficiario',
    render: (v) => `<span class="font-medium text-zinc-900">${v || '—'}</span>`,
  },
  {
    data: 'rol_parentesco',
    render: (v) => v || '—',
  },
  {
    data: 'tipo_beneficio',
    render: (v) => v || '—',
  },
  {
    data: 'fecha_solicitud',
    render: (v) => `<span class="whitespace-nowrap">${formatearFecha(v)}</span>`,
  },
  {
    data: 'fecha_aprobada',
    render: (v) => `<span class="whitespace-nowrap">${v ? formatearFecha(v) : 'Por aprobar'}</span>`,
  },
  {
    data: 'estatus',
    className: 'text-center',
    render: (v) => crearBadge(v || 'Pendiente', v === 'Aprobado' ? 'green' : 'yellow'),
  },
  {
    data: null,
    orderable: false,
    searchable: false,
    className: 'text-right',
    render: (_d, _t, fila) => renderAccionesBeneficio(fila),
  },
];
