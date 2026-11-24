import { Solicitud } from "/js/class/Solicitud.js";
import { InputValidador } from "/js/class/validator/InputValidador.js";
import {
  notificarErrorFormulario,
  notificarExitoFormulario,
} from "/js/components/alerta/alerta.js";
import { crearBadge } from "/js/components/badge/crearBadge.js";
// import { //inicializarCalendariosContenedor } from '/js/components/calendario/calendario.js';
import { abrirModalFamiliaresEmpleado } from "/js/components/modal/abrirModalFamiliaresEmpleado.js";
import { Modal } from "/js/components/modal/Modal.js";
import {
  actualizarOpcionesSelect,
  inicializarSelectsPersonalizados,
} from "/js/components/select/selectPersonalizado.js";
import { mostrarToast } from "/js/components/toast/toast.js";

const actualizarSubtituloEdicion = () => {
  const codigo = document.getElementById("codigo_empleado")?.value || "";
  const primerNombre = document.getElementById("primer_nombre")?.value || "";
  const primerApellido =
    document.getElementById("primer_apellido")?.value || "";
  const subtitulo = document.getElementById("subtitulo-editar-empleado");

  if (subtitulo && (primerNombre || codigo)) {
    subtitulo.textContent =
      `Editando ficha de ${primerNombre} ${primerApellido} (${codigo}).`.trim();
  }
};

const crearFilaTelefonoHtml = (numero = "", esEmergencia = false) => `
  <div data-fila-telefono class="rounded-2xl border ${esEmergencia ? "border-marca-200 bg-marca-50/40" : "border-slate-200 bg-slate-50/70"} p-3.5 space-y-2.5 transition-colors">
    <div class="flex items-center gap-2">
      <div class="relative flex-1">
        <i data-lucide="phone" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
        <input type="text"
          name="telefonos_numero[]"
          value="${numero}"
          maxlength="20"
          placeholder="Ej: 0412-1234567"
          class="input-telefono-empleado w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm text-zinc-900 outline-none transition focus:border-black focus:ring-2 focus:ring-black focus:ring-offset-1" />
      </div>
      <button type="button"
        data-eliminar-telefono
        title="Eliminar teléfono"
        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-700 transition-colors hover:bg-red-100 cursor-pointer">
        <i data-lucide="trash-2" class="h-4 w-4" aria-hidden="true"></i>
      </button>
    </div>

    <label class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2 cursor-pointer select-none">
      <span class="text-xs font-medium text-zinc-700">Designar como único contacto de emergencia</span>
      <input type="hidden" name="telefonos_emergencia[]" value="${esEmergencia ? "1" : "0"}" data-hidden-emergencia />
      <input type="checkbox"
        data-check-emergencia
        ${esEmergencia ? "checked" : ""}
        class="h-4 w-4 rounded border-slate-300 text-marca accent-marca focus:ring-2 focus:ring-marca-500 cursor-pointer" />
    </label>
  </div>
`;

const renderTarjetaRecaudoEmpleado = (rec) => {
  const cargado = Boolean(rec.cargado);
  const obligatorio = Boolean(rec.obligatorio);
  const bordeClase = cargado
    ? "border-green-200 bg-green-50/40"
    : obligatorio
      ? "border-yellow-300 bg-yellow-50/40"
      : "border-slate-200 bg-slate-50/40";

  const badgeHtml = cargado
    ? crearBadge("Vigente", "green")
    : crearBadge(
        obligatorio ? "Obligatorio" : "Opcional",
        obligatorio ? "yellow" : "neutro",
      );

  const enlaceActual =
    cargado && rec.url_descarga
      ? `
        <div class="flex items-center justify-between gap-2 pt-1 text-xs">
          <span class="truncate text-zinc-700 font-medium" title="${rec.nombre_original || ""}">${rec.nombre_original || "Archivo vigente"}</span>
          <a href="${rec.url_descarga}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 font-semibold text-marca hover:underline whitespace-nowrap">
            <i data-lucide="external-link" class="h-3.5 w-3.5" aria-hidden="true"></i>
            <span>Ver</span>
          </a>
        </div>`
      : "";

  const tipoId = rec.tipo_documento_id;

  return `
    <article data-recaudo-card data-tipo-documento-id="${tipoId}" class="rounded-2xl border ${bordeClase} p-4 flex flex-col justify-between gap-3 transition-colors">
      <div class="space-y-1.5">
        <div class="flex items-start justify-between gap-2">
          <h3 class="text-sm font-semibold text-zinc-900">${rec.etiqueta}</h3>
          <div class="flex items-center gap-1.5 shrink-0">${badgeHtml}</div>
        </div>
        ${rec.motivo ? `<p class="text-xs text-zinc-500">${rec.motivo}</p>` : ""}
        ${enlaceActual}
      </div>

      <div class="space-y-2">
        <div>
          <label for="doc-emp-${tipoId}" class="block text-[11px] font-medium text-zinc-600 mb-1">
            ${cargado ? "Actualizar archivo (pasa anterior a No vigente)" : "Adjuntar archivo (PDF/JPG/PNG)"}
          </label>
          <input id="doc-emp-${tipoId}"
            name="documentos[${tipoId}]"
            data-tipo-documento="${tipoId}"
            type="file"
            accept=".pdf,.jpg,.jpeg,.png,.webp"
            class="atlas-doc-input w-full rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-zinc-700 file:mr-2.5 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-white hover:file:bg-zinc-800 cursor-pointer" />
        </div>

        <button type="button"
          data-subir-documento-directo="${tipoId}"
          class="hidden w-full items-center justify-center gap-1.5 rounded-lg bg-marca px-3 py-1.5 text-xs font-semibold text-white transition-all hover:bg-marca-700 cursor-pointer">
          <i data-lucide="upload-cloud" class="h-3.5 w-3.5" aria-hidden="true"></i>
          <span>${cargado ? "Actualizar vigencia ahora" : "Cargar documento ahora"}</span>
        </button>
      </div>
    </article>
  `;
};

