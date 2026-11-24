import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import {
  crearAlertaConfirmacion,
  limpiarAlertaFormulario,
  notificarErrorFormulario,
  notificarExitoFormulario,
} from '/js/components/alerta/alerta.js';
import { Modal } from '/js/components/modal/Modal.js';
import {
  crearMarkupSelectPersonalizado,
  inicializarSelectsPersonalizados,
} from '/js/components/select/selectPersonalizado.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import { columnasTablaUsuarios } from '/js/templates/tabla_usuarios.js';

const leerJsonDataset = (nodo, clave, fallback) => {
  if (!nodo?.dataset?.[clave]) return fallback;
  try {
    return JSON.parse(nodo.dataset[clave]) || fallback;
  } catch {
    return fallback;
  }
};

const renderFormularioEditarUsuario = (usuario, rolesMapa) => `
  <form id="form-editar-usuario" class="space-y-4" novalidate>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="edit-usr-nombre" class="block text-sm font-medium text-zinc-900 mb-1.5">Nombre de usuario *</label>
        <input id="edit-usr-nombre" name="nombre_usuario" type="text" maxlength="30" value="${usuario.nombre_usuario || ''}" required class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
      </div>
      <div>
        ${crearMarkupSelectPersonalizado({
          id: 'edit-usr-rol',
          nombre: 'rol_id',
          etiqueta: 'Rol asignado *',
          opciones: rolesMapa,
          valorSeleccionado: String(usuario.rol_id || ''),
          requerido: true,
        })}
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="edit-usr-clave" class="block text-sm font-medium text-zinc-900 mb-1.5">Nueva contraseña (opcional)</label>
        <input id="edit-usr-clave" name="contrasena" type="password" placeholder="Dejar vacío para mantener" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
      </div>
      <div>
        <label for="edit-usr-pin" class="block text-sm font-medium text-zinc-900 mb-1.5">PIN (opcional)</label>
        <input id="edit-usr-pin" name="pin" type="number" placeholder="Ej: 1234" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white" />
      </div>
    </div>
    <label class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 cursor-pointer">
      <div>
        <span class="block text-sm font-medium text-zinc-900">Restablecer permisos base del rol</span>
        <span class="block text-xs text-zinc-500">Sincroniza los permisos de este usuario con la plantilla de su rol.</span>
      </div>
      <input type="checkbox" name="heredar_permisos_rol" class="h-4 w-4 rounded border-slate-300 text-marca focus:ring-marca-500" />
    </label>
  </form>
`;

const renderMatrizPermisosModal = (catalogo, idsActivos) => {
  const setActivos = new Set((idsActivos || []).map(Number));

  const bloques = (catalogo || [])
    .map((grupo) => {
      const checks = (grupo.permisos || [])
        .map((p) => {
          const marcado = setActivos.has(Number(p.id)) ? 'checked' : '';
          return `
            <label class="flex items-start gap-2.5 rounded-lg border border-slate-200 bg-white p-2.5 text-xs hover:bg-slate-50 cursor-pointer">
              <input type="checkbox" name="permiso_ids[]" value="${p.id}" ${marcado} class="mt-0.5 h-4 w-4 rounded border-slate-300 text-marca focus:ring-marca-500" />
              <span class="min-w-0">
                <strong class="block text-zinc-900">${p.codigo}</strong>
                <span class="block text-zinc-500">${p.descripcion}</span>
              </span>
            </label>
          `;
        })
        .join('');

      return `
        <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3.5 space-y-2.5">
          <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-600">Módulo: ${grupo.modulo}</h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">${checks}</div>
        </section>
      `;
    })
    .join('');

  return `
    <form id="form-permisos-usuario" class="space-y-3 max-h-[60vh] overflow-y-auto pr-1" novalidate>
      <div class="flex items-center justify-between gap-2 pb-1">
        <span class="text-xs font-medium text-zinc-500">Marca o desmarca los permisos específicos de esta cuenta.</span>
        <div class="flex items-center gap-2">
          <button type="button" data-marcar-todos="1" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-zinc-700 hover:bg-slate-100 cursor-pointer">Marcar todos</button>
          <button type="button" data-marcar-todos="0" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-zinc-700 hover:bg-slate-100 cursor-pointer">Ninguno</button>
        </div>
      </div>
      ${bloques}
    </form>
  `;
};

