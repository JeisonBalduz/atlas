import { Solicitud } from '/js/class/Solicitud.js';
import {
  crearMarkupSelectLongitud,
  inicializarSelectsPersonalizados,
} from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';

export const IDIOMA_ES = {
  processing: 'Procesando registros...',
  search: 'Buscar:',
  searchPlaceholder: 'Filtrar registros...',
  lengthMenu: 'Mostrar _MENU_ registros',
  info: 'Mostrando _START_ al _END_ de _TOTAL_ registros',
  infoEmpty: 'Mostrando 0 registros',
  infoFiltered: '(filtrado de _MAX_ registros totales)',
  loadingRecords: 'Cargando datos...',
  zeroRecords: 'No se encontraron registros que coincidan con la búsqueda.',
  emptyTable: 'No hay registros disponibles en esta tabla.',
  paginate: {
    first: 'Primero',
    previous: 'Anterior',
    next: 'Siguiente',
    last: 'Último',
  },
  aria: {
    sortAscending: ': activar para ordenar ascendentemente',
    sortDescending: ': activar para ordenar descendentemente',
  },
};

const transformarSelectLongitud = (tablaEl) => {
  const contenedorDt = tablaEl.closest('.dt-container');
  const selectLongitud = contenedorDt?.querySelector('.dt-length select');
  if (!selectLongitud || selectLongitud.closest('[data-custom-select]')) return;

  const valorActual = String(selectLongitud.value || '10');
  const opciones = [...selectLongitud.options].map((opt) => ({
    valor: String(opt.value),
    texto: String(opt.textContent || opt.value).trim(),
  }));

  const envoltura = document.createElement('div');
  envoltura.className = 'inline-block w-24 align-middle mx-1';
  envoltura.setAttribute('data-custom-select', '');

  const relativo = document.createElement('div');
  relativo.className = 'relative';
  envoltura.appendChild(relativo);

  selectLongitud.parentNode?.insertBefore(envoltura, selectLongitud);
  selectLongitud.className = 'custom-select-native sr-only';
  selectLongitud.setAttribute('data-placeholder', valorActual);
  selectLongitud.setAttribute('tabindex', '-1');
  selectLongitud.setAttribute('aria-hidden', 'true');
  relativo.appendChild(selectLongitud);

  relativo.insertAdjacentHTML('beforeend', crearMarkupSelectLongitud(opciones, valorActual));
  inicializarSelectsPersonalizados(contenedorDt);
  window.lucide?.createIcons();
};

export class GestorDataTable {
  constructor(
    selector,
    {
      url = null,
      datos = [],
      columnas,
      ordenInicial = [[0, 'asc']],
      obtenerFiltros = null,
      longitudPagina = 10,
      alCargar = null,
      alAccionar = null,
      selectorDelegacion = null,
    } = {}
  ) {
    this.instancia = null;
    const tablaEl = document.querySelector(selector);
    if (!tablaEl) return;

    if (typeof window.DataTable !== 'function') {
      mostrarToast('error', 'No se pudo cargar la librería DataTables 3.1.5.');
      return;
    }

    if (window.DataTable.ext) {
      window.DataTable.ext.errMode = 'none';
    }

    const configuracion = {
      autoWidth: false,
      pageLength: longitudPagina,
      lengthMenu: [
        [10, 25, 50, 100],
        [10, 25, 50, 100],
      ],
      language: IDIOMA_ES,
      order: ordenInicial,
      columns: columnas,
    };

    if (url) {
      configuracion.serverSide = true;
      configuracion.processing = true;
      configuracion.ajax = async (datosSolicitud, callback) => {
        const filtros = typeof obtenerFiltros === 'function' ? obtenerFiltros() : {};
        const res = await Solicitud.post(
          url,
          { ...datosSolicitud, ...filtros },
          { claveCancelacion: `dt:${selector}` }
        );

        if (res.aborted) return;

        if (!res.ok || !res.data || res.data.error) {
          mostrarToast('error', res.data?.error || res.message || 'Error al cargar los registros.');
          callback({ draw: datosSolicitud.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
          return;
        }

        if (typeof alCargar === 'function') alCargar(res.data);
        callback(res.data);
      };
    } else {
      configuracion.data = datos;
    }

    this.instancia = new window.DataTable(tablaEl, configuracion);
    transformarSelectLongitud(tablaEl);
    this.instancia.on('draw', () => window.lucide?.createIcons());

    if (typeof alAccionar === 'function') {
      const contenedor = selectorDelegacion ? document.querySelector(selectorDelegacion) : tablaEl;
      contenedor?.addEventListener('click', (evento) => {
        const boton = evento.target.closest('button[data-accion]');
        if (!boton || boton.disabled) return;

        const filaDom = boton.closest('tr');
        const registro = filaDom ? this.instancia.row(filaDom).data() : null;
        if (!registro) return;

        alAccionar(boton.dataset.accion, registro, boton);
      });
    }
  }

  recargar(mantenerPagina = true) {
    this.instancia?.ajax.reload(null, !mantenerPagina);
  }

  actualizarDatos(nuevosDatos) {
    this.instancia?.clear().rows.add(nuevosDatos).draw();
  }

  filtrarColumna(indice, valor) {
    this.instancia?.column(indice).search(valor === 'todos' ? '' : valor).draw();
  }

  destruir() {
    this.instancia?.destroy();
  }

  obtenerInstancia() {
    return this.instancia;
  }
}