const actualizarBadgesContadores = (resumen) => {
  if (!resumen) return;

  const badgeOblig = document.getElementById("badge-contador-obligatorios");
  const textoOblig = document.getElementById("texto-contador-obligatorios");
  const textoTotal = document.getElementById("texto-contador-totales");
  const completos = Boolean(resumen.completos);

  if (textoOblig) {
    textoOblig.textContent = `Obligatorios vigentes: ${resumen.obligatorios_cargados} / ${resumen.obligatorios_total}`;
  }
  if (textoTotal) {
    textoTotal.textContent = `Cargados totales: ${resumen.cargados_total} / ${resumen.total}`;
  }
  if (badgeOblig) {
    badgeOblig.classList.toggle("bg-green-400/20", completos);
    badgeOblig.classList.toggle("text-green-800", completos);
    badgeOblig.classList.toggle("bg-yellow-400/20", !completos);
    badgeOblig.classList.toggle("text-yellow-900", !completos);
  }
};

const normalizarEntradaTelefono = (input) => {
  input.value = input.value.replace(/[^0-9+\-\s]/g, "").slice(0, 20);
};

const configurarValidaciones = () => {
  InputValidador.soloLetras(
    "#primer_nombre, #segundo_nombre, #primer_apellido, #segundo_apellido",
    {
      minimo: 2,
      limite: 100,
      requerido: true,
    },
  );
  InputValidador.soloNumeros("#cedula", {
    minimo: 6,
    limite: 9,
    requerido: true,
  });
  InputValidador.correoElectronico("#correo");
  InputValidador.requerido("#formulario-editar-empleado select");
  InputValidador.archivo(".atlas-doc-input", { tamanoMaximoMB: 5 });
};