document.addEventListener('DOMContentLoaded', () => {
  inicializarSelectsPersonalizados();

  const formulario = document.getElementById('formulario-usuarios');
  const rolesMapa = leerJsonDataset(formulario, 'roles', {});
  const catalogoPermisos = leerJsonDataset(formulario, 'catalogoPermisos', []);
  const filtroEstatus = document.getElementById('filtro-estatus-usuario');

  const modalGestion = new Modal('#modal-gestion-usuario');
  const modalEstatus = new Modal('#modal-estatus-usuario');

  const actualizarResumenCards = (resumen) => {
    if (!resumen) return;
    const actEl = document.getElementById('resumen-usuarios-activos');
    const totEl = document.getElementById('resumen-usuarios-totales');
    const rolEl = document.getElementById('resumen-roles-total');

    if (actEl) actEl.textContent = String(resumen.activos ?? 0);
    if (totEl) {
      totEl.textContent = `${resumen.total_usuarios ?? 0} registradas · ${resumen.inactivos ?? 0} desactivadas`;
    }
    if (rolEl && resumen.total_roles !== undefined) rolEl.textContent = String(resumen.total_roles);
  };

  const abrirModalPermisosUsuario = async (usuario) => {
    modalGestion.abrir({
      titulo: `Permisos asignados · @${usuario.nombre_usuario}`,
      subtitulo: `${usuario.nombre_completo} · Rol ${usuario.rol}`,
      contenidoHtml: '<p class="py-8 text-center text-sm text-zinc-500">Consultando permisos asignados...</p>',
      mostrarConfirmar: false,
    });

    const res = await Solicitud.get(`/api/usuarios/${usuario.usuario_id}/permisos`);
    if (!res.ok || !res.data?.ok) {
      modalGestion.establecerContenido('<p class="py-6 text-center text-sm text-red-600">No se pudieron cargar los permisos del usuario.</p>');
      return;
    }

    const idsActivos = res.data.data?.permiso_ids || [];

    modalGestion.abrir({
      titulo: `Permisos asignados · @${usuario.nombre_usuario}`,
      subtitulo: `${usuario.nombre_completo} · Rol ${usuario.rol} (${idsActivos.length} permisos actuales)`,
      contenidoHtml: renderMatrizPermisosModal(catalogoPermisos, idsActivos),
      mostrarConfirmar: true,
      textoConfirmar: 'Guardar permisos',
      varianteConfirmar: 'marca',
      alConfirmar: async (dlg) => {
        const form = dlg.querySelector('#form-permisos-usuario');
        const seleccionados = Array.from(form?.querySelectorAll('input[name="permiso_ids[]"]:checked') || []).map((c) =>
          Number(c.value)
        );

        const resSave = await Solicitud.put(`/api/usuarios/${usuario.usuario_id}/permisos`, {
          permiso_ids: seleccionados,
        });

        if (!resSave.ok || !resSave.data?.ok) {
          notificarErrorFormulario(form, resSave.message || 'No se pudieron actualizar los permisos.');
          return false;
        }

        notificarExitoFormulario(form, resSave.data.message || 'Permisos actualizados correctamente.');
        await cargarUsuarios();
        return true;
      },
    });

    modalGestion.dialogo?.querySelector('#form-permisos-usuario')?.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-marcar-todos]');
      if (!btn) return;
      const marcar = btn.dataset.marcarTodos === '1';
      modalGestion.dialogo
        ?.querySelectorAll('#form-permisos-usuario input[name="permiso_ids[]"]')
        .forEach((chk) => (chk.checked = marcar));
    });
  };

  const abrirModalEditarUsuario = (usuario) => {
    modalGestion.abrir({
      titulo: `Editar cuenta · @${usuario.nombre_usuario}`,
      subtitulo: usuario.nombre_completo || '',
      contenidoHtml: renderFormularioEditarUsuario(usuario, rolesMapa),
      mostrarConfirmar: true,
      textoConfirmar: 'Actualizar cuenta',
      varianteConfirmar: 'marca',
      alConfirmar: async (dlg) => {
        const form = dlg.querySelector('#form-editar-usuario');
        const nombre = form?.elements?.nombre_usuario?.value?.trim() || '';
        const rolId = Number(form?.elements?.rol_id?.value || 0);
        const clave = form?.elements?.contrasena?.value || '';
        const pin = form?.elements?.pin?.value?.trim() || '';
        const heredar = Boolean(form?.elements?.heredar_permisos_rol?.checked);

        if (!nombre || !rolId) {
          notificarErrorFormulario(form, 'El nombre de usuario y el rol son obligatorios.');
          return false;
        }

        const res = await Solicitud.put(`/api/usuarios/${usuario.usuario_id}`, {
          nombre_usuario: nombre,
          rol_id: rolId,
          contrasena: clave || null,
          pin: pin || null,
          heredar_permisos_rol: heredar,
        });

        if (!res.ok || !res.data?.ok) {
          notificarErrorFormulario(form, res.message || 'No se pudo actualizar el usuario.');
          return false;
        }

        notificarExitoFormulario(form, res.data.message || 'Usuario actualizado correctamente.');
        await cargarUsuarios();
        return true;
      },
    });
  };

  const confirmarCambioEstatus = (usuario, activar) => {
    modalEstatus.abrir({
      titulo: activar ? 'Activar cuenta de usuario' : 'Desactivar cuenta de usuario',
      subtitulo: `@${usuario.nombre_usuario} · ${usuario.nombre_completo}`,
      contenidoHtml: crearAlertaConfirmacion({
        variante: activar ? 'green' : 'red',
        icono: activar ? 'user-check' : 'user-x',
        mensajeHtml: activar
          ? `¿Confirmas reactivar la cuenta <strong>@${usuario.nombre_usuario}</strong>? El trabajador volverá a tener acceso al sistema.`
          : `¿Deseas desactivar la cuenta <strong>@${usuario.nombre_usuario}</strong>? Su estatus cambiará a Desactivado y no podrá iniciar sesión.`,
      }),
      textoConfirmar: activar ? 'Activar usuario' : 'Desactivar usuario',
      varianteConfirmar: activar ? 'green' : 'red',
      alConfirmar: async () => {
        const res = await Solicitud.post(`/api/usuarios/${usuario.usuario_id}/estatus`, {
          estatus: activar ? 1 : 0,
        });

        if (!res.ok || !res.data?.ok) {
          mostrarToast('error', res.message || 'No se pudo actualizar el estatus del usuario.');
          return false;
        }

        mostrarToast('success', res.data.message || 'Estatus actualizado correctamente.');
        await cargarUsuarios();
        return true;
      },
    });
  };

  const tabla = new GestorDataTable('#tabla-usuarios', {
    datos: [],
    ordenInicial: [[0, 'asc']],
    columnas: columnasTablaUsuarios(),
    alAccionar: (accion, registro) => {
      if (accion === 'permisos') return abrirModalPermisosUsuario(registro);
      if (accion === 'editar') return abrirModalEditarUsuario(registro);
      if (accion === 'activar') return confirmarCambioEstatus(registro, true);
      if (accion === 'desactivar') return confirmarCambioEstatus(registro, false);
    },
  });

  const cargarUsuarios = async () => {
    const res = await Solicitud.get('/api/usuarios');
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudieron consultar los usuarios.');
      return;
    }

    const lista = Array.isArray(res.data.data) ? res.data.data : [];
    actualizarResumenCards(res.data.resumen);
    tabla.actualizarDatos(lista);
    if (filtroEstatus?.value && filtroEstatus.value !== 'todos') {
      tabla.filtrarColumna(4, filtroEstatus.value);
    }
  };

  filtroEstatus?.addEventListener('change', (e) => {
    tabla.filtrarColumna(4, e.target.value);
  });

  let enviando = false;

  formulario?.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    if (enviando) return;

    const empleadoId = Number(document.getElementById('empleado_id')?.value || 0);
    const nombreUsuario = document.getElementById('nombre_usuario')?.value?.trim() || '';
    const rolId = Number(document.getElementById('rol_id')?.value || 0);
    const contrasena = document.getElementById('contrasena')?.value || '';
    const pin = document.getElementById('pin')?.value?.trim() || '';

    if (!empleadoId || !nombreUsuario || !rolId || !contrasena) {
      notificarErrorFormulario(formulario, 'Completa el empleado titular, nombre de usuario, rol y contraseña.');
      return;
    }

    if (contrasena.length < 6) {
      notificarErrorFormulario(formulario, 'La contraseña debe contener al menos 6 caracteres.');
      return;
    }

    enviando = true;
    const btnSubmit = formulario.querySelector('button[type="submit"]');
    if (btnSubmit) btnSubmit.disabled = true;

    try {
      const res = await Solicitud.post('/api/usuarios', {
        empleado_id: empleadoId,
        nombre_usuario: nombreUsuario,
        rol_id: rolId,
        contrasena,
        pin: pin || null,
      });

      if (!res.ok || !res.data?.ok) {
        notificarErrorFormulario(formulario, res.message || 'No se pudo registrar el usuario.');
        return;
      }

      notificarExitoFormulario(formulario, res.data.message || 'Usuario registrado correctamente.');
      formulario.reset();
      limpiarAlertaFormulario(formulario);
      await cargarUsuarios();
    } finally {
      enviando = false;
      if (btnSubmit) btnSubmit.disabled = false;
    }
  });

  cargarUsuarios();
});
