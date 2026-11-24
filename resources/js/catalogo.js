import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { crearAlertaConfirmacion } from '/js/components/alerta/alerta.js';
import { crearCheck } from '/js/components/check/crearCheck.js';
import { Modal } from '/js/components/modal/Modal.js';
import {
  actualizarOpcionesSelect,
  crearMarkupSelectPersonalizado,
  inicializarSelectsPersonalizados,
} from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import {
  construirColumnasCatalogo,
  construirConfigCatalogos,
  renderTablaVaciaCatalogo,
} from '/js/templates/tabla_catalogo.js';

const leerEstadosDesdeDom = (contenedor) => {
  if (!contenedor?.dataset?.estados) return {};
  try {
    return JSON.parse(contenedor.dataset.estados) || {};
  } catch {
    return {};
  }
};

const renderCampoFormularioCatalogo = (campo, registroEditar) => {
  const idCampo = `modal-catalogo-${campo.nombre}`;
  const valorActual = registroEditar ? (registroEditar[campo.nombre] ?? '') : '';

  if (campo.tipo === 'checkbox') {
    const marcado = registroEditar ? Boolean(valorActual) : true;
    return crearCheck({
      id: idCampo,
      nombre: campo.nombre,
      etiqueta: campo.etiqueta,
      descripcion: 'Define si este registro aparece habilitado en los selectores de formularios.',
      marcado,
    });
  }

  if (campo.tipo === 'select') {
    return crearMarkupSelectPersonalizado({
      id: idCampo,
      nombre: campo.nombre,
      etiqueta: campo.etiqueta,
      opciones: campo.opciones || {},
      valorSeleccionado: valorActual,
      placeholder: 'Seleccionar opción...',
      requerido: Boolean(campo.requerido),
      buscador: true,
    });
  }

  const maxAttr = campo.maximo ? `maxlength="${campo.maximo}"` : '';
  const reqAttr = campo.requerido ? 'required' : '';

  return `
    <div class="space-y-1.5">
      <label for="${idCampo}" class="block text-sm font-medium text-zinc-900">${campo.etiqueta}</label>
      <input
        id="${idCampo}"
        name="${campo.nombre}"
        type="${campo.tipo}"
        value="${valorActual}"
        ${maxAttr}
        ${reqAttr}
        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none transition duration-200 focus:border-black focus:bg-white focus:ring-2 focus:ring-black focus:ring-offset-2"
      />
    </div>
  `;
};

const renderFormularioCatalogo = (config, registroEditar) => `
  <form id="form-modal-catalogo" class="space-y-4" novalidate>
    ${config.campos.map((campo) => renderCampoFormularioCatalogo(campo, registroEditar)).join('')}
  </form>
`;

const leerPayloadFormulario = (formulario, campos) => {
  const payload = {};
  let valido = true;

  campos.forEach((campo) => {
    if (campo.soloFiltroModal) return;

    const control = formulario.elements[campo.nombre];
    if (!control) return;

    if (campo.tipo === 'checkbox') {
      payload[campo.nombre] = Boolean(control.checked);
      return;
    }

    const valorTexto = String(control.value || '').trim();
    if (campo.requerido && !valorTexto) valido = false;

    const esNumerico = campo.tipo === 'number' || campo.nombre.endsWith('_id');
    payload[campo.nombre] = esNumerico ? Number(valorTexto) : valorTexto;
  });

  return valido ? payload : null;
};