document.addEventListener("DOMContentLoaded", () => {
  inicializarSelectsPersonalizados();
  // //inicializarCalendariosContenedor();
  actualizarSubtituloEdicion();
  configurarValidaciones();

  const formulario = document.getElementById("formulario-editar-empleado");
  const empleadoId = formulario?.dataset?.empleadoId;
  const btnVerFamiliares = document.getElementById(
    "btn-ver-familiares-empleado",
  );
  const nodoModalFamiliares = document.getElementById(
    "modal-familiares-empleado",
  );
  const modalFamiliares = nodoModalFamiliares
    ? new Modal(nodoModalFamiliares)
    : null;

  const obtenerDatosEmpleadoHeader = () => ({
    empleado_id: empleadoId,
    codigo_empleado:
      document.getElementById("codigo_empleado")?.value ||
      btnVerFamiliares?.dataset?.codigoEmpleado ||
      "",
    cedula: btnVerFamiliares?.dataset?.cedulaEmpleado || "",
    nombre_completo:
      [
        document.getElementById("primer_nombre")?.value,
        document.getElementById("segundo_nombre")?.value,
        document.getElementById("primer_apellido")?.value,
        document.getElementById("segundo_apellido")?.value,
      ]
        .filter(Boolean)
        .join(" ") ||
      btnVerFamiliares?.dataset?.nombreEmpleado ||
      "Trabajador",
  });

  btnVerFamiliares?.addEventListener("click", () => {
    if (modalFamiliares && empleadoId) {
      abrirModalFamiliaresEmpleado(
        modalFamiliares,
        obtenerDatosEmpleadoHeader(),
      );
    }
  });

  nodoModalFamiliares?.addEventListener("click", (evento) => {
    if (
      evento.target.closest("[data-reintentar-familiares]") &&
      modalFamiliares &&
      empleadoId
    ) {
      abrirModalFamiliaresEmpleado(
        modalFamiliares,
        obtenerDatosEmpleadoHeader(),
      );
    }
  });
  const selectEstado = document.getElementById("estado");
  const selectMunicipio = document.getElementById("municipio");
  const selectEstadoCivil = document.getElementById("estado_civil_id");
  const selectNivelAcademico = document.getElementById("nivel_academico_id");
  const selectCargo = document.getElementById("cargo_id");
  const listaTelefonos = document.getElementById("lista-telefonos-empleado");
  const btnAgregarTelefono = document.getElementById("btn-agregar-telefono");
  const contenedorRecaudos = document.getElementById(
    "contenedor-recaudos-empleado",
  );

  // --- Ubicación geográfica activa ---
  selectEstado?.addEventListener("change", async () => {
    const estadoId = selectEstado.value;
    actualizarOpcionesSelect("#municipio", {});
    actualizarOpcionesSelect("#parroquia_id", {});
    if (!estadoId) return;

    const res = await Solicitud.get(`/api/ubicacion/municipios/${estadoId}`);
    if (res.ok && res.data?.ok) {
      actualizarOpcionesSelect("#municipio", res.data.data || {});
    }
  });

  selectMunicipio?.addEventListener("change", async () => {
    const municipioId = selectMunicipio.value;
    actualizarOpcionesSelect("#parroquia_id", {});
    if (!municipioId) return;

    const res = await Solicitud.get(`/api/ubicacion/parroquias/${municipioId}`);
    if (res.ok && res.data?.ok) {
      actualizarOpcionesSelect("#parroquia_id", res.data.data || {});
    }
  });

  // --- Gestión de múltiples teléfonos con máximo 1 de emergencia ---
  const sincronizarEstilosEmergencia = (checkActivo = null) => {
    listaTelefonos?.querySelectorAll("[data-fila-telefono]").forEach((fila) => {
      const check = fila.querySelector("[data-check-emergencia]");
      const hidden = fila.querySelector("[data-hidden-emergencia]");
      if (!check || !hidden) return;

      if (checkActivo && check !== checkActivo && checkActivo.checked) {
        check.checked = false;
      }

      const marcado = Boolean(check.checked);
      hidden.value = marcado ? "1" : "0";
      fila.classList.toggle("border-marca-200", marcado);
      fila.classList.toggle("bg-marca-50/40", marcado);
      fila.classList.toggle("border-slate-200", !marcado);
      fila.classList.toggle("bg-slate-50/70", !marcado);
    });
  };

  btnAgregarTelefono?.addEventListener("click", () => {
    if (!listaTelefonos) return;
    listaTelefonos.insertAdjacentHTML(
      "beforeend",
      crearFilaTelefonoHtml("", false),
    );
    window.lucide?.createIcons();
  });

  listaTelefonos?.addEventListener("input", (evento) => {
    const inputTel = evento.target.closest(".input-telefono-empleado");
    if (inputTel) normalizarEntradaTelefono(inputTel);
  });

  listaTelefonos?.addEventListener("change", (evento) => {
    const checkEmg = evento.target.closest("[data-check-emergencia]");
    if (checkEmg) sincronizarEstilosEmergencia(checkEmg);
  });

  listaTelefonos?.addEventListener("click", (evento) => {
    const btnEliminar = evento.target.closest("[data-eliminar-telefono]");
    if (!btnEliminar || !listaTelefonos) return;

    const filas = listaTelefonos.querySelectorAll("[data-fila-telefono]");
    const fila = btnEliminar.closest("[data-fila-telefono]");
    if (!fila) return;

    if (filas.length <= 1) {
      const input = fila.querySelector(".input-telefono-empleado");
      const check = fila.querySelector("[data-check-emergencia]");
      if (input) input.value = "";
      if (check) check.checked = false;
      sincronizarEstilosEmergencia();
      return;
    }

    fila.remove();
  });

  // --- Re-evaluación dinámica de recaudos y subida con regla de vigencia ---
  const refrescarRecaudosEmpleado = async () => {
    if (!empleadoId || !contenedorRecaudos) return;

    const res = await Solicitud.get(`/api/empleados/${empleadoId}/recaudos`, {
      params: {
        estado_civil_id: selectEstadoCivil?.value || "",
        nivel_academico_id: selectNivelAcademico?.value || "",
        cargo_id: selectCargo?.value || "",
      },
      claveCancelacion: "recaudos-editar-empleado",
    });

    if (res.aborted || !res.ok || !res.data?.ok) return;

    const lista = Array.isArray(res.data.data?.recaudos)
      ? res.data.data.recaudos
      : [];
    const resumen = res.data.data?.resumen;

    contenedorRecaudos.innerHTML = lista
      .map(renderTarjetaRecaudoEmpleado)
      .join("");
    actualizarBadgesContadores(resumen);
    InputValidador.archivo(".atlas-doc-input", { tamanoMaximoMB: 5 });
    window.lucide?.createIcons();
  };

  selectEstadoCivil?.addEventListener("change", refrescarRecaudosEmpleado);
  selectNivelAcademico?.addEventListener("change", refrescarRecaudosEmpleado);
  selectCargo?.addEventListener("change", refrescarRecaudosEmpleado);

  contenedorRecaudos?.addEventListener("change", (evento) => {
    const inputDoc = evento.target.closest(".atlas-doc-input");
    if (!inputDoc) return;
    const card = inputDoc.closest("[data-recaudo-card]");
    const btnDirecto = card?.querySelector("[data-subir-documento-directo]");
    if (!btnDirecto) return;
    const tieneArchivo = Boolean(inputDoc.files && inputDoc.files.length > 0);
    btnDirecto.classList.toggle("hidden", !tieneArchivo);
    btnDirecto.classList.toggle("inline-flex", tieneArchivo);
  });

  contenedorRecaudos?.addEventListener("click", async (evento) => {
    const btnDirecto = evento.target.closest("[data-subir-documento-directo]");
    if (!btnDirecto || !empleadoId) return;

    const tipoId = btnDirecto.dataset.subirDocumentoDirecto;
    const card = btnDirecto.closest("[data-recaudo-card]");
    const inputDoc = card?.querySelector(".atlas-doc-input");
    const archivo = inputDoc?.files?.[0];
    if (!archivo || !tipoId) return;

    btnDirecto.disabled = true;
    const formData = new FormData();
    formData.append("empleado_id", empleadoId);
    formData.append("tipo_documento_id", tipoId);
    formData.append("archivo", archivo);

    try {
      const res = await Solicitud.post("/api/documentos", formData);
      if (!res.ok || !res.data?.ok) {
        mostrarToast("error", res.message || "No se pudo cargar el documento.");
        return;
      }

      mostrarToast(
        "success",
        "Documento actualizado como Vigente (el anterior pasó a No vigente).",
      );
      await refrescarRecaudosEmpleado();
    } finally {
      btnDirecto.disabled = false;
    }
  });

  // --- Envío general del formulario ---
  let enviando = false;

  formulario?.addEventListener("submit", async (evento) => {
    evento.preventDefault();
    if (enviando) return;

    if (!empleadoId) {
      notificarErrorFormulario(
        formulario,
        "No se encontró el identificador del trabajador a actualizar.",
      );
      return;
    }

    const marcadosEmergencia =
      listaTelefonos?.querySelectorAll("[data-check-emergencia]:checked")
        ?.length || 0;
    if (marcadosEmergencia > 1) {
      notificarErrorFormulario(
        formulario,
        "Solo puedes marcar un (1) teléfono de emergencia.",
      );
      return;
    }

    const btnSubmit = formulario.querySelector('button[type="submit"]');
    const textoSpan = btnSubmit?.querySelector("span");
    const textoOriginal = textoSpan?.textContent || "Actualizar Empleado";

    enviando = true;
    if (btnSubmit) btnSubmit.disabled = true;
    if (textoSpan) textoSpan.textContent = "Guardando...";

    try {
      const res = await Solicitud.post(
        `/api/empleados/${empleadoId}`,
        new FormData(formulario),
      );
      if (!res.ok || !res.data?.ok) {
        notificarErrorFormulario(
          formulario,
          res.message || "No se pudo actualizar el trabajador.",
        );
        return;
      }

      notificarExitoFormulario(
        formulario,
        res.data.message || "Datos del trabajador actualizados correctamente.",
      );
      setTimeout(() => {
        window.location.href = "/empleado/registros";
      }, 650);
    } finally {
      enviando = false;
      if (btnSubmit) btnSubmit.disabled = false;
      if (textoSpan) textoSpan.textContent = textoOriginal;
    }
  });
});
