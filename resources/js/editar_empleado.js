import { Solicitud } from "/js/class/Solicitud.js";
import { InputUI } from "/js/class/validator/InputUI.js";
import { InputValidador } from "/js/class/validator/InputValidador.js";
import {
  notificarErrorFormulario,
  notificarExitoFormulario,
} from "/js/components/alerta/alerta.js";
import { crearBadge } from "/js/components/badge/crearBadge.js";
import { abrirModalFamiliaresEmpleado } from "/js/components/modal/abrirModalFamiliaresEmpleado.js";
import { Modal } from "/js/components/modal/Modal.js";
import {
  actualizarOpcionesSelect,
  crearMarkupSelectPersonalizado,
  inicializarSelectsPersonalizados,
} from "/js/components/select/selectPersonalizado.js";
import { mostrarToast } from "/js/components/toast/toast.js";
import {
  fechaVisualAIso,
  inicializarCampoFechaDobleFormato,
  inicializarRangoFechasDobleFormato,
  obtenerFechaHoyLocalIso,
} from "/js/utils/formatearFecha.js";

const OPERADORAS_TELEFONO = {
  "0412": "0412",
  "0416": "0416",
  "0424": "0424",
  "0422": "0422",
  "0414": "0414",
  "0243": "0243",
};

const REGEX_SOLO_TEXTO = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/;
const REGEX_TEXTO_NUMEROS = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9\s]+$/;
let contadorFilasTelefono = 100;

// Recibe ninguno; sincroniza subtítulo y tarjeta de encabezado con nombre y código actuales.
const actualizarSubtituloEdicion = () => {
  const codigo = document.getElementById("codigo_empleado")?.value || "";
  const primerNombre = document.getElementById("primer_nombre")?.value || "";
  const primerApellido =
    document.getElementById("primer_apellido")?.value || "";
  const subtitulo = document.getElementById("subtitulo-editar-empleado");
  const codigoHeader = document.getElementById("codigo-empleado-header");

  if (codigoHeader) {
    codigoHeader.textContent = codigo.trim() || "SIN-COD";
  }
  if (subtitulo && (primerNombre || codigo)) {
    subtitulo.textContent =
      `Editando ficha de ${primerNombre} ${primerApellido} (${codigo}).`.trim();
  }
};

