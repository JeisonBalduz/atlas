import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import {
  limpiarAlertaFormulario,
  notificarErrorFormulario,
  notificarExitoFormulario,
} from '/js/components/alerta/alerta.js';
import { crearBadge } from '/js/components/badge/crearBadge.js';
import { inicializarRangoFechas } from '/js/components/calendario/calendario.js';
import { crearListaDetalle, Modal } from '/js/components/modal/Modal.js';
import {
  crearMarkupSelectPersonalizado,
  inicializarSelectsPersonalizados,
} from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import { columnasTablaBeneficios } from '/js/templates/tabla_beneficios.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

const leerReglasBeneficios = (formulario) => {
  if (!formulario?.dataset?.reglas) return {};
  try {
    return JSON.parse(formulario.dataset.reglas) || {};
  } catch {
    return {};
  }
};

const renderDetalleBeneficio = (registro) => {
  const esPendiente = registro.estatus === 'Pendiente';
  return crearListaDetalle([
    { etiqueta: 'Cédula', valorHtml: `<span class="font-semibold">${registro.cedula || '—'}</span>` },
    { etiqueta: 'Rol / Parentesco', valorHtml: registro.rol_parentesco || 'Beneficiario' },
    { etiqueta: 'Base contractual', valorHtml: registro.clausula || 'Contrato Colectivo' },
    { etiqueta: 'Periodicidad', valorHtml: registro.periodicidad || 'Según cláusula' },
    { etiqueta: 'Estatus actual', valorHtml: crearBadge(registro.estatus, esPendiente ? 'yellow' : 'green') },
    { etiqueta: 'Fecha de solicitud', valorHtml: formatearFecha(registro.fecha_solicitud) },
    {
      etiqueta: 'Fecha de aprobación',
      valorHtml: registro.fecha_aprobada ? formatearFecha(registro.fecha_aprobada) : 'Pendiente por aprobar',
      anchoCompleto: true,
    },
  ]);
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();

  const modalDetalle = new Modal('#modal-detalle-beneficio');
  const filtroEstatus = document.getElementById('filtro-estatus-beneficio');
  const formulario = document.getElementById('formulario-beneficios');
  const reglasPorBeneficio = leerReglasBeneficios(formulario);

  const selectPersona = document.getElementById('persona_id');
  const selectBeneficio = document.getElementById('beneficio_id');
  const inputDtBeneficioId = document.getElementById('dtbeneficio_id');
  const inputSolicitud = document.getElementById('fecha_solicitud');
  const inputAprobada = document.getElementById('fecha_aprobada');
  const inputArchivo = document.getElementById('archivo_soporte');
  const tituloForm = document.getElementById('titulo-form-beneficio');
  const textoBtnSubmit = document.getElementById('texto-btn-submit-beneficio');
  const btnCancelarEdicion = document.getElementById('btn-cancelar-edicion-beneficio');

  const panelRegla = document.getElementById('panel-regla-beneficio');
  const nodoClausula = document.getElementById('regla-clausula');
  const nodoBadgeDoc = document.getElementById('regla-badge-doc');
  const nodoPeriodicidad = document.getElementById('regla-periodicidad');
  const nodoNota = document.getElementById('regla-nota');
  const bloqueDoc = document.getElementById('bloque-documento-beneficio');
  const nodoDocVigente = document.getElementById('estado-doc-vigente');
  const contenedorTipoDoc = document.getElementById('contenedor-select-tipo-doc');

  let tieneDocumentoVigentePrevio = false;
  let rangoFechas = inicializarRangoFechas(inputSolicitud, inputAprobada);

  // Recibe el id de persona y su etiqueta; inyecta la opción en el select remoto y sincroniza la UI.
  const seleccionarPersonaRemota = (personaId, nombre, subetiqueta = '') => {
    if (!selectPersona || !personaId) return;
    const valor = String(personaId);
    let opcion = Array.from(selectPersona.options).find((op) => op.value === valor);
    if (!opcion) {
      opcion = document.createElement('option');
      opcion.value = valor;
      opcion.textContent = nombre || `Persona #${valor}`;
      if (subetiqueta) opcion.dataset.sublabel = subetiqueta;
      selectPersona.appendChild(opcion);
    }
    selectPersona.value = valor;
    selectPersona.dispatchEvent(new Event('change', { bubbles: true }));
  };

  // Limpia el estado de edición y restaura el formulario de beneficios al modo de creación.
  const salirModoEdicion = () => {
    if (inputDtBeneficioId) inputDtBeneficioId.value = '';
    if (tituloForm) tituloForm.textContent = 'Asignar Beneficio';
    if (textoBtnSubmit) textoBtnSubmit.textContent = 'Registrar Beneficio';
    btnCancelarEdicion?.classList.remove('inline-flex');
    btnCancelarEdicion?.classList.add('hidden');
    formulario?.reset();
    rangoFechas?.inicio?.clear?.(false);
    rangoFechas?.fin?.clear?.(false);
    rangoFechas?.fin?.set?.('minDate', '1930-01-01');
    limpiarAlertaFormulario(formulario);
    panelRegla?.classList.add('hidden');
    tieneDocumentoVigentePrevio = false;
  };

  // Recibe el registro seleccionado en la tabla; carga todos sus valores en el formulario para editarlo.
  const cargarBeneficioEnFormulario = (registro) => {
    if (!registro?.dtbeneficio_id) return;

    limpiarAlertaFormulario(formulario);
    if (inputDtBeneficioId) inputDtBeneficioId.value = String(registro.dtbeneficio_id);
    if (tituloForm) tituloForm.textContent = 'Editar Beneficio';
    if (textoBtnSubmit) textoBtnSubmit.textContent = 'Actualizar Beneficio';
    btnCancelarEdicion?.classList.remove('hidden');
    btnCancelarEdicion?.classList.add('inline-flex');

    const subetiqueta = [registro.cedula, registro.rol_parentesco].filter(Boolean).join(' · ');
    seleccionarPersonaRemota(registro.persona_id, registro.beneficiario, subetiqueta);

    if (selectBeneficio) {
      selectBeneficio.value = String(registro.beneficio_id || '');
      selectBeneficio.dispatchEvent(new Event('change', { bubbles: true }));
    }

    if (inputSolicitud) {
      inputSolicitud.value = registro.fecha_solicitud || '';
      if (rangoFechas?.inicio?.setDate) {
        rangoFechas.inicio.setDate(registro.fecha_solicitud || null, false);
      }
    }
    if (rangoFechas?.fin?.set) {
      rangoFechas.fin.set('minDate', registro.fecha_solicitud || '1930-01-01');
    }
    if (inputAprobada) {
      inputAprobada.value = registro.fecha_aprobada || '';
      if (rangoFechas?.fin?.setDate) {
        rangoFechas.fin.setDate(registro.fecha_aprobada || null, false);
      }
    }
    if (inputArchivo) inputArchivo.value = '';

    formulario?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  btnCancelarEdicion?.addEventListener('click', salirModoEdicion);

  const consultarVigenciaPrevio = async () => {
    tieneDocumentoVigentePrevio = false;
    if (!nodoDocVigente) return;
    nodoDocVigente.classList.add('hidden');
    nodoDocVigente.innerHTML = '';

    const personaId = Number(selectPersona?.value || 0);
    const beneficioId = Number(selectBeneficio?.value || 0);
    const regla = reglasPorBeneficio[String(beneficioId)];

    if (!personaId || !beneficioId || !regla?.requiere_documento) return;

    const res = await Solicitud.get('/api/beneficios/estado-documental', {
      params: { persona_id: personaId, beneficio_id: beneficioId },
      claveCancelacion: 'doc-beneficio',
    });

    if (res.aborted || !res.ok || !res.data?.ok) return;
    const info = res.data.data || {};
    tieneDocumentoVigentePrevio = Boolean(info.tiene_vigente);

    if (tieneDocumentoVigentePrevio && Array.isArray(info.documentos_vigentes) && info.documentos_vigentes[0]) {
      const doc = info.documentos_vigentes[0];
      nodoDocVigente.innerHTML = `Documento <strong>Vigente</strong> ya cargado en expediente (${doc.nombre_original}). Puedes conservarlo o subir una nueva versión.`;
      nodoDocVigente.classList.remove('hidden');
    }
  };

  const actualizarPanelRegla = () => {
    const beneficioId = String(selectBeneficio?.value || '');
    const regla = reglasPorBeneficio[beneficioId];

    if (!regla || !panelRegla) {
      panelRegla?.classList.add('hidden');
      return;
    }

    if (nodoClausula) nodoClausula.textContent = regla.clausula || 'Contrato Colectivo';
    if (nodoPeriodicidad) nodoPeriodicidad.textContent = `Periodicidad: ${regla.etiqueta_periodicidad}`;
    if (nodoNota) nodoNota.textContent = regla.nota || '';
    if (nodoBadgeDoc) {
      nodoBadgeDoc.innerHTML = regla.requiere_documento
        ? crearBadge('Documento obligatorio', 'yellow')
        : crearBadge('Sin documento requerido', 'green');
    }

    if (regla.requiere_documento && Array.isArray(regla.documentos) && regla.documentos.length > 0) {
      bloqueDoc?.classList.remove('hidden');
      if (contenedorTipoDoc) {
        const opcionesDoc = {};
        regla.documentos.forEach((d) => {
          opcionesDoc[String(d.id)] = d.nombre;
        });
        contenedorTipoDoc.innerHTML = crearMarkupSelectPersonalizado({
          id: 'tipo_documento_id',
          nombre: 'tipo_documento_id',
          etiqueta: 'Tipo de soporte exigido',
          opciones: opcionesDoc,
          valorSeleccionado: String(regla.documentos[0].id),
        });
        inicializarSelectsPersonalizados(contenedorTipoDoc);
      }
      consultarVigenciaPrevio();
    } else {
      bloqueDoc?.classList.add('hidden');
      if (contenedorTipoDoc) contenedorTipoDoc.innerHTML = '';
      if (inputArchivo) inputArchivo.value = '';
    }

    panelRegla.classList.remove('hidden');
  };

  selectBeneficio?.addEventListener('change', actualizarPanelRegla);
  selectPersona?.addEventListener('change', consultarVigenciaPrevio);

  const aprobarBeneficio = async (registro) => {
    const res = await Solicitud.post(`/api/beneficios/${registro.dtbeneficio_id}/aprobar`);
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudo aprobar el beneficio.');
      return false;
    }

    mostrarToast('success', res.data.message || `Beneficio aprobado para ${registro.beneficiario}.`);
    await cargarBeneficios();
    return true;
  };

  const abrirModalBeneficio = (registro) => {
    const esPendiente = registro.estatus === 'Pendiente';

    modalDetalle.abrir({
      titulo: registro.tipo_beneficio || 'Beneficio socioeconómico',
      subtitulo: `${registro.beneficiario || ''} · ${registro.rol_parentesco || ''}`,
      contenidoHtml: renderDetalleBeneficio(registro),
      mostrarConfirmar: esPendiente,
      textoConfirmar: 'Aprobar hoy',
      varianteConfirmar: 'green',
      alConfirmar: esPendiente ? () => aprobarBeneficio(registro) : null,
    });
  };

  const tabla = new GestorDataTable('#tabla-beneficios', {
    datos: [],
    ordenInicial: [[4, 'desc']],
    columnas: columnasTablaBeneficios(),
    alAccionar: (accion, registro) => {
      if (accion === 'editar') return cargarBeneficioEnFormulario(registro);
      if (accion === 'aprobar') return aprobarBeneficio(registro);
      abrirModalBeneficio(registro);
    },
  });

  const cargarBeneficios = async () => {
    const res = await Solicitud.get('/api/beneficios');
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudieron consultar los beneficios.');
      return;
    }

    const lista = Array.isArray(res.data.data) ? res.data.data : [];
    const resumenOtorgados = document.getElementById('resumen-beneficios-otorgados');
    if (resumenOtorgados) {
      resumenOtorgados.textContent = `${lista.length} asignaciones registradas en BD`;
    }

    tabla.actualizarDatos(lista);
    if (filtroEstatus?.value && filtroEstatus.value !== 'todos') {
      tabla.filtrarColumna(6, filtroEstatus.value);
    }
  };

  filtroEstatus?.addEventListener('change', (evento) => {
    tabla.filtrarColumna(6, evento.target.value);
  });

  let enviando = false;

  formulario?.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    if (enviando) return;

    const dtBeneficioId = Number(inputDtBeneficioId?.value || 0);
    const esEdicion = dtBeneficioId > 0;
    const personaId = Number(selectPersona?.value || 0);
    const beneficioId = Number(selectBeneficio?.value || 0);
    const fechaSolicitud = inputSolicitud?.value || '';
    const fechaAprobada = inputAprobada?.value || '';
    const regla = reglasPorBeneficio[String(beneficioId)];

    if (!personaId || !beneficioId || !fechaSolicitud) {
      notificarErrorFormulario(formulario, 'Selecciona beneficiario, tipo de beneficio y fecha de solicitud.');
      return;
    }

    if (fechaAprobada && fechaAprobada < fechaSolicitud) {
      notificarErrorFormulario(formulario, 'La fecha de aprobación no puede ser menor que la fecha de solicitud.');
      return;
    }

    const tieneArchivoAdjunto = Boolean(inputArchivo?.files && inputArchivo.files.length > 0);
    if (regla?.requiere_documento && !tieneArchivoAdjunto && !tieneDocumentoVigentePrevio) {
      notificarErrorFormulario(
        formulario,
        `Según ${regla.clausula}, este beneficio exige adjuntar el soporte documental obligatorio.`
      );
      return;
    }

    enviando = true;
    const btnSubmit = formulario.querySelector('button[type="submit"]');
    if (btnSubmit) btnSubmit.disabled = true;

    try {
      const formData = new FormData(formulario);
      const endpoint = esEdicion ? `/api/beneficios/${dtBeneficioId}` : '/api/beneficios';
      const res = await Solicitud.post(endpoint, formData);

      if (!res.ok || !res.data?.ok) {
        notificarErrorFormulario(formulario, res.message || 'No se pudo guardar el beneficio.');
        return;
      }

      notificarExitoFormulario(
        formulario,
        res.data.message || (esEdicion ? 'Beneficio actualizado correctamente.' : 'Beneficio asignado correctamente.')
      );
      salirModoEdicion();
      await cargarBeneficios();
    } finally {
      enviando = false;
      if (btnSubmit) btnSubmit.disabled = false;
    }
  });

  cargarBeneficios();
});
