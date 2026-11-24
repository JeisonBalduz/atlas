import { crearBadge } from '/js/components/badge/crearBadge.js';
import { crearBotonAccion } from '/js/components/botones/crearBotonAccion.js';
import { crearGrupoBotones } from '/js/components/botones/crearGrupoBotones.js';

export const construirConfigCatalogos = (estadosMapa) => ({
  estatus: {
    titulo: 'Catálogo de Estatus',
    claveId: 'estatus_id',
    filtros: { activo: true },
    campos: [
      { nombre: 'estatus', etiqueta: 'Nombre del estatus', tipo: 'text', requerido: true, maximo: 50 },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
    ],
  },
  dependencias: {
    titulo: 'Catálogo de Dependencias',
    claveId: 'dependencia_id',
    filtros: { estado: true, activo: true },
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
    filtros: { activo: true },
    campos: [
      { nombre: 'departamento', etiqueta: 'Nombre del departamento', tipo: 'text', requerido: true, maximo: 100 },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
    ],
  },
  cargo: {
    titulo: 'Catálogo de Cargos',
    claveId: 'cargo_id',
    filtros: { activo: true },
    campos: [
      { nombre: 'cargo', etiqueta: 'Nombre del cargo', tipo: 'text', requerido: true, maximo: 50 },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
    ],
  },
  nivel_academicos: {
    titulo: 'Catálogo de Niveles Académicos',
    claveId: 'nivel_academico_id',
    filtros: { activo: true },
    campos: [
      { nombre: 'nivel_academico', etiqueta: 'Nivel académico', tipo: 'text', requerido: true, maximo: 100 },
      { nombre: 'jerarquia', etiqueta: 'Jerarquía (número entero)', tipo: 'number', requerido: true },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
    ],
  },
  carrera: {
    titulo: 'Catálogo de Carreras',
    claveId: 'carrera_id',
    filtros: { activo: true },
    campos: [
      { nombre: 'carrera', etiqueta: 'Nombre de la carrera', tipo: 'text', requerido: true, maximo: 150 },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
    ],
  },
  estados: {
    titulo: 'Catálogo de Estados',
    claveId: 'estado_id',
    filtros: { activo: true },
    campos: [
      { nombre: 'estado', etiqueta: 'Nombre del estado', tipo: 'text', requerido: true, maximo: 100 },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
    ],
  },
  municipios: {
    titulo: 'Catálogo de Municipios',
    claveId: 'municipio_id',
    filtros: { estado: true, activo: true },
    campos: [
      { nombre: 'municipio', etiqueta: 'Nombre del municipio', tipo: 'text', requerido: true, maximo: 100 },
      { nombre: 'estado_id', etiqueta: 'Estado', tipo: 'select', opciones: estadosMapa, requerido: true },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
    ],
  },
  parroquias: {
    titulo: 'Catálogo de Parroquias',
    claveId: 'parroquia_id',
    filtros: { estado: true, municipio: true, activo: true },
    campos: [
      { nombre: 'parroquia', etiqueta: 'Nombre de la parroquia', tipo: 'text', requerido: true, maximo: 100 },
      { nombre: 'estado_id', etiqueta: 'Estado', tipo: 'select', opciones: estadosMapa, requerido: false, soloFiltroModal: true },
      { nombre: 'municipio_id', etiqueta: 'Municipio', tipo: 'select', opciones: {}, requerido: true },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
    ],
  },
  estado_civil: {
    titulo: 'Catálogo de Estado Civil',
    claveId: 'estado_civil_id',
    filtros: { activo: true },
    campos: [
      { nombre: 'estado_civil', etiqueta: 'Estado civil', tipo: 'text', requerido: true, maximo: 50 },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
    ],
  },
  tipo_permisos: {
    titulo: 'Catálogo de Tipos de Permiso',
    claveId: 'tppermiso_id',
    filtros: { activo: true },
    campos: [
      { nombre: 'permiso', etiqueta: 'Tipo de permiso', tipo: 'text', requerido: true, maximo: 100 },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
    ],
  },
  tipo_reposos: {
    titulo: 'Catálogo de Tipos de Reposo',
    claveId: 'tpreposo_id',
    filtros: { activo: true },
    campos: [
      { nombre: 'reposo', etiqueta: 'Tipo de reposo médico', tipo: 'text', requerido: true, maximo: 100 },
      { nombre: 'activo', etiqueta: 'Registro activo', tipo: 'checkbox', requerido: false },
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

    if (campo.nombre === 'municipio_id') {
      return {
        data: null,
        title: 'Municipio',
        render: (_valor, _tipo, fila) => fila?.municipio?.municipio || 'Sin asignar',
      };
    }

    if (campo.nombre === 'estado_id') {
      return {
        data: null,
        title: 'Estado',
        render: (_valor, _tipo, fila) =>
          fila?.estado?.estado ||
          fila?.municipio?.estado?.estado ||
          estadosMapa[String(fila?.estado_id)] ||
          'Sin asignar',
      };
    }

    return {
      data: campo.nombre,
      title: campo.etiqueta,
      render: (valor) => valor ?? '—',
    };
  });

  cols.push({
    data: null,
    title: 'Acciones',
    orderable: false,
    searchable: false,
    className: 'text-right',
    render: (_datos, _tipo, fila) => {
      const esInactivo = fila.activo === false;
      const botonEstado = esInactivo
        ? crearBotonAccion({
            accion: 'activar',
            id: fila[config.claveId],
            etiqueta: 'Activar',
            icono: 'check-circle',
            variante: 'green',
          })
        : crearBotonAccion({
            accion: 'eliminar',
            id: fila[config.claveId],
            etiqueta: 'Desactivar',
            icono: 'ban',
            variante: 'red',
          });

      return crearGrupoBotones([
        crearBotonAccion({
          accion: 'editar',
          id: fila[config.claveId],
          etiqueta: 'Editar',
          icono: 'pencil',
          variante: 'zinc',
        }),
        botonEstado,
      ]);
    },
  });

  return cols;
};

export const renderTablaVaciaCatalogo = (columnas) => {
  const encabezadosHtml = columnas
    .map((col) => `<th class="${col.className || ''}">${col.title}</th>`)
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
