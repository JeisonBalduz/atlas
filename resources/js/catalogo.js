/**
 * catalogo.js
 * Controlador de los 6 catálogos base conectado a /api/catalogos/{tipo}.
 */

import { ControladorModal } from '/js/class/ControladorModal.js';
import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { inicializarSelectsPersonalizados } from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';
import {
  construirColumnasCatalogo,
  construirConfigCatalogos,
  renderFormularioCatalogo,
  renderTablaVaciaCatalogo,
} from '/js/templates/tabla_catalogo.js';
import { crearAlertaConfirmacion } from '/js/templates/template_ui.js';
import { escaparHtml } from '/js/utils/dom.js';

const leerEstadosDesdeDom = (contenedor) => {
  if (!contenedor?.dataset?.estados) return {};
  try {
    return JSON.parse(contenedor.dataset.estados) || {};
  } catch {
    return {};
  }
};

const leerPayloadFormulario = (formulario, campos) => {
  const payload = {};
  let valido = true;

  campos.forEach((campo) => {
    const control = formulario.elements[campo.nombre];
    if (!control) return;

    if (campo.tipo === 'checkbox') {
      payload[campo.nombre] = Boolean(control.checked);
      return;
    }

    const valorTexto = String(control.value || '').trim();
    if (campo.requerido && !valorTexto) valido = false;

    payload[campo.nombre] =
      campo.tipo === 'number' || campo.nombre === 'estado_id' ? Number(valorTexto) : valorTexto;
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
  const contenedorTabla = document.getElementById('contenedor-tabla-catalogo');
  const tituloTabla = document.getElementById('titulo-tabla-catalogo');
  const envolturaTabla = document.getElementById('envoltura-tabla-catalogo');
  const botonesPestana = document.querySelectorAll('.pestana-catalogo');
  const btnNuevo = document.getElementById('btn-nuevo-catalogo');

  const estadosMapa = leerEstadosDesdeDom(contenedorTabla);
  const configCatalogos = construirConfigCatalogos(estadosMapa);
  const modalFormulario = new ControladorModal('#modal-formulario-catalogo');
  const modalEliminar = new ControladorModal('#modal-eliminar-catalogo');

  let catalogoActivo = 'estatus';
  let tablaActual = null;

  const cargarCatalogo = async (claveCatalogo) => {
    const config = configCatalogos[claveCatalogo];
    if (!config) return;
    if (tituloTabla) tituloTabla.textContent = config.titulo;

    const res = await Solicitud.get(`/api/catalogos/${claveCatalogo}`, {
      claveCancelacion: 'catalogo-lista',
    });

    if (res.aborted) return;
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudo consultar el catálogo.');
      return;
    }

    const datos = Array.isArray(res.data.data) ? res.data.data : [];
    const columnas = construirColumnasCatalogo(config, estadosMapa);

    tablaActual?.destruir();
    if (envolturaTabla) envolturaTabla.innerHTML = renderTablaVaciaCatalogo(columnas);

    tablaActual = new GestorDataTable('#tabla-catalogo', {
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
      contenidoHtml: renderFormularioCatalogo(config, registroEditar),
      textoConfirmar: registroEditar ? 'Actualizar' : 'Guardar',
      textoCargando: 'Guardando...',
      alConfirmar: async (dialogo) => {
        const formulario = dialogo.querySelector('#form-modal-catalogo');
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
    inicializarSelectsPersonalizados();
  };

  const abrirModalEliminar = (registro) => {
    const config = configCatalogos[catalogoActivo];
    modalEliminar.abrir({
      titulo: 'Eliminar registro',
      subtitulo: config.titulo,
      contenidoHtml: crearAlertaConfirmacion({
        variante: 'red',
        mensajeHtml: `¿Deseas eliminar o desactivar el registro seleccionado del catálogo <strong>${escaparHtml(config.titulo)}</strong>?`,
      }),
      textoConfirmar: 'Confirmar',
      textoCargando: 'Procesando...',
      alConfirmar: async () => {
        const res = await Solicitud.delete(`/api/catalogos/${catalogoActivo}/${registro[config.claveId]}`);
        if (!res.ok || !res.data?.ok) {
          mostrarToast('error', res.message || 'No se pudo eliminar el registro.');
          return false;
        }
        mostrarToast('success', res.data.message || 'Operación completada.');
        cargarCatalogo(catalogoActivo);
        return true;
      },
    });
  };

  envolturaTabla?.addEventListener('click', (evento) => {
    const boton = evento.target.closest('button[data-accion]');
    if (!boton || !tablaActual) return;
    const registro = tablaActual.obtenerInstancia().row(boton.closest('tr')).data();
    if (!registro) return;

    if (boton.dataset.accion === 'editar') abrirModalRegistro(registro);
    if (boton.dataset.accion === 'eliminar') abrirModalEliminar(registro);
  });

  botonesPestana.forEach((boton) => {
    boton.addEventListener('click', () => {
      catalogoActivo = boton.dataset.catalogo;
      actualizarPestanaActiva(botonesPestana, boton);
      cargarCatalogo(catalogoActivo);
    });
  });

  btnNuevo?.addEventListener('click', () => abrirModalRegistro(null));
  cargarCatalogo(catalogoActivo);
});
