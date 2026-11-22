/**
 * registrar_familiar.js
 * Formulario de registro de carga familiar conectado a POST /api/familiares
 * y GET /api/empleados/{id}, manteniendo los <select> hidratados desde PHP.
 */

import { InputValidador } from '/js/class/validator/InputValidador.js';
import { InputUI } from '/js/class/validator/InputUI.js';
import { Solicitud } from '/js/class/Solicitud.js';
import { inicializarSelectsPersonalizados } from '/js/components/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast.js';

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();

  const selectEmpleado = document.getElementById('empleado_id');
  const selectParentesco = document.getElementById('parentesco_id');
  const contenedorRecaudos = document.getElementById('contenedor-recaudos-familiar');
  const tarjetaTitular = document.getElementById('tarjeta-resumen-titular');
  const formulario = document.getElementById('formulario-registrar-familiar');

  let recaudosPorParentesco = {};
  try {
    const scriptRecaudos = document.getElementById('recaudos-parentesco-json');
    if (scriptRecaudos?.textContent) {
      recaudosPorParentesco = JSON.parse(scriptRecaudos.textContent) || {};
    }
  } catch {
    recaudosPorParentesco = {};
  }

  const escapar = (v) =>
    String(v ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#39;');

  const renderizarRecaudosFamiliar = (parentescoId) => {
    if (!contenedorRecaudos) return;
    const lista = recaudosPorParentesco[String(parentescoId || '')] || [];
    if (!Array.isArray(lista) || lista.length === 0) {
      contenedorRecaudos.innerHTML = `
        <p class="col-span-full rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-zinc-500">
          Selecciona un parentesco arriba para cargar los campos de documentos correspondientes.
        </p>`;
      return;
    }

    contenedorRecaudos.innerHTML = lista
      .map((rec) => {
        const esTitular = Boolean(rec.es_titular);
        const badge = rec.obligatorio
          ? '<span class="inline-flex items-center rounded-md bg-yellow-400/20 px-2 py-0.5 text-[11px] font-semibold text-yellow-900 whitespace-nowrap">Obligatorio</span>'
          : '<span class="inline-flex items-center rounded-md bg-slate-200/70 px-2 py-0.5 text-[11px] font-semibold text-zinc-700 whitespace-nowrap">Opcional</span>';

        const controlHtml = esTitular
          ? '<p class="rounded-lg bg-sky-50 px-3 py-2 text-xs text-sky-800">Este documento se verifica directamente en el expediente del trabajador titular.</p>'
          : `
            <div>
              <label for="doc-fam-${escapar(rec.tipo_documento_id)}" class="block text-[11px] font-medium text-zinc-600 mb-1">
                Adjuntar archivo (PDF/JPG/PNG)
              </label>
              <input
                id="doc-fam-${escapar(rec.tipo_documento_id)}"
                name="documentos[${escapar(rec.tipo_documento_id)}]"
                type="file"
                accept=".pdf,.jpg,.jpeg,.png,.webp"
                class="atlas-doc-fam-input w-full rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-zinc-700 file:mr-2.5 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-white hover:file:bg-zinc-800 cursor-pointer"
              />
            </div>`;

        return `
          <article class="rounded-2xl border ${rec.obligatorio ? 'border-yellow-300 bg-yellow-50/40' : 'border-slate-200 bg-slate-50/40'} p-4 flex flex-col justify-between gap-3">
            <div class="space-y-1">
              <div class="flex items-start justify-between gap-2">
                <h3 class="text-sm font-semibold text-zinc-900">${escapar(rec.etiqueta)}</h3>
                ${badge}
              </div>
              ${rec.motivo ? `<p class="text-xs text-zinc-500">${escapar(rec.motivo)}</p>` : ''}
            </div>
            ${controlHtml}
          </article>`;
      })
      .join('');

    InputValidador.archivo('.atlas-doc-fam-input', { tamanoMaximoMB: 5 });
  };

  selectParentesco?.addEventListener('change', () => {
    renderizarRecaudosFamiliar(selectParentesco.value);
  });
  if (selectParentesco?.value) {
    renderizarRecaudosFamiliar(selectParentesco.value);
  }

  let controladorTitular = null;

  const cargarResumenTitular = async (empleadoId) => {
    if (!empleadoId) {
      tarjetaTitular?.classList.add('hidden');
      return;
    }

    if (controladorTitular) {
      controladorTitular.abort();
    }
    controladorTitular = new AbortController();

    const res = await Solicitud.get(`/api/empleados/${empleadoId}`, {
      signal: controladorTitular.signal,
    });

    if (res.aborted) return;

    if (res.status === 401) {
      window.location.href = '/autenticacion';
      return;
    }

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
    const cargoDep = [emp.cargo, emp.departamento].filter(Boolean).join(' · ') || 'Sin asignar';

    document.getElementById('titular-nombre').textContent = emp.nombre_completo || `Empleado #${empleadoId}`;
    document.getElementById('titular-cedula').textContent = cedula;
    document.getElementById('titular-codigo').textContent = emp.codigo_empleado || '—';
    document.getElementById('titular-cargo').textContent = cargoDep;
    tarjetaTitular?.classList.remove('hidden');
  };

  selectEmpleado?.addEventListener('change', () => {
    cargarResumenTitular(selectEmpleado.value);
  });

  // Si llega preseleccionado por ?empleado=ID desde PHP, cargar la tarjeta resumen
  if (selectEmpleado?.value) {
    cargarResumenTitular(selectEmpleado.value);
  }

  // Validaciones de campos
  InputValidador.soloLetras('#primer_nombre, #segundo_nombre, #primer_apellido, #segundo_apellido', {
    minimo: 2,
    limite: 100,
    requerido: true,
  });
  InputValidador.soloNumeros('#cedula', { minimo: 6, limite: 9, requerido: true });
  InputValidador.soloNumeros('#tomo, #folio', { minimo: 1, limite: 6, requerido: true });
  InputValidador.requerido('#formulario-registrar-familiar select, #fecha_nacimiento');

  let enviando = false;

  formulario?.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    if (enviando) return;

    const camposRequeridos = formulario.querySelectorAll('[required]');
    let formularioValido = true;

    camposRequeridos.forEach((campo) => {
      if (!String(campo.value || '').trim()) {
        InputUI.establecerInvalido(campo, 'Este campo es obligatorio');
        formularioValido = false;
      }
    });

    if (!formularioValido) {
      mostrarToast('error', 'Completa todos los campos obligatorios del familiar.');
      return;
    }

    const btnSubmit = formulario.querySelector('button[type="submit"]');
    const textoSpan = btnSubmit?.querySelector('span');
    const textoOriginal = textoSpan?.textContent || 'Guardar Familiar';

    enviando = true;
    if (btnSubmit) btnSubmit.disabled = true;
    if (textoSpan) textoSpan.textContent = 'Guardando...';

    try {
      const formData = new FormData(formulario);
      const res = await Solicitud.post('/api/familiares', formData);

      if (res.status === 401) {
        window.location.href = '/autenticacion';
        return;
      }

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
