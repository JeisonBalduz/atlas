/**
 * catalogo.js
 * Gestor unificado para los 6 catálogos base conectado a /api/catalogos/{tipo}
 * con DataTables 3.1.5 en JavaScript 100% nativo (cero jQuery).
 */

import { ControladorModal } from '/js/class/ControladorModal.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { inicializarSelectsPersonalizados } from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';
import {
  crearBadge,
  crearBotonAccion,
  crearMarkupSelectPersonalizado,
  crearTablaCliente,
  escaparHtml,
} from '/js/datatables/configDataTable.js';

const obtenerEstadosMapa = () => {
  const nodo = document.getElementById('contenedor-tabla-catalogo');
  if (!nodo?.dataset?.estados) return {};
  try {
    return JSON.parse(nodo.dataset.estados) || {};
  } catch {
    return {};
  }
};

const construirConfigCatalogos = (estadosMapa) => ({
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

const construirColumnasDataTable = (config, estadosMapa) => {
  const cols = config.campos.map((campo) => {
    if (campo.tipo === 'checkbox') {
      return {
        data: campo.nombre,
        title: 'Estado',
        className: 'text-center',
        render: (valor) =>
          crearBadge(valor ? 'Activo' : 'Inactivo', valor ? 'green' : 'red'),
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
    render: (_datos, _tipo, fila) => {
      const id = fila[config.claveId];
      const btnEditar = crearBotonAccion({
        accion: 'editar',
        id,
        etiqueta: 'Editar',
        icono: 'pencil',
        variante: 'zinc',
      });
      const btnEliminar = crearBotonAccion({
        accion: 'eliminar',
        id,
        etiqueta: 'Eliminar',
        icono: 'trash-2',
        variante: 'red',
      });
      return `<div class="inline-flex items-center justify-end gap-1.5">${btnEditar}${btnEliminar}</div>`;
    },
  });

  return cols;
};

const construirFormularioHtml = (config, registro = null) => {
  const bloques = config.campos
    .map((campo) => {
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
    })
    .join('');

  return `<form id="form-modal-catalogo" class="space-y-4" novalidate>${bloques}</form>`;
};

document.addEventListener('DOMContentLoaded', () => {
  const estadosMapa = obtenerEstadosMapa();
  const configCatalogos = construirConfigCatalogos(estadosMapa);

  let catalogoActivo = 'estatus';
  let tablaActual = null;
  let controladorCarga = null;

  const modalFormulario = new ControladorModal('#modal-formulario-catalogo');
  const modalEliminar = new ControladorModal('#modal-eliminar-catalogo');
  const tituloTabla = document.getElementById('titulo-tabla-catalogo');
  const envolturaTabla = document.getElementById('envoltura-tabla-catalogo');

  const reiniciarTablaHtml = (columnas) => {
    if (tablaActual) {
      tablaActual.destroy();
      tablaActual = null;
    }

    if (!envolturaTabla) return;

    const encabezadosHtml = columnas
      .map(
        (col) =>
          `<th class="${escaparHtml(col.className || '')}">${escaparHtml(col.title)}</th>`
      )
      .join('');

    envolturaTabla.innerHTML = `
      <table id="tabla-catalogo" class="w-full text-sm text-left">
        <thead class="text-xs font-semibold uppercase tracking-wider text-zinc-500 bg-slate-50">
          <tr>${encabezadosHtml}</tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-zinc-700"></tbody>
      </table>
    `;
  };

  const cargarCatalogo = async (claveCatalogo) => {
    const config = configCatalogos[claveCatalogo];
    if (!config) return;

    if (tituloTabla) {
      tituloTabla.textContent = config.titulo;
    }

    if (controladorCarga) {
      controladorCarga.abort();
    }
    controladorCarga = new AbortController();

    const respuesta = await Solicitud.get(`/api/catalogos/${claveCatalogo}`, {
      signal: controladorCarga.signal,
    });

    if (respuesta.aborted) return;

    if (respuesta.status === 401) {
      window.location.href = '/autenticacion';
      return;
    }

    if (!respuesta.ok || !respuesta.data?.ok) {
      mostrarToast('error', respuesta.message || 'No se pudo consultar el catálogo.');
      return;
    }

    const datos = Array.isArray(respuesta.data.data) ? respuesta.data.data : [];
    const columnas = construirColumnasDataTable(config, estadosMapa);

    reiniciarTablaHtml(columnas);
    tablaActual = crearTablaCliente('#tabla-catalogo', {
      datos,
      columnas,
      ordenInicial: [[0, 'asc']],
    });
  };

  const abrirModalRegistro = (registroEditar = null) => {
    const config = configCatalogos[catalogoActivo];
    modalFormulario.abrir({
      titulo: registroEditar ? `Editar en ${config.titulo}` : `Nuevo en ${config.titulo}`,
      subtitulo: 'Los cambios se guardan directamente en la base de datos.',
      contenidoHtml: construirFormularioHtml(config, registroEditar),
      textoConfirmar: registroEditar ? 'Actualizar' : 'Guardar',
      alConfirmar: (dialogo) => {
        const formulario = dialogo.querySelector('#form-modal-catalogo');
        if (!formulario) return false;

        const payload = {};
        let valido = true;

        config.campos.forEach((campo) => {
          const control = formulario.elements[campo.nombre];
          if (!control) return;

          if (campo.tipo === 'checkbox') {
            payload[campo.nombre] = Boolean(control.checked);
          } else {
            const valorTexto = String(control.value || '').trim();
            if (campo.requerido && !valorTexto) {
              valido = false;
            }
            payload[campo.nombre] =
              campo.tipo === 'number' || campo.nombre === 'estado_id'
                ? Number(valorTexto)
                : valorTexto;
          }
        });

        if (!valido) {
          mostrarToast('error', 'Completa todos los campos obligatorios.');
          return false;
        }

        const btnConfirmar = dialogo.querySelector('[data-modal-confirmar]');
        const textoOriginal = btnConfirmar?.textContent || 'Guardar';
        if (btnConfirmar) {
          btnConfirmar.disabled = true;
          btnConfirmar.textContent = 'Guardando...';
        }

        const peticion = registroEditar
          ? Solicitud.put(`/api/catalogos/${catalogoActivo}/${registroEditar[config.claveId]}`, payload)
          : Solicitud.post(`/api/catalogos/${catalogoActivo}`, payload);

        peticion
          .then((res) => {
            if (res.status === 401) {
              window.location.href = '/autenticacion';
              return;
            }
            if (!res.ok || !res.data?.ok) {
              mostrarToast('error', res.message || 'No se pudo guardar el registro.');
              return;
            }

            mostrarToast('success', res.data.message || 'Registro guardado correctamente.');
            modalFormulario.cerrar();
            cargarCatalogo(catalogoActivo);
          })
          .finally(() => {
            if (btnConfirmar) {
              btnConfirmar.disabled = false;
              btnConfirmar.textContent = textoOriginal;
            }
          });

        return false;
      },
    });
    inicializarSelectsPersonalizados();
  };

  // Delegación de eventos sobre el contenedor de tabla (sobrevive a destroy/rebuild)
  envolturaTabla?.addEventListener('click', (evento) => {
    const boton = evento.target.closest('button[data-accion]');
    if (!boton || !tablaActual) return;

    const accion = boton.dataset.accion;
    const filaDom = boton.closest('tr');
    const registro = filaDom ? tablaActual.row(filaDom).data() : null;
    if (!registro) return;

    const config = configCatalogos[catalogoActivo];

    if (accion === 'editar') {
      abrirModalRegistro(registro);
      return;
    }

    if (accion === 'eliminar') {
      modalEliminar.abrir({
        titulo: 'Eliminar registro',
        subtitulo: config.titulo,
        contenidoHtml: `
          <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            ¿Deseas eliminar o desactivar el registro seleccionado del catálogo <strong>${escaparHtml(config.titulo)}</strong>?
          </div>
        `,
        textoConfirmar: 'Confirmar',
        alConfirmar: (dialogo) => {
          const btnConfirmar = dialogo.querySelector('[data-modal-confirmar]');
          const textoOriginal = btnConfirmar?.textContent || 'Confirmar';
          if (btnConfirmar) {
            btnConfirmar.disabled = true;
            btnConfirmar.textContent = 'Procesando...';
          }

          Solicitud.delete(`/api/catalogos/${catalogoActivo}/${registro[config.claveId]}`)
            .then((res) => {
              if (res.status === 401) {
                window.location.href = '/autenticacion';
                return;
              }
              if (!res.ok || !res.data?.ok) {
                mostrarToast('error', res.message || 'No se pudo eliminar el registro.');
                return;
              }

              mostrarToast('success', res.data.message || 'Operación completada.');
              modalEliminar.cerrar();
              cargarCatalogo(catalogoActivo);
            })
            .finally(() => {
              if (btnConfirmar) {
                btnConfirmar.disabled = false;
                btnConfirmar.textContent = textoOriginal;
              }
            });

          return false;
        },
      });
    }
  });

  // Cambio de pestaña de catálogo
  const botonesPestana = document.querySelectorAll('.pestana-catalogo');
  botonesPestana.forEach((boton) => {
    boton.addEventListener('click', () => {
      catalogoActivo = boton.dataset.catalogo;

      botonesPestana.forEach((btn) => {
        const activo = btn === boton;
        btn.setAttribute('aria-selected', String(activo));
        btn.classList.toggle('bg-marca', activo);
        btn.classList.toggle('text-white', activo);
        btn.classList.toggle('bg-white', !activo);
        btn.classList.toggle('text-zinc-500', !activo);
      });

      cargarCatalogo(catalogoActivo);
    });
  });

  document.getElementById('btn-nuevo-catalogo')?.addEventListener('click', () => abrirModalRegistro(null));

  cargarCatalogo(catalogoActivo);
});
