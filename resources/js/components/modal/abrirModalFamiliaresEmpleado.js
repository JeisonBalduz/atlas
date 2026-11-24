import { Solicitud } from '/js/class/Solicitud.js';
import { renderTablaFamiliaresEmpleado } from '/js/templates/tabla_empleados.js';

const renderCargaFamiliares = () => `
  <div class="flex flex-col items-center justify-center gap-3 py-12 text-center" role="status" aria-live="polite">
    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-marca-50 text-marca">
      <i data-lucide="loader-2" class="h-5 w-5 animate-spin" aria-hidden="true"></i>
    </div>
    <div class="space-y-1">
      <p class="text-sm font-medium text-zinc-900">Consultando carga familiar...</p>
      <p class="text-xs text-zinc-500">Obteniendo familiares vinculados desde la base de datos.</p>
    </div>
  </div>
`;

const renderErrorFamiliares = (mensaje) => `
  <div class="flex flex-col items-center justify-center gap-4 py-10 text-center">
    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-red-50 text-red-600">
      <i data-lucide="alert-circle" class="h-5 w-5" aria-hidden="true"></i>
    </div>
    <div class="space-y-1">
      <p class="text-sm font-semibold text-zinc-900">No se pudo cargar la información</p>
      <p class="text-xs text-zinc-500">${mensaje}</p>
    </div>
    <button type="button" data-reintentar-familiares class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-zinc-800 cursor-pointer">
      <i data-lucide="refresh-cw" class="h-3.5 w-3.5" aria-hidden="true"></i>
      <span>Reintentar</span>
    </button>
  </div>
`;

/**
 * Abre el modal reutilizable de carga familiar de un empleado consultando `/api/empleados/{id}/familiares`.
 * Reutilizado sin duplicar código en `registros_empleados.js` y `editar_empleado.js`.
 *
 * @param {import('/js/components/modal/Modal.js').Modal} modalFamiliares
 * @param {{ empleado_id: number|string, nombre_completo?: string, codigo_empleado?: string, cedula?: string, cargo?: string }} empleado
 */
export const abrirModalFamiliaresEmpleado = async (modalFamiliares, empleado) => {
  if (!modalFamiliares || !empleado?.empleado_id) return;

  const subtituloPartes = [empleado.codigo_empleado, empleado.cedula, empleado.cargo].filter(Boolean);

  modalFamiliares.abrir({
    titulo: `Carga familiar · ${empleado.nombre_completo || 'Trabajador'}`,
    subtitulo: subtituloPartes.join(' · '),
    contenidoHtml: renderCargaFamiliares(),
  });

  const res = await Solicitud.get(`/api/empleados/${empleado.empleado_id}/familiares`);
  if (!res.ok || !res.data?.ok) {
    modalFamiliares.establecerContenido(renderErrorFamiliares(res.message || 'No se pudo consultar la carga familiar.'));
    return;
  }

  modalFamiliares.establecerContenido(renderTablaFamiliaresEmpleado(res.data, empleado.empleado_id));
};
