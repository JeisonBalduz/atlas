import {
  crearAlertaConfirmacion,
  crearBadge,
  crearBotonAccion,
} from '/js/templates/template_ui.js';
import { formatearFecha } from '/js/utils/dom.js';

/**
 * Indicador visual mientras se consulta la carga familiar en el servidor.
 */
export const htmlCargaFamiliares = () => `
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

/**
 * Mensaje de error con botón "Reintentar" dentro del modal de familiares.
 */
export const htmlErrorFamiliares = (mensaje) => `
  <div class="flex flex-col items-center justify-center gap-4 py-10 text-center">
    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-red-50 text-red-600">
      <i data-lucide="alert-circle" class="h-5 w-5" aria-hidden="true"></i>
    </div>
    <div class="space-y-1">
      <p class="text-sm font-semibold text-zinc-900">No se pudo cargar la información</p>
      <p class="text-xs text-zinc-500">${mensaje}</p>
    </div>
    <button
      type="button"
      data-reintentar-familiares
      class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-zinc-800 focus-visible:outline focus-visible:outline-offset-2 focus-visible:outline-zinc-950 active:scale-[0.98] cursor-pointer"
    >
      <i data-lucide="refresh-cw" class="h-3.5 w-3.5" aria-hidden="true"></i>
      <span>Reintentar</span>
    </button>
  </div>
