import { GestorDataTable } from '/js/class/GestorDataTable.js';
import { Solicitud } from '/js/class/Solicitud.js';
import {
  limpiarAlertaFormulario,
  notificarErrorFormulario,
  notificarExitoFormulario,
} from '/js/components/alerta/alerta.js';
import { Modal } from '/js/components/modal/Modal.js';
import { mostrarToast } from '/js/components/toast/toast.js';
import { columnasTablaRoles } from '/js/templates/tabla_roles.js';

const renderFormularioModalRol = (rolEditar = null) => `
  <form id="form-modal-rol" class="space-y-4" novalidate>
    <div>
      <label for="input-nombre-rol" class="block text-sm font-medium text-zinc-900 mb-1.5">Nombre del rol *</label>
      <input
        id="input-nombre-rol"
        name="rol"
        type="text"
        maxlength="50"
        value="${rolEditar?.rol || ''}"
        placeholder="Ej: Coordinador Regional"
        required
        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-black focus:bg-white"
      />
    </div>
  </form>
`;

document.addEventListener('DOMContentLoaded', () => {
  const modalRol = new Modal('#modal-formulario-rol');
  const formularioMatriz = document.getElementById('formulario-permisos-rol');
  const tituloMatriz = document.getElementById('titulo-matriz-rol');
  const subtituloMatriz = document.getElementById('subtitulo-matriz-rol');
  const btnNuevoRol = document.getElementById('btn-nuevo-rol');
  const btnMarcarTodos = document.getElementById('btn-rol-marcar-todos');
  const btnDesmarcarTodos = document.getElementById('btn-rol-desmarcar-todos');
  const checkSincronizar = document.getElementById('sincronizar_usuarios');

  let listaRoles = [];
  let rolSeleccionado = null;
  let guardandoMatriz = false;

  const aplicarSeleccionRol = (rolObj) => {
    rolSeleccionado = rolObj;
    limpiarAlertaFormulario(formularioMatriz);

    if (!rolObj) return;

    if (tituloMatriz) tituloMatriz.textContent = `Permisos del Rol: ${rolObj.rol}`;
    if (subtituloMatriz) {
      subtituloMatriz.textContent = `${rolObj.permisos_count ?? 0} permisos asignados · ${rolObj.usuarios_count ?? 0} usuarios con este rol.`;
    }

    const activos = new Set((rolObj.permiso_ids || []).map(Number));
    document.querySelectorAll('.check-permiso-rol').forEach((chk) => {
      chk.checked = activos.has(Number(chk.value));
    });
  };

  const abrirModalRol = (rolEditar = null) => {
    modalRol.abrir({
      titulo: rolEditar ? `Editar rol · ${rolEditar.rol}` : 'Crear nuevo rol',
      subtitulo: 'Se guarda directamente en la tabla public.rol.',
      contenidoHtml: renderFormularioModalRol(rolEditar),
      mostrarConfirmar: true,
      textoConfirmar: rolEditar ? 'Actualizar rol' : 'Crear rol',
      varianteConfirmar: 'marca',
      alConfirmar: async (dlg) => {
        const form = dlg.querySelector('#form-modal-rol');
        const nombre = form?.elements?.rol?.value?.trim() || '';

        if (!nombre) {
          notificarErrorFormulario(form, 'Ingresa el nombre del rol.');
          return false;
        }

        const res = rolEditar
          ? await Solicitud.put(`/api/roles/${rolEditar.rol_id}`, { rol: nombre })
          : await Solicitud.post('/api/roles', { rol: nombre });

        if (!res.ok || !res.data?.ok) {
          notificarErrorFormulario(form, res.message || 'No se pudo guardar el rol.');
          return false;
        }

        notificarExitoFormulario(form, res.data.message || 'Rol guardado correctamente.');
        await cargarRoles(res.data.data?.rol_id || rolEditar?.rol_id);
        return true;
      },
    });
  };

  const tabla = new GestorDataTable('#tabla-roles', {
    datos: [],
    ordenInicial: [[0, 'asc']],
    columnas: columnasTablaRoles(),
    alAccionar: (accion, registro) => {
      if (accion === 'configurar-permisos') return aplicarSeleccionRol(registro);
      if (accion === 'editar') return abrirModalRol(registro);
    },
  });

  const cargarRoles = async (idPreferido = null) => {
    const res = await Solicitud.get('/api/roles');
    if (!res.ok || !res.data?.ok) {
      mostrarToast('error', res.message || 'No se pudieron cargar los roles.');
      return;
    }

    listaRoles = Array.isArray(res.data.data) ? res.data.data : [];
    const totalEl = document.getElementById('resumen-total-roles');
    if (totalEl && res.data.resumen?.total_roles !== undefined) {
      totalEl.textContent = String(res.data.resumen.total_roles);
    }

    tabla.actualizarDatos(listaRoles);

    const idObjetivo = idPreferido || rolSeleccionado?.rol_id || listaRoles[0]?.rol_id;
    const encontrado = listaRoles.find((r) => Number(r.rol_id) === Number(idObjetivo)) || listaRoles[0] || null;
    if (encontrado) aplicarSeleccionRol(encontrado);
  };

  btnNuevoRol?.addEventListener('click', () => abrirModalRol(null));

  btnMarcarTodos?.addEventListener('click', () => {
    document.querySelectorAll('.check-permiso-rol').forEach((chk) => (chk.checked = true));
  });

  btnDesmarcarTodos?.addEventListener('click', () => {
    document.querySelectorAll('.check-permiso-rol').forEach((chk) => (chk.checked = false));
  });

  formularioMatriz?.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    if (guardandoMatriz) return;

    if (!rolSeleccionado?.rol_id) {
      notificarErrorFormulario(formularioMatriz, 'Selecciona primero un rol de la tabla para asignar sus permisos.');
      return;
    }

    const permisoIds = Array.from(document.querySelectorAll('.check-permiso-rol:checked')).map((c) => Number(c.value));
    const sincronizarUsuarios = Boolean(checkSincronizar?.checked);

    guardandoMatriz = true;
    const btnGuardar = document.getElementById('btn-guardar-permisos-rol');
    if (btnGuardar) btnGuardar.disabled = true;

    try {
      const res = await Solicitud.put(`/api/roles/${rolSeleccionado.rol_id}/permisos`, {
        permiso_ids: permisoIds,
        sincronizar_usuarios: sincronizarUsuarios,
      });

      if (!res.ok || !res.data?.ok) {
        notificarErrorFormulario(formularioMatriz, res.message || 'No se pudieron actualizar los permisos del rol.');
        return;
      }

      notificarExitoFormulario(formularioMatriz, res.data.message || 'Permisos del rol actualizados correctamente.');
      await cargarRoles(rolSeleccionado.rol_id);
    } finally {
      guardandoMatriz = false;
      if (btnGuardar) btnGuardar.disabled = false;
    }
  });

  cargarRoles();
});