// Recibe operadora, dígitos (7) y marca de emergencia; genera el HTML de una fila de teléfono dividida.
const crearFilaTelefonoHtml = (
  operadora = "",
  digitos = "",
  esEmergencia = false,
) => {
  contadorFilasTelefono += 1;
  const idSelect = `tel-operadora-din-${contadorFilasTelefono}`;
  const selectOperadoraHtml = crearMarkupSelectPersonalizado({
    id: idSelect,
    nombre: "telefonos_operadora[]",
    opciones: OPERADORAS_TELEFONO,
    valorSeleccionado: operadora,
    placeholder: "Cód.",
  });
  const completo = operadora && digitos ? `${operadora}-${digitos}` : "";

  return `
  <div data-fila-telefono class="rounded-2xl border ${esEmergencia ? "border-marca-200 bg-marca-50/40" : "border-slate-200 bg-slate-50/70"} p-3.5 space-y-2.5 transition-colors">
    <div class="flex items-start gap-2">
      <div class="grid grid-cols-[6.75rem_1fr] gap-2 flex-1">
        <div>${selectOperadoraHtml}</div>
        <div class="relative">
          <i data-lucide="phone" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
          <input type="text"
            name="telefonos_digitos[]"
            value="${digitos}"
            maxlength="7"
            inputmode="numeric"
            placeholder="1234567"
            class="input-telefono-digitos w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm text-zinc-900 outline-none transition focus:border-black focus:ring-2 focus:ring-black focus:ring-offset-1" />
          <input type="hidden" name="telefonos_numero[]" value="${completo}" data-hidden-telefono-completo />
        </div>
      </div>
      <button type="button"
        data-eliminar-telefono
        title="Eliminar teléfono"
        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-700 transition-colors hover:bg-red-100 cursor-pointer">
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
};

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

// Recibe un input de dígitos telefónicos; restringe la entrada a máximo 7 dígitos numéricos.
const normalizarDigitosTelefono = (input) => {
  input.value = input.value.replace(/[^0-9]/g, "").slice(0, 7);
};

// Recibe una fila de teléfono; concatena operadora y dígitos con guion en el input oculto.
const sincronizarTelefonoFila = (fila) => {
  if (!fila) return;
  const selectOp = fila.querySelector('select[name="telefonos_operadora[]"]');
  const inputDig = fila.querySelector(".input-telefono-digitos");
  const hiddenCompleto = fila.querySelector("[data-hidden-telefono-completo]");
  if (!hiddenCompleto) return;

  const op = String(selectOp?.value || "").trim();
  const dig = String(inputDig?.value || "").trim();
  hiddenCompleto.value = op && dig ? `${op}-${dig}` : "";
};

// Recibe ninguno; configura las validaciones en vivo de datos personales, dirección y archivos.
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
  InputValidador.soloLetras("#nombre_urb", {
    minimo: 2,
    limite: 100,
    permitirNumeros: false,
    mensajeError: "Solo se permite texto en urbanización / sector",
  });
  InputValidador.soloLetras("#descripcion", {
    limite: 255,
    permitirNumeros: false,
    mensajeError: "Solo se permite texto en el punto de referencia",
  });
  InputValidador.soloLetras("#calle", {
    minimo: 2,
    limite: 30,
    permitirNumeros: true,
    requerido: true,
    mensajeError: "Solo se permiten letras y números en calle / avenida",
  });
  InputValidador.soloLetras("#piso_edificio", {
    minimo: 1,
    limite: 10,
    permitirNumeros: true,
    mensajeError: "Solo se permiten letras y números en piso / edificio",
  });
  InputValidador.soloLetras("#numero_vivienda", {
    minimo: 1,
    limite: 10,
    permitirNumeros: true,
    requerido: false,
    mensajeError: "Solo se permiten letras y números en número de vivienda",
  });
  InputValidador.correoElectronico("#correo");
  InputValidador.requerido("#formulario-editar-empleado select");
  InputValidador.archivo(".atlas-doc-input", { tamanoMaximoMB: 5 });
};

// Recibe el formulario de edición; valida dirección, teléfonos divididos y card de discapacidad antes de enviar.
const validarFormularioAntesDeEnviar = (formulario, listaTelefonos) => {
  const inputCodigo = document.getElementById("codigo_empleado");
  if (!String(inputCodigo?.value || "").trim()) {
    InputUI.establecerInvalido(inputCodigo, "El código de empleado es obligatorio");
    notificarErrorFormulario(formulario, "El código de empleado es obligatorio.");
    return false;
  }

  const selectVivienda = document.getElementById("vivienda");
  const tipoVivienda = String(selectVivienda?.value || "").trim();
  if (tipoVivienda !== "Casa" && tipoVivienda !== "Departamento") {
    InputUI.establecerInvalido(selectVivienda, "Seleccione Casa o Departamento");
    notificarErrorFormulario(formulario, "Selecciona un tipo de vivienda válido (Casa o Departamento).");
    return false;
  }

  const inputCalle = document.getElementById("calle");
  const calleVal = String(inputCalle?.value || "").trim();
  if (!calleVal || !REGEX_TEXTO_NUMEROS.test(calleVal)) {
    InputUI.establecerInvalido(inputCalle, "Solo texto y números");
    notificarErrorFormulario(formulario, "Calle / Avenida es obligatoria y solo permite texto y números.");
    return false;
  }

  const inputNumViv = document.getElementById("numero_vivienda");
  const numVivVal = String(inputNumViv?.value || "").trim();
  if (!numVivVal || !REGEX_TEXTO_NUMEROS.test(numVivVal)) {
    InputUI.establecerInvalido(inputNumViv, "Solo texto y números");
    notificarErrorFormulario(formulario, "Número de vivienda es obligatorio y solo permite texto y números.");
    return false;
  }

  if (tipoVivienda === "Departamento") {
    const inputUrb = document.getElementById("nombre_urb");
    const urbVal = String(inputUrb?.value || "").trim();
    if (!urbVal || !REGEX_SOLO_TEXTO.test(urbVal)) {
      InputUI.establecerInvalido(inputUrb, "Solo texto");
      notificarErrorFormulario(formulario, "Nombre de la urbanización es obligatorio para Departamento y solo permite texto.");
      return false;
    }

    const inputPiso = document.getElementById("piso_edificio");
    const pisoVal = String(inputPiso?.value || "").trim();
    if (!pisoVal || !REGEX_TEXTO_NUMEROS.test(pisoVal)) {
      InputUI.establecerInvalido(inputPiso, "Solo texto y números");
      notificarErrorFormulario(formulario, "Piso / Edificio es obligatorio para Departamento y solo permite texto y números.");
      return false;
    }
  }

  const inputDesc = document.getElementById("descripcion");
  const descVal = String(inputDesc?.value || "").trim();
  if (descVal && !REGEX_TEXTO_NUMEROS.test(descVal)) {
    InputUI.establecerInvalido(inputDesc, "Solo texto");
    notificarErrorFormulario(formulario, "El Punto de referencia / Descripción solo permite texto.");
    return false;
  }

  let errorTelefono = "";
  listaTelefonos?.querySelectorAll("[data-fila-telefono]").forEach((fila) => {
    sincronizarTelefonoFila(fila);
    const selectOp = fila.querySelector('select[name="telefonos_operadora[]"]');
    const inputDig = fila.querySelector(".input-telefono-digitos");
    const op = String(selectOp?.value || "").trim();
    const dig = String(inputDig?.value || "").trim();
    if (!op && !dig) return;
    if (!OPERADORAS_TELEFONO[op]) {
      errorTelefono = "Selecciona una operadora válida (0412, 0416, 0424, 0422, 0414 o 0243).";
      if (selectOp) InputUI.establecerInvalido(selectOp, "Operadora requerida");
      return;
    }
    if (!/^\d{7}$/.test(dig)) {
      errorTelefono = "Cada teléfono debe tener exactamente 7 dígitos numéricos.";
      if (inputDig) InputUI.establecerInvalido(inputDig, "Debe tener 7 dígitos");
    }
  });
  if (errorTelefono) {
    notificarErrorFormulario(formulario, errorTelefono);
    return false;
  }

  if (document.getElementById("discapacidad_activa")?.value === "1") {
    const grado = String(document.getElementById("grado_discapacidad_id")?.value || "").trim();
    const carnet = String(document.getElementById("numero_carnet")?.value || "").trim();
    const entidad = String(document.getElementById("entidad_emisora")?.value || "").trim();
    const emision = fechaVisualAIso(
      document.getElementById("fecha_emision_carnet_iso")?.value ||
        document.getElementById("fecha_emision_carnet")?.value ||
        "",
    );
    const vencimiento = fechaVisualAIso(
      document.getElementById("fecha_vencimiento_carnet_iso")?.value ||
        document.getElementById("fecha_vencimiento_carnet")?.value ||
        "",
    );

    if (!grado || !carnet || !entidad) {
      notificarErrorFormulario(
        formulario,
        "Completa el grado de discapacidad, número de carnet CONAPDIS y entidad emisora.",
      );
      return false;
    }
    if (emision && vencimiento && vencimiento < emision) {
      notificarErrorFormulario(
        formulario,
        "La fecha de vencimiento del carnet no puede ser menor que la fecha de emisión.",
      );
      return false;
    }
  }

  return true;
};

document.addEventListener("DOMContentLoaded", () => {
  inicializarSelectsPersonalizados();
  inicializarCampoFechaDobleFormato("#fecha_nacimiento", {
    maxDate: obtenerFechaHoyLocalIso(),
  });
  inicializarRangoFechasDobleFormato(
    "#fecha_emision_carnet",
    "#fecha_vencimiento_carnet",
  );
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
  const selectVivienda = document.getElementById("vivienda");
  const inputCodigoEmpleado = document.getElementById("codigo_empleado");
  const selectEstadoCivil = document.getElementById("estado_civil_id");
  const selectNivelAcademico = document.getElementById("nivel_academico_id");
  const selectCargo = document.getElementById("cargo_id");
  const listaTelefonos = document.getElementById("lista-telefonos-empleado");
  const btnAgregarTelefono = document.getElementById("btn-agregar-telefono");
  const contenedorRecaudos = document.getElementById(
    "contenedor-recaudos-empleado",
  );

  inputCodigoEmpleado?.addEventListener("input", actualizarSubtituloEdicion);

  // Recibe ninguno; muestra u oculta urbanización y piso/edificio según tipo de vivienda.
  const sincronizarCamposVivienda = () => {
    const esDepto = String(selectVivienda?.value || "").trim() === "Departamento";
    const camposDepto = document.querySelectorAll("[data-campo-departamento]");
    camposDepto.forEach((bloque) => {
      bloque.classList.toggle("hidden", !esDepto);
      const input = bloque.querySelector("input");
      if (!input) return;
      input.required = esDepto;
      if (!esDepto) {
        InputUI.establecerNeutral(input);
      }
    });
  };

  selectVivienda?.addEventListener("change", sincronizarCamposVivienda);
  sincronizarCamposVivienda();

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
      crearFilaTelefonoHtml("", "", false),
    );
    const nuevaFila = listaTelefonos.lastElementChild;
    if (nuevaFila) inicializarSelectsPersonalizados(nuevaFila);
    window.lucide?.createIcons();
  });

  listaTelefonos?.addEventListener("input", (evento) => {
    const inputDig = evento.target.closest(".input-telefono-digitos");
    if (!inputDig) return;
    normalizarDigitosTelefono(inputDig);
    sincronizarTelefonoFila(inputDig.closest("[data-fila-telefono]"));
  });

  listaTelefonos?.addEventListener("change", (evento) => {
    const checkEmg = evento.target.closest("[data-check-emergencia]");
    if (checkEmg) sincronizarEstilosEmergencia(checkEmg);

    const selectOp = evento.target.closest('select[name="telefonos_operadora[]"]');
    if (selectOp) sincronizarTelefonoFila(selectOp.closest("[data-fila-telefono]"));
  });

  listaTelefonos?.addEventListener("click", (evento) => {
    const btnEliminar = evento.target.closest("[data-eliminar-telefono]");
    if (!btnEliminar || !listaTelefonos) return;

    const filas = listaTelefonos.querySelectorAll("[data-fila-telefono]");
    const fila = btnEliminar.closest("[data-fila-telefono]");
    if (!fila) return;

    if (filas.length <= 1) {
      const selectOp = fila.querySelector('select[name="telefonos_operadora[]"]');
      const inputDig = fila.querySelector(".input-telefono-digitos");
      const check = fila.querySelector("[data-check-emergencia]");
      if (selectOp) actualizarOpcionesSelect(selectOp, OPERADORAS_TELEFONO, "");
      if (inputDig) inputDig.value = "";
      if (check) check.checked = false;
      sincronizarTelefonoFila(fila);
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

    if (!validarFormularioAntesDeEnviar(formulario, listaTelefonos)) {
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
        window.location.reload();
      }, 650);
    } finally {
      enviando = false;
      if (btnSubmit) btnSubmit.disabled = false;
      if (textoSpan) textoSpan.textContent = textoOriginal;
    }
  });
});