`;

/**
 * Construye una fila de la tabla de familiares dentro del modal.
 */
const htmlFilaFamiliar = (fam) => {
  const edadTexto = fam.edad !== null && fam.edad !== undefined ? `${fam.edad} años` : '—';
  const editarBtn = crearBotonAccion({
    etiqueta: 'Editar',
    icono: 'pencil',
    variante: 'zinc',
    href: `/empleado/familiar/editar/${fam.familia_id}`,
    titulo: `Editar a ${fam.nombre_completo || 'familiar'}`,
  });

  return `
    <tr class="hover:bg-slate-50/70 transition-colors">
      <td class="px-3.5 py-2.5 font-medium text-zinc-900 whitespace-nowrap">${fam.cedula || '—'}</td>
      <td class="px-3.5 py-2.5 font-medium text-zinc-900">${fam.nombre_completo || '—'}</td>
      <td class="px-3.5 py-2.5 whitespace-nowrap">${crearBadge(fam.parentesco || 'Sin definir', 'marca')}</td>
      <td class="px-3.5 py-2.5 whitespace-nowrap text-zinc-600">
        <span>${formatearFecha(fam.fecha_nacimiento)}</span>
        <span class="block text-xs text-zinc-400">${edadTexto}</span>
      </td>
      <td class="px-3.5 py-2.5 text-center whitespace-nowrap text-zinc-700">${fam.genero || '—'}</td>
      <td class="px-3.5 py-2.5 text-center whitespace-nowrap font-medium text-zinc-700">T-${fam.tomo} / F-${fam.folio}</td>
      <td class="px-3.5 py-2.5 text-right whitespace-nowrap">${editarBtn}</td>
    </tr>
  `;
};

/**
 * Tabla completa de carga familiar del trabajador (o estado vacío si no tiene familiares).
 */
export const htmlTablaFamiliares = (payload, empleadoIdFallback) => {
  const empleado = payload?.empleado || {};
  const empleadoId = empleado.empleado_id || empleadoIdFallback;
  const lista = Array.isArray(payload?.data) ? payload.data : [];

  if (lista.length === 0) {
    return `
      <div class="flex flex-col items-center justify-center gap-4 py-10 text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-zinc-500">
          <i data-lucide="users" class="h-6 w-6" aria-hidden="true"></i>
        </div>
        <div class="max-w-sm space-y-1">
          <p class="text-sm font-semibold text-zinc-900">Este trabajador no tiene familiares registrados</p>
          <p class="text-xs text-zinc-500">Puedes asociar su carga familiar ahora mismo usando el botón inferior.</p>
        </div>
        <a
          href="/empleado/registrar/familiar?empleado=${empleadoId}"
          class="inline-flex items-center gap-1.5 rounded-lg bg-marca px-4 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-marca-700 focus-visible:outline focus-visible:outline-offset-2 focus-visible:outline-marca active:scale-[0.98]"
        >
          <i data-lucide="user-plus" class="h-3.5 w-3.5" aria-hidden="true"></i>
          <span>Registrar familiar</span>
        </a>
      </div>
    `;
  }

  return `
    <div class="space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
        <div class="text-xs text-zinc-600">
          <span class="font-semibold text-zinc-900">${lista.length}</span>
          ${lista.length === 1 ? 'familiar registrado' : 'familiares registrados'}
          ${empleado.departamento ? ` · <span>${empleado.departamento}</span>` : ''}
        </div>
        <a
          href="/empleado/registrar/familiar?empleado=${empleadoId}"
          class="inline-flex items-center gap-1.5 rounded-lg bg-marca px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-marca-700 focus-visible:outline focus-visible:outline-offset-2 focus-visible:outline-marca active:scale-[0.98]"
        >
          <i data-lucide="user-plus" class="h-3.5 w-3.5" aria-hidden="true"></i>
          <span>Agregar familiar</span>
        </a>
      </div>

      <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-zinc-500">
            <tr>
              <th class="px-3.5 py-2.5">Cédula</th>
              <th class="px-3.5 py-2.5">Nombre completo</th>
              <th class="px-3.5 py-2.5">Parentesco</th>
              <th class="px-3.5 py-2.5">Nacimiento / Edad</th>
              <th class="px-3.5 py-2.5 text-center">Género</th>
              <th class="px-3.5 py-2.5 text-center">Tomo / Folio</th>
              <th class="px-3.5 py-2.5 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">${lista.map(htmlFilaFamiliar).join('')}</tbody>
        </table>
      </div>
    </div>
  `;
};

/**
 * Mensaje de confirmación para activar o desactivar un empleado.
 */
export const htmlAlertaEstadoEmpleado = (nombreCompleto, esActivacion) =>
  crearAlertaConfirmacion({
    variante: esActivacion ? 'green' : 'red',
    icono: esActivacion ? 'user-check' : '',
    mensajeHtml: esActivacion
      ? `¿Estás seguro de que deseas activar a <strong>${nombreCompleto}</strong>? El trabajador volverá a figurar como <strong>Activo</strong> en nómina.`
      : `¿Deseas marcar como <strong>Inactivo</strong> a <strong>${nombreCompleto}</strong>? El trabajador permanecerá en el historial con estatus inactivo.`,
  });

/**
 * Formulario para asignar discapacidad y carnet CONAPDIS a un empleado.
 */
export const htmlFormularioDiscapacidad = (catalogos) => {
  const opcionesTipo = Object.values(catalogos?.tipos || {})
    .map((nombre) => `<option value="${nombre}">${nombre}</option>`)
    .join('');

  const opcionesGrado = Object.entries(catalogos?.grados || {})
    .map(([id, nombre]) => `<option value="${id}">${nombre}</option>`)
    .join('');

  const hoy = new Date().toISOString().slice(0, 10);
  const enCincoAnios = new Date(Date.now() + 5 * 365 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10);

  return `
    <form id="form-asignar-discapacidad" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm" enctype="multipart/form-data">
      <div>
        <label for="disc-tipo" class="block text-xs font-semibold text-zinc-700 mb-1">Tipo de discapacidad</label>
        <select id="disc-tipo" name="tipo_discapacidad" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-marca focus:outline-none">
          <option value="">Seleccionar tipo...</option>
          ${opcionesTipo}
        </select>
      </div>
      <div>
        <label for="disc-grado" class="block text-xs font-semibold text-zinc-700 mb-1">Grado de discapacidad <span class="text-red-500">*</span></label>
        <select id="disc-grado" name="grado_discapacidad_id" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-marca focus:outline-none">
          <option value="">Seleccionar grado...</option>
          ${opcionesGrado}
        </select>
      </div>
      <div>
        <label for="disc-carnet" class="block text-xs font-semibold text-zinc-700 mb-1">N.° de Carnet CONAPDIS <span class="text-red-500">*</span></label>
        <input id="disc-carnet" name="numero_carnet" type="text" maxlength="30" required placeholder="Ej. CONAPDIS-123456" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-zinc-900 focus:border-marca focus:outline-none" />
      </div>
      <div>
        <label for="disc-entidad" class="block text-xs font-semibold text-zinc-700 mb-1">Entidad emisora <span class="text-red-500">*</span></label>
        <input id="disc-entidad" name="entidad_emisora" type="text" maxlength="100" required value="CONAPDIS" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-zinc-900 focus:border-marca focus:outline-none" />
      </div>
      <div>
        <label for="disc-emision" class="block text-xs font-semibold text-zinc-700 mb-1">Fecha de emisión</label>
        <input id="disc-emision" name="fecha_emision_carnet" type="date" value="${hoy}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-zinc-900 focus:border-marca focus:outline-none" />
      </div>
      <div>
        <label for="disc-vencimiento" class="block text-xs font-semibold text-zinc-700 mb-1">Fecha de vencimiento</label>
        <input id="disc-vencimiento" name="fecha_vencimiento_carnet" type="date" value="${enCincoAnios}" class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-zinc-900 focus:border-marca focus:outline-none" />
      </div>
      <div class="md:col-span-2">
        <label for="disc-descripcion" class="block text-xs font-semibold text-zinc-700 mb-1">Descripción / Observaciones</label>
        <textarea id="disc-descripcion" name="descripcion" rows="2" placeholder="Detalles médicos o indicaciones relevantes..." class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-zinc-900 focus:border-marca focus:outline-none"></textarea>
      </div>
      <div class="md:col-span-2">
        <label for="disc-archivo" class="block text-xs font-semibold text-zinc-700 mb-1">Certificado / Carnet CONAPDIS (PDF o imagen, opcional)</label>
        <input id="disc-archivo" name="archivo" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-xs text-zinc-700 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-zinc-800" />
      </div>
    </form>
  `;
};
