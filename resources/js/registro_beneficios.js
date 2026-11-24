import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { crearBadge } from '/js/components/badge/crearBadge.js';
import { crearListaDetalle, Modal } from '/js/components/modal/Modal.js';
import { inicializarSelectsPersonalizados } from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import { columnasTablaBeneficios } from '/js/templates/tabla_beneficios.js';
import { formatearFecha } from '/js/utils/formatearFecha.js';

const renderDetalleBeneficio = (registro) => {
  const esPendiente = registro.estatus === 'Pendiente';
  return crearListaDetalle([
    { etiqueta: 'Cédula', valorHtml: `<span class="font-semibold">${registro.cedula || '—'}</span>` },
    { etiqueta: 'Rol / Parentesco', valorHtml: registro.rol_parentesco || 'Beneficiario' },
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
    tabla.actualizarDatos(lista);
    if (filtroEstatus?.value && filtroEstatus.value !== 'todos') {
      tabla.filtrarColumna(6, filtroEstatus.value);
    }
  };

  filtroEstatus?.addEventListener('change', (evento) => {
    tabla.filtrarColumna(6, evento.target.value);
  });

  const formulario = document.getElementById('formulario-beneficios');
  let enviando = false;

  formulario?.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    if (enviando) return;

    const personaId = Number(document.getElementById('persona_id')?.value || 0);
    const beneficioId = Number(document.getElementById('beneficio_id')?.value || 0);
    const fechaSolicitud = document.getElementById('fecha_solicitud')?.value || '';
    const fechaAprobada = document.getElementById('fecha_aprobada')?.value || '';

    if (!personaId || !beneficioId || !fechaSolicitud) {
      mostrarToast('error', 'Selecciona beneficiario, tipo de beneficio y fecha de solicitud.');
      return;
    }

    enviando = true;
    const btnSubmit = formulario.querySelector('button[type="submit"]');
    if (btnSubmit) btnSubmit.disabled = true;

    try {
      const res = await Solicitud.post('/api/beneficios', {
        persona_id: personaId,
        beneficio_id: beneficioId,
        fecha_solicitud: fechaSolicitud,
        fecha_aprobada: fechaAprobada || null,
      });

      if (!res.ok || !res.data?.ok) {
        mostrarToast('error', res.message || 'No se pudo asignar el beneficio.');
        return;
      }

      mostrarToast('success', res.data.message || 'Beneficio asignado correctamente.');
      formulario.reset();
      await cargarBeneficios();
    } finally {
      enviando = false;
      if (btnSubmit) btnSubmit.disabled = false;
    }
  });

  cargarBeneficios();
});
