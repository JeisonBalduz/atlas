/**
 * registrar_familiar.js
 * Formulario de registro de carga familiar conectado a POST /api/familiares
 * y GET /api/empleados/{id}.
 */

import { Solicitud } from '/js/class/Solicitud.js';
import { InputUI } from '/js/class/validator/InputUI.js';
import { InputValidador } from '/js/class/validator/InputValidador.js';
import { inicializarSelectsPersonalizados } from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';
import { escaparHtml, leerJsonDeScript } from '/js/utils/dom.js';

const renderTarjetaRecaudoFamiliar = (rec) => {
  const badge = rec.obligatorio
    ? '<span class="inline-flex items-center rounded-md bg-yellow-400/20 px-2 py-0.5 text-[11px] font-semibold text-yellow-900 whitespace-nowrap">Obligatorio</span>'
    : '<span class="inline-flex items-center rounded-md bg-slate-200/70 px-2 py-0.5 text-[11px] font-semibold text-zinc-700 whitespace-nowrap">Opcional</span>';

  const controlHtml = rec.es_titular
    ? '<p class="rounded-lg bg-sky-50 px-3 py-2 text-xs text-sky-800">Este documento se verifica directamente en el expediente del trabajador titular.</p>'
    : `
      <div>
        <label for="doc-fam-${escaparHtml(rec.tipo_documento_id)}" class="block text-[11px] font-medium text-zinc-600 mb-1">
          Adjuntar archivo (PDF/JPG/PNG)
        </label>
        <input
          id="doc-fam-${escaparHtml(rec.tipo_documento_id)}"
          name="documentos[${escaparHtml(rec.tipo_documento_id)}]"
          type="file"
          accept=".pdf,.jpg,.jpeg,.png,.webp"
          class="atlas-doc-fam-input w-full rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-zinc-700 file:mr-2.5 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-white hover:file:bg-zinc-800 cursor-pointer"
        />
      </div>`;

  return `
    <article class="rounded-2xl border ${rec.obligatorio ? 'border-yellow-300 bg-yellow-50/40' : 'border-slate-200 bg-slate-50/40'} p-4 flex flex-col justify-between gap-3">
      <div class="space-y-1">
        <div class="flex items-start justify-between gap-2">
          <h3 class="text-sm font-semibold text-zinc-900">${escaparHtml(rec.etiqueta)}</h3>
          ${badge}
        </div>
        ${rec.motivo ? `<p class="text-xs text-zinc-500">${escaparHtml(rec.motivo)}</p>` : ''}
      </div>
      ${controlHtml}
    </article>`;
};

const renderRecaudosFamiliar = (lista) => {
  if (!Array.isArray(lista) || lista.length === 0) {
    return `
      <p class="col-span-full rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-zinc-500">
        Selecciona un parentesco arriba para cargar los campos de documentos correspondientes.
      </p>`;
  }
  return lista.map(renderTarjetaRecaudoFamiliar).join('');
};

const validarCamposRequeridos = (formulario) => {
  let valido = true;
  formulario.querySelectorAll('[required]').forEach((campo) => {
    if (!String(campo.value || '').trim()) {
      InputUI.establecerInvalido(campo, 'Este campo es obligatorio');
      valido = false;
    }
  });

  if (!valido) {
    mostrarToast('error', 'Completa todos los campos obligatorios del familiar.');
  }
  return valido;
};

const configurarValidaciones = () => {
  InputValidador.soloLetras('#primer_nombre, #segundo_nombre, #primer_apellido, #segundo_apellido', {
    minimo: 2,
    limite: 100,
    requerido: true,
  });
  InputValidador.soloNumeros('#cedula', { minimo: 6, limite: 9, requerido: true });
  InputValidador.soloNumeros('#tomo, #folio', { minimo: 1, limite: 6, requerido: true });
  InputValidador.requerido('#formulario-registrar-familiar select, #fecha_nacimiento');
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();
  configurarValidaciones();

  const selectEmpleado = document.getElementById('empleado_id');
  const selectParentesco = document.getElementById('parentesco_id');
  const contenedorRecaudos = document.getElementById('contenedor-recaudos-familiar');
  const tarjetaTitular = document.getElementById('tarjeta-resumen-titular');
  const formulario = document.getElementById('formulario-registrar-familiar');
  const recaudosPorParentesco = leerJsonDeScript('recaudos-parentesco-json', {});

  const actualizarRecaudos = (parentescoId) => {
    if (!contenedorRecaudos) return;
    const lista = recaudosPorParentesco[String(parentescoId || '')] || [];
    contenedorRecaudos.innerHTML = renderRecaudosFamiliar(lista);
    if (Array.isArray(lista) && lista.length > 0) {
      InputValidador.archivo('.atlas-doc-fam-input', { tamanoMaximoMB: 5 });
    }
  };

  const cargarResumenTitular = async (empleadoId) => {
    if (!empleadoId) {
      tarjetaTitular?.classList.add('hidden');
      return;
    }

    const res = await Solicitud.get(`/api/empleados/${empleadoId}`, {
      claveCancelacion: 'resumen-titular',
    });

    if (res.aborted) return;
    if (!res.ok || !res.data?.ok || !res.data?.data) {
      tarjetaTitular?.classList.add('hidden');
      return;
    }

    const emp = res.data.data;
    const persona = emp.persona || {};
    const cedula =
      persona.nacionalidad && persona.cedula
        ? `${String(persona.nacionalidad).toUpperCase()}-${persona.cedula}`
        : '—';

    document.getElementById('titular-nombre').textContent = emp.nombre_completo || `Empleado #${empleadoId}`;
    document.getElementById('titular-cedula').textContent = cedula;
    document.getElementById('titular-codigo').textContent = emp.codigo_empleado || '—';
    document.getElementById('titular-cargo').textContent = [emp.cargo, emp.departamento].filter(Boolean).join(' · ') || 'Sin asignar';
    tarjetaTitular?.classList.remove('hidden');
  };

  selectParentesco?.addEventListener('change', () => actualizarRecaudos(selectParentesco.value));
  if (selectParentesco?.value) actualizarRecaudos(selectParentesco.value);

  selectEmpleado?.addEventListener('change', () => cargarResumenTitular(selectEmpleado.value));
  if (selectEmpleado?.value) cargarResumenTitular(selectEmpleado.value);

  let enviando = false;

  formulario?.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    if (enviando || !validarCamposRequeridos(formulario)) return;

    const btnSubmit = formulario.querySelector('button[type="submit"]');
    const textoSpan = btnSubmit?.querySelector('span');
    const textoOriginal = textoSpan?.textContent || 'Guardar Familiar';

    enviando = true;
    if (btnSubmit) btnSubmit.disabled = true;
    if (textoSpan) textoSpan.textContent = 'Guardando...';

    try {
      const res = await Solicitud.post('/api/familiares', new FormData(formulario));
      if (!res.ok || !res.data?.ok) {
        mostrarToast('error', res.message || 'No se pudo registrar el familiar.');
        return;
      }

      mostrarToast('success', res.data.message || 'Familiar registrado correctamente.');
      setTimeout(() => {
        window.location.href = '/empleado/registros/familiar';
      }, 650);
    } finally {
      enviando = false;
      if (btnSubmit) btnSubmit.disabled = false;
      if (textoSpan) textoSpan.textContent = textoOriginal;
    }
  });

  document.getElementById('btn-limpiar-familiar')?.addEventListener('click', () => {
    tarjetaTitular?.classList.add('hidden');
  });
});
