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
  const tarjetaTitular = document.getElementById('tarjeta-resumen-titular');
  const formulario = document.getElementById('formulario-registrar-familiar');

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