const actualizarPestanaActiva = (botones, activoBtn) => {
  botones.forEach((btn) => {
    const activo = btn === activoBtn;
    btn.setAttribute('aria-selected', String(activo));
    btn.classList.toggle('bg-marca', activo);
    btn.classList.toggle('text-white', activo);
    btn.classList.toggle('bg-white', !activo);
    btn.classList.toggle('text-zinc-500', !activo);
  });
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();

  const contenedorTabla = document.getElementById('contenedor-tabla-catalogo');
  const tituloTabla = document.getElementById('titulo-tabla-catalogo');
  const envolturaTabla = document.getElementById('envoltura-tabla-catalogo');
  const botonesPestana = document.querySelectorAll('.pestana-catalogo');
  const btnNuevo = document.getElementById('btn-nuevo-catalogo');

  const barraFiltros = document.getElementById('filtros-tabla-catalogo');
  const envolturaFiltroEstado = document.getElementById('envoltura-filtro-estado');
  const envolturaFiltroMunicipio = document.getElementById('envoltura-filtro-municipio');
  const envolturaFiltroActivo = document.getElementById('envoltura-filtro-activo');
  const filtroEstado = document.getElementById('filtro-catalogo-estado');
  const filtroMunicipio = document.getElementById('filtro-catalogo-municipio');
  const filtroActivo = document.getElementById('filtro-catalogo-activo');

  const estadosMapa = leerEstadosDesdeDom(contenedorTabla);
  const configCatalogos = construirConfigCatalogos(estadosMapa);
  const modalFormulario = new Modal('#modal-formulario-catalogo');
  const modalEliminar = new Modal('#modal-eliminar-catalogo');

  let catalogoActivo = 'estatus';
  let tablaActual = null;
  let sincronizandoFiltros = false;

  const actualizarMapaEstados = (listaEstados) => {
    Object.keys(estadosMapa).forEach((clave) => delete estadosMapa[clave]);
    listaEstados.forEach((item) => {
      if (item?.estado_id && item?.estado) {
        estadosMapa[String(item.estado_id)] = item.estado;
      }
    });

    sincronizandoFiltros = true;
    actualizarOpcionesSelect(
      '#filtro-catalogo-estado',
      { todos: 'Todos los estados', ...estadosMapa },
      filtroEstado?.value || 'todos'
    );
    sincronizandoFiltros = false;
  };

  const cargarMunicipiosSelect = async (selector, estadoId, valorSeleccionado = '', incluirTodos = false) => {
    const base = incluirTodos ? { todos: 'Todos los municipios' } : {};
    if (!estadoId || estadoId === 'todos') {
      sincronizandoFiltros = incluirTodos;
      actualizarOpcionesSelect(selector, base, incluirTodos ? 'todos' : '');
      sincronizandoFiltros = false;
      return;
    }

    const res = await Solicitud.get(`/api/catalogos/municipios`, {
      params: { estado_id: estadoId },
      claveCancelacion: `municipios:${selector}`,
    });
    if (res.aborted) return;

    const lista = res.ok && Array.isArray(res.data?.data) ? res.data.data : [];
    const municipios = {};
    lista.forEach((m) => {
      municipios[String(m.municipio_id)] = m.municipio;
    });

    sincronizandoFiltros = incluirTodos;
    actualizarOpcionesSelect(selector, { ...base, ...municipios }, valorSeleccionado);
    sincronizandoFiltros = false;
  };

  const configurarVisibilidadFiltros = (config) => {
    const filtros = config?.filtros || {};
    const hayFiltros = Boolean(filtros.estado || filtros.municipio || filtros.activo);

    barraFiltros?.classList.toggle('hidden', !hayFiltros);
    envolturaFiltroEstado?.classList.toggle('hidden', !filtros.estado);
    envolturaFiltroMunicipio?.classList.toggle('hidden', !filtros.municipio);
    envolturaFiltroActivo?.classList.toggle('hidden', !filtros.activo);
  };

  const reiniciarFiltros = () => {
    sincronizandoFiltros = true;
    actualizarOpcionesSelect('#filtro-catalogo-estado', { todos: 'Todos los estados', ...estadosMapa }, 'todos');
    actualizarOpcionesSelect('#filtro-catalogo-municipio', { todos: 'Todos los municipios' }, 'todos');
    actualizarOpcionesSelect(
      '#filtro-catalogo-activo',
      { todos: 'Todos', 1: 'Solo activos', 0: 'Solo inactivos' },
      'todos'
    );
    sincronizandoFiltros = false;
  };

  const obtenerParametrosFiltro = (config) => {
    const params = {};
    const filtros = config?.filtros || {};

    if (filtros.estado && filtroEstado?.value && filtroEstado.value !== 'todos') {
      params.estado_id = filtroEstado.value;
    }
    if (filtros.municipio && filtroMunicipio?.value && filtroMunicipio.value !== 'todos') {
      params.municipio_id = filtroMunicipio.value;
    }
    if (filtros.activo && filtroActivo?.value && filtroActivo.value !== 'todos') {
      params.activo = filtroActivo.value;
    }

    return params;
  };

  const cargarCatalogo = async (claveCatalogo) => {
    const config = configCatalogos[claveCatalogo];
    if (!config) return;
    if (tituloTabla) tituloTabla.textContent = config.titulo;

    configurarVisibilidadFiltros(config);
    const params = obtenerParametrosFiltro(config);

    const res = await Solicitud.get(`/api/catalogos/${claveCatalogo}`, {
      params,
      claveCancelacion: 'catalogo-lista',
    });

    if (res.aborted) return;
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudo consultar el catálogo.');
      return;
    }

    const datos = Array.isArray(res.data.data) ? res.data.data : [];
    if (claveCatalogo === 'estados' && Object.keys(params).length === 0) {
      actualizarMapaEstados(datos);
    }

    const columnas = construirColumnasCatalogo(config, estadosMapa);

    tablaActual?.destruir();
    if (envolturaTabla) envolturaTabla.innerHTML = renderTablaVaciaCatalogo(columnas);

    tablaActual = new GestorDataTable('#tabla-catalogo', {
      datos,
      columnas,
      ordenInicial: [[0, 'asc']],
    });
  };

  const vincularSelectsModal = (dialogo, registroEditar) => {
    const selectEstadoModal = dialogo.querySelector('#modal-catalogo-estado_id');
    const selectMunicipioModal = dialogo.querySelector('#modal-catalogo-municipio_id');

    if (catalogoActivo === 'parroquias' && selectEstadoModal && selectMunicipioModal) {
      const estadoInicial = registroEditar
        ? String(registroEditar?.municipio?.estado_id || '')
        : filtroEstado?.value && filtroEstado.value !== 'todos'
          ? filtroEstado.value
          : '';

      const municipioInicial = registroEditar
        ? String(registroEditar.municipio_id || '')
        : filtroMunicipio?.value && filtroMunicipio.value !== 'todos'
          ? filtroMunicipio.value
          : '';

      if (estadoInicial) {
        actualizarOpcionesSelect('#modal-catalogo-estado_id', estadosMapa, estadoInicial);
        cargarMunicipiosSelect('#modal-catalogo-municipio_id', estadoInicial, municipioInicial);
      }

      selectEstadoModal.addEventListener('change', () => {
        cargarMunicipiosSelect('#modal-catalogo-municipio_id', selectEstadoModal.value, '');
      });
      return;
    }

    if (!registroEditar && selectEstadoModal && filtroEstado?.value && filtroEstado.value !== 'todos') {
      actualizarOpcionesSelect('#modal-catalogo-estado_id', estadosMapa, filtroEstado.value);
    }
  };

  const abrirModalRegistro = (registroEditar = null) => {
    const config = configCatalogos[catalogoActivo];
    modalFormulario.abrir({
      titulo: registroEditar ? `Editar en ${config.titulo}` : `Nuevo en ${config.titulo}`,
      subtitulo: 'Los cambios se guardan directamente en la base de datos.',
      contenidoHtml: renderFormularioCatalogo(config, registroEditar),
      textoConfirmar: registroEditar ? 'Actualizar' : 'Guardar',
      textoCargando: 'Guardando...',
      alConfirmar: async (dlg) => {
        const formulario = dlg.querySelector('#form-modal-catalogo');
        const payload = formulario ? leerPayloadFormulario(formulario, config.campos) : null;
        if (!payload) {
          mostrarToast('error', 'Completa todos los campos obligatorios.');
          return false;
        }

        const res = registroEditar
          ? await Solicitud.put(`/api/catalogos/${catalogoActivo}/${registroEditar[config.claveId]}`, payload)
          : await Solicitud.post(`/api/catalogos/${catalogoActivo}`, payload);

        if (!res.ok || !res.data?.ok) {
          mostrarToast('error', res.message || 'No se pudo guardar el registro.');
          return false;
        }

        mostrarToast('success', res.data.message || 'Registro guardado correctamente.');
        cargarCatalogo(catalogoActivo);
        return true;
      },
    });

    if (modalFormulario.dialogo) vincularSelectsModal(modalFormulario.dialogo, registroEditar);
  };

  const abrirModalEliminar = (registro) => {
    const config = configCatalogos[catalogoActivo];
    modalEliminar.abrir({
      titulo: 'Desactivar registro',
      subtitulo: config.titulo,
      contenidoHtml: crearAlertaConfirmacion({
        variante: 'red',
        mensajeHtml: `¿Deseas desactivar el registro seleccionado del catálogo <strong>${config.titulo}</strong>? Dejará de mostrarse en los selectores de formularios.`,
      }),
      textoConfirmar: 'Confirmar',
      textoCargando: 'Procesando...',
      alConfirmar: async () => {
        const res = await Solicitud.delete(`/api/catalogos/${catalogoActivo}/${registro[config.claveId]}`);
        if (!res.ok || !res.data?.ok) {
          mostrarToast('error', res.message || 'No se pudo desactivar el registro.');
          return false;
        }
        mostrarToast('success', res.data.message || 'Registro desactivado.');
        cargarCatalogo(catalogoActivo);
        return true;
      },
    });
  };

  envolturaTabla?.addEventListener('click', (evento) => {
    const boton = evento.target.closest('button[data-accion]');
    if (!boton || !tablaActual) return;
    const registro = tablaActual.obtenerInstancia()?.row(boton.closest('tr')).data();
    if (!registro) return;

    if (boton.dataset.accion === 'editar') abrirModalRegistro(registro);
    if (boton.dataset.accion === 'eliminar') abrirModalEliminar(registro);
  });

  filtroEstado?.addEventListener('change', async () => {
    if (sincronizandoFiltros) return;
    if (catalogoActivo === 'parroquias') {
      await cargarMunicipiosSelect('#filtro-catalogo-municipio', filtroEstado.value, 'todos', true);
    }
    cargarCatalogo(catalogoActivo);
  });

  filtroMunicipio?.addEventListener('change', () => {
    if (sincronizandoFiltros) return;
    cargarCatalogo(catalogoActivo);
  });

  filtroActivo?.addEventListener('change', () => {
    if (sincronizandoFiltros) return;
    cargarCatalogo(catalogoActivo);
  });

  botonesPestana.forEach((boton) => {
    boton.addEventListener('click', () => {
      catalogoActivo = boton.dataset.catalogo;
      actualizarPestanaActiva(botonesPestana, boton);
      reiniciarFiltros();
      cargarCatalogo(catalogoActivo);
    });
  });

  btnNuevo?.addEventListener('click', () => abrirModalRegistro(null));
  cargarCatalogo(catalogoActivo);
});
