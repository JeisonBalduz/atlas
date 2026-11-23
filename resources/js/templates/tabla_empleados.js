import { crearBadge, crearBotonAccion, crearGrupoBotones } from '/js/templates/template_ui.js';

/**
 * Muestra un texto corto en negrita (código de empleado o cédula).
 */
const renderTextoSimple = (valor) => {
  return `<span class="font-medium text-zinc-900 whitespace-nowrap">${valor || '—'}</span>`;
};

/**
 * Muestra el nombre completo del trabajador y su correo electrónico debajo si tiene.
 */
const renderNombre = (nombre, fila) => {
  const correo = fila.correo ? `<span class="block text-xs text-zinc-500">${fila.correo}</span>` : '';
  return `<div class="min-w-44"><span class="font-semibold text-zinc-900">${nombre || '—'}</span>${correo}</div>`;
};

/**
 * Si el trabajador tiene discapacidad registrada muestra su badge;
 * si no tiene, muestra el botón "Asignar" para abrir el modal.
 */
const renderDiscapacidad = (discapacidad, fila) => {
  if (discapacidad && typeof discapacidad === 'object') {
    const carnet = discapacidad.numero_carnet ? `Carnet: ${discapacidad.numero_carnet}` : 'Discapacidad registrada';
    return `<span title="${carnet}">${crearBadge(discapacidad.etiqueta || 'Con discapacidad', 'sky')}</span>`;
  }

  return crearBotonAccion({
    accion: 'asignar-discapacidad',
    id: fila.empleado_id,
    etiqueta: 'Asignar',
    icono: 'accessibility',
    variante: 'zinc',
    titulo: `Asignar discapacidad a ${fila.nombre_completo || 'empleado'}`,
  });
};

/**
 * Muestra el badge del estatus laboral.
 * Si estatus_actual == 3, muestra "Pendiente" en amarillo.
 */
const renderEstatus = (fila) => {
  if (Number(fila.estatus_actual) === 3) {
    return `<div class="inline-flex flex-wrap items-center justify-center gap-1">${crearBadge('Pendiente', 'yellow')}</div>`;
  }

  const colorEstatus = fila.activo ? 'marca' : 'red';
  const badgePrincipal = crearBadge(fila.estatus || 'Sin estatus', colorEstatus);
  const badgeInactivo = fila.activo ? '' : ` ${crearBadge('Inactivo', 'red')}`;

  return `<div class="inline-flex flex-wrap items-center justify-center gap-1">${badgePrincipal}${badgeInactivo}</div>`;
};

/**
 * Genera los botones de acción por fila:
 * - Si estatus_actual == 3: solo botón amarillo "Pendiente" hacia la edición.
 * - En caso normal: Familiares, Editar y Activar/Eliminar según esté activo o inactivo.
 */
const renderAcciones = (fila) => {
  if (Number(fila.estatus_actual) === 3) {
    const btnPendiente = crearBotonAccion({
      etiqueta: 'Pendiente',
      icono: 'clock',
      variante: 'yellow',
      href: `/empleado/editar/${fila.empleado_id}`,
      titulo: `Completar documentos pendientes de ${fila.nombre_completo || 'empleado'}`,
    });
    return crearGrupoBotones([btnPendiente]);
  }

  const btnFamiliares = crearBotonAccion({
    accion: 'familiares',
    id: fila.empleado_id,
    etiqueta: 'Familiares',
    icono: 'users',
    variante: 'marca',
    contador: fila.total_familiares ?? 0,
    titulo: `Ver familiares de ${fila.nombre_completo || 'empleado'}`,
  });

  const btnEditar = crearBotonAccion({
    etiqueta: 'Editar',
    icono: 'pencil',
    variante: 'zinc',
    href: `/empleado/editar/${fila.empleado_id}`,
    titulo: `Editar ficha de ${fila.nombre_completo || 'empleado'}`,
  });

  const btnEstado = fila.activo
    ? crearBotonAccion({
        accion: 'eliminar',
        id: fila.empleado_id,
        etiqueta: 'Eliminar',
        icono: 'trash-2',
        variante: 'red',
        titulo: `Desactivar a ${fila.nombre_completo || 'empleado'}`,
      })
    : crearBotonAccion({
        accion: 'activar',
        id: fila.empleado_id,
        etiqueta: 'Activar',
        icono: 'user-check',
        variante: 'green',
        titulo: `Activar a ${fila.nombre_completo || 'empleado'}`,
      });

  return crearGrupoBotones([btnFamiliares, btnEditar, btnEstado]);
};

/**
 * Definición de las 9 columnas de DataTables para el listado de empleados.
 */
export const columnasTablaEmpleados = () => [
  {
    data: 'codigo_empleado',
    name: 'codigo_empleado',
    render: (valor) => renderTextoSimple(valor),
  },
  {
    data: 'cedula',
    name: 'cedula',
    render: (valor) => renderTextoSimple(valor),
  },
  {
    data: 'nombre_completo',
    name: 'nombre_completo',
    render: (valor, tipo, fila) => renderNombre(valor, fila),
  },
  {
    data: 'cargo',
    name: 'cargo',
    render: (valor) => valor || '—',
  },
  {
    data: 'departamento',
    name: 'departamento',
    render: (valor) => valor || '—',
  },
  {
    data: 'dependencia',
    name: 'dependencia',
    render: (valor) => valor || '—',
  },
  {
    data: 'discapacidad',
    name: 'discapacidad',
    orderable: false,
    searchable: false,
    className: 'text-center',
    render: (valor, tipo, fila) => renderDiscapacidad(valor, fila),
  },
  {
    data: 'estatus_actual',
    name: 'estatus_actual',
    className: 'text-center',
    render: (valor, tipo, fila) => renderEstatus(fila),
  },
  {
    data: null,
    orderable: false,
    searchable: false,
    className: 'text-right',
    render: (datos, tipo, fila) => renderAcciones(fila),
  },
];
