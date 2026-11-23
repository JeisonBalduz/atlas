import {
  crearBadge,
  crearBotonAccion,
  crearGrupoBotones,
  crearMarkupSelectPersonalizado,
} from '/js/templates/template_ui.js';
import { escaparHtml } from '/js/utils/dom.js';

export const construirConfigCatalogos = (estadosMapa) => ({
  estatus: {
    titulo: 'Catálogo de Estatus',
    claveId: 'estatus_id',
    campos: [
      { nombre: 'estatus', etiqueta: 'Nombre del estatus', tipo: 'text', requerido: true, maximo: 50 },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
    ],
  },
  dependencias: {
    titulo: 'Catálogo de Dependencias',
    claveId: 'dependencia_id',
    campos: [
      { nombre: 'dependencia', etiqueta: 'Nombre de la dependencia', tipo: 'text', requerido: true, maximo: 100 },
      { nombre: 'codigo', etiqueta: 'Código', tipo: 'text', requerido: true, maximo: 20 },
      { nombre: 'estado_id', etiqueta: 'Estado', tipo: 'select', opciones: estadosMapa, requerido: true },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
    ],
  },
  departamentos: {
    titulo: 'Catálogo de Departamentos',
    claveId: 'departamento_id',
    campos: [
      { nombre: 'departamento', etiqueta: 'Nombre del departamento', tipo: 'text', requerido: true, maximo: 100 },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
    ],
  },
  cargo: {
    titulo: 'Catálogo de Cargos',
    claveId: 'cargo_id',
    campos: [
      { nombre: 'cargo', etiqueta: 'Nombre del cargo', tipo: 'text', requerido: true, maximo: 50 },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
    ],
  },
  nivel_academicos: {
    titulo: 'Catálogo de Niveles Académicos',
    claveId: 'nivel_academico_id',
    campos: [
      { nombre: 'nivel_academico', etiqueta: 'Nivel académico', tipo: 'text', requerido: true, maximo: 100 },
      { nombre: 'jerarquia', etiqueta: 'Jerarquía (número entero)', tipo: 'number', requerido: true },
    ],
  },
  carrera: {
    titulo: 'Catálogo de Carreras',
    claveId: 'carrera_id',
    campos: [
      { nombre: 'carrera', etiqueta: 'Nombre de la carrera', tipo: 'text', requerido: true, maximo: 150 },
    ],
  },
});

export const construirColumnasCatalogo = (config, estadosMapa) => {
  const cols = config.campos.map((campo) => {
    if (campo.tipo === 'checkbox') {
      return {
        data: campo.nombre,
        title: 'Estado',
        className: 'text-center',
        render: (valor) => crearBadge(valor ? 'Activo' : 'Inactivo', valor ? 'green' : 'red'),
      };
    }

    if (campo.nombre === 'estado_id') {
      return {
        data: 'estado_id',
        title: 'Estado',
        render: (valor, _tipo, fila) =>
          escaparHtml(fila?.estado?.estado || estadosMapa[String(valor)] || 'Sin asignar'),
      };
    }

    return {
      data: campo.nombre,
      title: campo.etiqueta,
      render: (valor) => escaparHtml(valor ?? '—'),
    };
  });

  cols.push({
    data: null,
    title: 'Acciones',
    orderable: false,
    searchable: false,
    className: 'text-right',
    render: (_datos, _tipo, fila) =>
      crearGrupoBotones([
        crearBotonAccion({ accion: 'editar', id: fila[config.claveId], etiqueta: 'Editar', icono: 'pencil', variante: 'zinc' }),
        crearBotonAccion({ accion: 'eliminar', id: fila[config.claveId], etiqueta: 'Eliminar', icono: 'trash-2', variante: 'red' }),
      ]),
  });

  return cols;
};

export const renderTablaVaciaCatalogo = (columnas) => {
  const encabezadosHtml = columnas
    .map((col) => `<th class="${escaparHtml(col.className || '')}">${escaparHtml(col.title)}</th>`)
    .join('');

  return `
    <table id="tabla-catalogo" class="w-full text-sm text-left">
      <thead class="text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-slate-50">
        <tr>${encabezadosHtml}</tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-zinc-700"></tbody>
    </table>
  `;
};

const renderCampoCatalogo = (campo, registro) => {
  const valorActual = registro ? registro[campo.nombre] : campo.tipo === 'checkbox' ? true : '';

  if (campo.tipo === 'checkbox') {
    return `
      <label class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 cursor-pointer">
        <span class="text-sm font-medium text-zinc-900">${escaparHtml(campo.etiqueta)}</span>
        <input type="checkbox" name="${escaparHtml(campo.nombre)}" ${valorActual ? 'checked' : ''}
          class="h-4 w-4 rounded border-slate-300 text-marca focus:ring-2 focus:ring-marca-500" />
      </label>
    `;
  }

  if (campo.tipo === 'select') {
    return crearMarkupSelectPersonalizado({
      id: `modal-catalogo-${campo.nombre}`,
      nombre: campo.nombre,
      etiqueta: campo.etiqueta,
      opciones: campo.opciones || {},
      valorSeleccionado: valorActual ?? '',
      placeholder: 'Seleccionar...',
      requerido: Boolean(campo.requerido),
      buscador: true,
    });
  }

  return `
    <div class="space-y-1.5">
      <label class="block text-sm font-medium text-zinc-900">${escaparHtml(campo.etiqueta)}</label>
      <input type="${escaparHtml(campo.tipo)}" name="${escaparHtml(campo.nombre)}" value="${escaparHtml(valorActual ?? '')}"
        ${campo.maximo ? `maxlength="${escaparHtml(campo.maximo)}"` : ''} ${campo.requerido ? 'required' : ''}
        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 focus:border-marca-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2" />
    </div>
  `;
};

export const renderFormularioCatalogo = (config, registro = null) =>
  `<form id="form-modal-catalogo" class="space-y-4" novalidate>${config.campos.map((c) => renderCampoCatalogo(c, registro)).join('')}</form>`;
