import { crearBadge } from '/js/components/badge/crearBadge.js';
import { crearBotonAccion } from '/js/components/botones/crearBotonAccion.js';
import { crearGrupoBotones } from '/js/components/botones/crearGrupoBotones.js';
import { crearDropdownAcciones } from '/js/components/dropdown/crearDropdownAcciones.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

const renderTextoSimple = (valor) => `<span class="font-medium text-zinc-900 whitespace-nowrap">${valor || '—'}</span>`;

const renderNombre = (nombre, fila) => {
  const correo = fila.correo ? `<span class="block text-xs text-zinc-500">${fila.correo}</span>` : '';
  return `<div class="min-w-44"><span class="font-semibold text-zinc-900">${nombre || '—'}</span>${correo}</div>`;
};

// Recibe el objeto discapacidad y la fila; renderiza botón "Ver" si ya tiene discapacidad o "Asignar" si no.
const renderDiscapacidad = (discapacidad, fila) => {
  if (discapacidad && typeof discapacidad === 'object') {
    return crearBotonAccion({
      accion: 'ver-discapacidad',
      id: fila.empleado_id,
      etiqueta: 'Ver',
      icono: 'eye',
      variante: 'marca',
      titulo: `Ver discapacidad registrada de ${fila.nombre_completo || 'empleado'}`,
    });
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

const renderEstatus = (fila) => {
  if (Number(fila.estatus_actual) === 3) {
    return `<div class="inline-flex flex-wrap items-center justify-center gap-1">${crearBadge('Pendiente', 'yellow')}</div>`;
  }

  const colorEstatus = fila.activo ? 'marca' : 'red';
  const badgePrincipal = crearBadge(fila.estatus || 'Sin estatus', colorEstatus);
  const badgeInactivo = fila.activo ? '' : ` ${crearBadge('Inactivo', 'red')}`;

  return `<div class="inline-flex flex-wrap items-center justify-center gap-1">${badgePrincipal}${badgeInactivo}</div>`;
};

// Función original preservada: genera los botones individuales de acciones por fila sin eliminarlos.
export const renderAcciones = (fila) => {
  if (Number(fila.estatus_actual) === 3) {
    return crearGrupoBotones([
      crearBotonAccion({
        etiqueta: 'Pendiente',
        icono: 'clock',
        variante: 'yellow',
        href: `/empleado/editar/${fila.empleado_id}`,
        titulo: `Completar documentos pendientes de ${fila.nombre_completo || 'empleado'}`,
      }),
    ]);
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

// Recibe los datos de fila del empleado; genera el nuevo Dropdown con Familiares (con badge), Teléfonos, Editar y Eliminar/Activar.
const renderAccionesDropdown = (fila) => {
  if (Number(fila.estatus_actual) === 3) {
    return crearDropdownAcciones({
      etiqueta: 'Pendiente',
      icono: 'clock',
      variante: 'yellow',
      items: [
        {
          etiqueta: 'Completar expediente',
          icono: 'file-check',
          variante: 'yellow',
          href: `/empleado/editar/${fila.empleado_id}`,
          titulo: `Completar documentos pendientes de ${fila.nombre_completo || 'empleado'}`,
        },
        {
          accion: 'telefonos',
          id: fila.empleado_id,
          etiqueta: 'Teléfonos',
          icono: 'phone',
          variante: 'sky',
          titulo: `Ver teléfonos de contacto de ${fila.nombre_completo || 'empleado'}`,
        },
      ],
    });
  }

  return crearDropdownAcciones({
    etiqueta: 'Acciones',
    items: [
      {
        accion: 'familiares',
        id: fila.empleado_id,
        etiqueta: 'Familiares',
        icono: 'users',
        variante: 'marca',
        contador: fila.total_familiares ?? 0,
        titulo: `Ver familiares de ${fila.nombre_completo || 'empleado'}`,
      },
      {
        accion: 'telefonos',
        id: fila.empleado_id,
        etiqueta: 'Teléfonos',
        icono: 'phone',
        variante: 'sky',
        titulo: `Ver teléfonos de contacto de ${fila.nombre_completo || 'empleado'}`,
      },
      {
        etiqueta: 'Editar',
        icono: 'pencil',
        variante: 'zinc',
        href: `/empleado/editar/${fila.empleado_id}`,
        titulo: `Editar ficha de ${fila.nombre_completo || 'empleado'}`,
      },
      fila.activo
        ? {
            accion: 'eliminar',
            id: fila.empleado_id,
            etiqueta: 'Eliminar',
            icono: 'trash-2',
            variante: 'red',
            separadorSuperior: true,
            titulo: `Desactivar a ${fila.nombre_completo || 'empleado'}`,
          }
        : {
            accion: 'activar',
            id: fila.empleado_id,
            etiqueta: 'Activar',
            icono: 'user-check',
            variante: 'green',
            separadorSuperior: true,
            titulo: `Activar a ${fila.nombre_completo || 'empleado'}`,
          },
    ],
  });
};

export const columnasTablaEmpleados = () => [
  { data: 'codigo_empleado', name: 'codigo_empleado', render: (valor) => renderTextoSimple(valor) },
  { data: 'cedula', name: 'cedula', render: (valor) => renderTextoSimple(valor) },
  { data: 'nombre_completo', name: 'nombre_completo', render: (valor, _t, fila) => renderNombre(valor, fila) },
  { data: 'cargo', name: 'cargo', render: (valor) => valor || '—' },
  { data: 'departamento', name: 'departamento', render: (valor) => valor || '—' },
  { data: 'dependencia', name: 'dependencia', render: (valor) => valor || '—' },
  {
    data: 'discapacidad',
    name: 'discapacidad',
    orderable: false,
    searchable: false,
    className: 'text-center',
    render: (valor, _t, fila) => renderDiscapacidad(valor, fila),
  },
  {
    data: 'estatus_actual',
    name: 'estatus_actual',
    className: 'text-center',
    render: (_valor, _t, fila) => renderEstatus(fila),
  },
  {
    data: null,
    orderable: false,
    searchable: false,
    className: 'text-right',
    render: (_d, _t, fila) => renderAccionesDropdown(fila),
  },
];

const renderFilaTablaFamiliaresModal = (fam) => {
  const edadTexto = fam.edad !== null && fam.edad !== undefined ? `${fam.edad} años` : '—';
  const editarBtn = crearBotonAccion({
    etiqueta: 'Editar',
    icono: 'pencil',
    variante: 'zinc',
    href: `/empleado/familiar/editar/${fam.familia_id}`,
    titulo: `Editar a ${fam.nombre_completo || 'familiar'}`,
  });

  return `
    <tr class="hover:bg-slate-50/70 transition-colors">
      <td class="px-3.5 py-2.5 font-medium text-zinc-900 whitespace-nowrap">${fam.cedula || '—'}</td>
      <td class="px-3.5 py-2.5 font-medium text-zinc-900">${fam.nombre_completo || '—'}</td>
      <td class="px-3.5 py-2.5 whitespace-nowrap">${crearBadge(fam.parentesco || 'Sin definir', 'marca')}</td>
      <td class="px-3.5 py-2.5 whitespace-nowrap text-zinc-600">
        <span>${formatearFecha(fam.fecha_nacimiento)}</span>
        <span class="block text-xs text-zinc-400">${edadTexto}</span>
      </td>
      <td class="px-3.5 py-2.5 text-center whitespace-nowrap text-zinc-700">${fam.genero || '—'}</td>
      <td class="px-3.5 py-2.5 text-center whitespace-nowrap font-medium text-zinc-700">T-${fam.tomo} / F-${fam.folio}</td>
      <td class="px-3.5 py-2.5 text-right whitespace-nowrap">${editarBtn}</td>
    </tr>
  `;
};

export const renderTablaFamiliaresEmpleado = (payload, empleadoIdFallback) => {
  const empleado = payload?.empleado || {};
  const empleadoId = empleado.empleado_id || empleadoIdFallback;
  const lista = Array.isArray(payload?.data) ? payload.data : [];

  if (lista.length === 0) {
    return `
      <div class="flex flex-col items-center justify-center gap-4 py-10 text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-zinc-500">
          <i data-lucide="users" class="h-6 w-6" aria-hidden="true"></i>
        </div>
        <div class="max-w-sm space-y-1">
          <p class="text-sm font-semibold text-zinc-900">Este trabajador no tiene familiares registrados</p>
          <p class="text-xs text-zinc-500">Puedes asociar su carga familiar ahora mismo usando el botón inferior.</p>
        </div>
        <a href="/empleado/registrar/familiar?empleado=${empleadoId}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-marca px-4 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-marca-700 focus-visible:outline focus-visible:outline-offset-2 focus-visible:outline-marca active:scale-[0.98]">
          <i data-lucide="user-plus" class="h-3.5 w-3.5" aria-hidden="true"></i>
          <span>Registrar familiar</span>
        </a>
      </div>
    `;
  }

  return `
    <div class="space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
        <div class="text-xs text-zinc-600">
          <span class="font-semibold text-zinc-900">${lista.length}</span>
          ${lista.length === 1 ? 'familiar registrado' : 'familiares registrados'}
          ${empleado.departamento ? ` · <span>${empleado.departamento}</span>` : ''}
        </div>
        <a href="/empleado/registrar/familiar?empleado=${empleadoId}"
           class="inline-flex items-center gap-1.5 rounded-lg bg-marca px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-marca-700 focus-visible:outline focus-visible:outline-offset-2 focus-visible:outline-marca active:scale-[0.98]">
          <i data-lucide="user-plus" class="h-3.5 w-3.5" aria-hidden="true"></i>
          <span>Agregar familiar</span>
        </a>
      </div>

      <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-zinc-500">
            <tr>
              <th class="px-3.5 py-2.5">Cédula</th>
              <th class="px-3.5 py-2.5">Nombre completo</th>
              <th class="px-3.5 py-2.5">Parentesco</th>
              <th class="px-3.5 py-2.5">Nacimiento / Edad</th>
              <th class="px-3.5 py-2.5 text-center">Género</th>
              <th class="px-3.5 py-2.5 text-center">Tomo / Folio</th>
              <th class="px-3.5 py-2.5 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">${lista.map(renderFilaTablaFamiliaresModal).join('')}</tbody>
        </table>
      </div>
    </div>
  `;
};
