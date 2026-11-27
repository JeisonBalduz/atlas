import { Solicitud } from "/js/class/Solicitud.js";
import { InputUI } from "/js/class/validator/InputUI.js";
import { InputValidador } from "/js/class/validator/InputValidador.js";
import {
  notificarErrorFormulario,
  notificarExitoFormulario,
} from "/js/components/alerta/alerta.js";
import { crearBadge } from "/js/components/badge/crearBadge.js";
import { inicializarSelectsPersonalizados } from "/js/components/select/selectPersonalizado.js";
import {
  inicializarCampoFechaDobleFormato,
  inicializarRangoFechasDobleFormato,
  obtenerFechaHoyLocalIso,
  parsearFechaFlexible,
} from "/js/utils/formatearFecha.js";

// Recibe fecha (dd-mm-yyyy o YYYY-MM-DD); retorna años cumplidos o meses si tiene menos de 1 año.
const calcularEdadTexto = (fechaValor) => {
  const parsed = parsearFechaFlexible(fechaValor);
  if (!parsed) return "";

  const { anio, mes, dia } = parsed;
  const hoy = new Date();
  const anioHoy = hoy.getFullYear();
  const mesHoy = hoy.getMonth() + 1;
  const diaHoy = hoy.getDate();

  const esFutura =
    anio > anioHoy ||
    (anio === anioHoy && (mes > mesHoy || (mes === mesHoy && dia > diaHoy)));
  if (esFutura) return "";

  let anios = anioHoy - anio;
  if (mesHoy < mes || (mesHoy === mes && diaHoy < dia)) {
    anios -= 1;
  }

  if (anios < 0 || anios > 130) return "";
  if (anios >= 1) {
    return `${anios} ${anios === 1 ? "año" : "años"}`;
  }

  let meses = (anioHoy - anio) * 12 + (mesHoy - mes);
  if (diaHoy < dia) {
    meses -= 1;
  }
  meses = Math.max(0, meses);
  return `${meses} ${meses === 1 ? "mes" : "meses"}`;
};
import {
  filtrarRecaudosPorDiscapacidad,
  sincronizarPanelDiscapacidad,
  sincronizarReglaCedulaMenor,
  sincronizarVisibilidadTomoFolio,
  validarBloqueDiscapacidadFamiliar,
} from "/js/utils/reglasFamiliar.js";

// Recibe un objeto de recaudo; construye el HTML de la tarjeta de documento preservando su estado vigente.
const renderTarjetaRecaudoFamiliarEdicion = (rec) => {
  const cargado = Boolean(rec.cargado);
  const obligatorio = Boolean(rec.obligatorio);
  const esTitular = Boolean(rec.es_titular);
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
  const controlHtml = esTitular
    ? '<p class="rounded-lg bg-sky-50 px-3 py-2 text-xs text-sky-800">Se verifica en el expediente del trabajador titular.</p>'
    : `
      <div>
        <label for="doc-fam-edit-${rec.tipo_documento_id}" class="block text-[11px] font-medium text-zinc-600 mb-1">
          ${cargado ? "Actualizar archivo (pasa anterior a No vigente)" : "Adjuntar archivo (PDF/JPG/PNG)"}
        </label>
        <input id="doc-fam-edit-${rec.tipo_documento_id}"
          name="documentos[${rec.tipo_documento_id}]"
          data-tipo-documento="${rec.tipo_documento_id}"
          type="file"
          accept=".pdf,.jpg,.jpeg,.png,.webp"
          class="atlas-doc-fam-input w-full rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-zinc-700 file:mr-2.5 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-white hover:file:bg-zinc-800 cursor-pointer" />
      </div>`;

  return `
    <article data-recaudo-card data-tipo-documento-id="${rec.tipo_documento_id}" class="rounded-2xl border ${bordeClase} p-4 flex flex-col justify-between gap-3 transition-colors">
      <div class="space-y-1.5">
        <div class="flex items-start justify-between gap-2">
          <h3 class="text-sm font-semibold text-zinc-900">${rec.etiqueta}</h3>
          <div class="flex items-center gap-1.5 shrink-0">${badgeHtml}</div>
        </div>
        ${rec.motivo ? `<p class="text-xs text-zinc-500">${rec.motivo}</p>` : ""}
        ${enlaceActual}
      </div>
      ${controlHtml}
    </article>`;
};

// Recibe ninguno; configura las validaciones en vivo del formulario de edición familiar.
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
    requerido: false,
  });
  InputValidador.soloNumeros("#tomo, #folio", {
    minimo: 1,
    limite: 6,
    requerido: false,
  });
  InputValidador.requerido(
    "#formulario-editar-familiar select, #fecha_nacimiento",
  );
  InputValidador.archivo(".atlas-doc-fam-input", { tamanoMaximoMB: 5 });
};

document.addEventListener("DOMContentLoaded", () => {
  inicializarSelectsPersonalizados();
  configurarValidaciones();

  const formulario = document.getElementById("formulario-editar-familiar");
  const familiaId = formulario?.dataset?.familiaId;
  const selectTitular = document.getElementById("empleado_id");
  const selectParentesco = document.getElementById("parentesco_id");
  const inputFechaNac = document.getElementById("fecha_nacimiento");
  const inputFechaNacIso = document.getElementById("fecha_nacimiento_iso");
  const inputEdadVisual = document.getElementById("edad_visual");
  const inputCedula = document.getElementById("cedula");
  const avisoCedulaMenor = document.getElementById("aviso-cedula-menor");
  const inputTomo = document.getElementById("tomo");
  const inputFolio = document.getElementById("folio");
  const bloquesTomoFolio = Array.from(
    document.querySelectorAll("[data-bloque-tomo-folio]"),
  );
  const btnToggleDisc = document.getElementById("btn-toggle-discapacidad-familiar");
  const inputTieneDisc = document.getElementById("tiene_discapacidad");
  const panelDisc = document.getElementById("panel-discapacidad-familiar");
  const contenedorRecaudos = document.getElementById("contenedor-recaudos-editar-familiar");
  const camposRequeridosDisc = [
    document.getElementById("grado_discapacidad_id"),
    document.getElementById("numero_carnet"),
    document.getElementById("entidad_emisora"),
  ];

  let recaudoDiscapacidadBase = null;
  try {
    recaudoDiscapacidadBase = formulario?.dataset?.recaudoDiscapacidad
      ? JSON.parse(formulario.dataset.recaudoDiscapacidad)
      : null;
  } catch {
    recaudoDiscapacidadBase = null;
  }

  // Recibe ninguno; aplica regla de cédula < 9 años y visibilidad de tomo y folio.
  const sincronizarReglasDinamicas = () => {
    sincronizarReglaCedulaMenor({
      inputFecha: inputFechaNacIso || inputFechaNac,
      inputCedula,
      avisoCedula: avisoCedulaMenor,
    });
    sincronizarVisibilidadTomoFolio({
      selectParentesco,
      bloquesTomoFolio,
      inputTomo,
      inputFolio,
    });
  };

  // Recibe fecha opcional; recalcula y muestra la edad visual sin alterar el envío al servidor.
  const actualizarEdadVisual = (fechaOpcional = null) => {
    if (!inputEdadVisual) return;
    const valor =
      fechaOpcional !== null
        ? fechaOpcional
        : inputFechaNac
          ? inputFechaNac.value
          : inputFechaNacIso?.value || "";
    inputEdadVisual.value = calcularEdadTexto(valor);
  };

  inicializarCampoFechaDobleFormato(inputFechaNac, {
    inputIso: inputFechaNacIso,
    maxDate: obtenerFechaHoyLocalIso(),
    alSeleccionar: (fechaIso) => {
      sincronizarReglasDinamicas();
      actualizarEdadVisual(fechaIso);
    },
  });
  inicializarRangoFechasDobleFormato(
    "#fecha_emision_carnet",
    "#fecha_vencimiento_carnet",
  );

  // Recibe ninguno; consulta al servidor los recaudos vigentes y aplica visibilidad condicional del recaudo ID 10.
  const refrescarRecaudosFamiliar = async () => {
    if (!familiaId || !contenedorRecaudos) return;
    const res = await Solicitud.get(`/api/familiares/${familiaId}/recaudos`, {
      params: {
        parentesco_id: selectParentesco?.value || "",
        empleado_id: selectTitular?.value || "",
      },
      claveCancelacion: "recaudos-editar-familiar",
    });

    if (res.aborted || !res.ok || !res.data?.ok) return;
    const base = Array.isArray(res.data.data?.recaudos) ? res.data.data.recaudos : [];
    const discBase = res.data.data?.recaudo_discapacidad || recaudoDiscapacidadBase;
    const filtrados = filtrarRecaudosPorDiscapacidad(
      base,
      inputTieneDisc?.value === "1",
      discBase,
    );

    contenedorRecaudos.innerHTML = filtrados
      .map(renderTarjetaRecaudoFamiliarEdicion)
      .join("");
    InputValidador.archivo(".atlas-doc-fam-input", { tamanoMaximoMB: 5 });
    window.lucide?.createIcons();
  };

  // Recibe el ID del empleado titular; actualiza en vivo la tarjeta de información del responsable.
  const actualizarResumenTitular = async (empleadoId) => {
    if (!empleadoId) return;
    const res = await Solicitud.get(`/api/empleados/${empleadoId}`, {
      claveCancelacion: "resumen-titular-edicion",
    });
    if (res.aborted || !res.ok || !res.data?.ok || !res.data?.data) return;

    const emp = res.data.data;
    const persona = emp.persona || {};
    const cedula =
      persona.nacionalidad && persona.cedula
        ? `${String(persona.nacionalidad).toUpperCase()}-${persona.cedula}`
        : "";
    const cargoDepto = [emp.cargo, emp.departamento].filter(Boolean).join(" · ");

    const nodoNombre = document.getElementById("editar-titular-nombre");
    const nodoCedula = document.getElementById("editar-titular-cedula");
    const nodoCargo = document.getElementById("editar-titular-cargo");
    if (nodoNombre) nodoNombre.textContent = emp.nombre_completo || "";
    if (nodoCedula) nodoCedula.textContent = cedula;
    if (nodoCargo) nodoCargo.textContent = cargoDepto;
  };

  selectTitular?.addEventListener("change", () => {
    actualizarResumenTitular(selectTitular.value);
    refrescarRecaudosFamiliar();
  });

  selectParentesco?.addEventListener("change", () => {
    sincronizarReglasDinamicas();
    refrescarRecaudosFamiliar();
  });

  inputFechaNac?.addEventListener("change", () => {
    sincronizarReglasDinamicas();
    actualizarEdadVisual();
  });
  inputFechaNac?.addEventListener("input", () => {
    sincronizarReglasDinamicas();
    actualizarEdadVisual();
  });
  inputFechaNacIso?.addEventListener("change", () => {
    sincronizarReglasDinamicas();
    actualizarEdadVisual(inputFechaNacIso.value);
  });

  btnToggleDisc?.addEventListener("click", () => {
    const proximo = inputTieneDisc?.value !== "1";
    sincronizarPanelDiscapacidad({
      activo: proximo,
      inputHiddenActivo: inputTieneDisc,
      panelDiscapacidad: panelDisc,
      botonToggle: btnToggleDisc,
      camposRequeridos: camposRequeridosDisc,
    });
    refrescarRecaudosFamiliar();
  });

  sincronizarReglasDinamicas();

  let enviando = false;

  formulario?.addEventListener("submit", async (evento) => {
    evento.preventDefault();
    if (enviando) return;

    if (!familiaId) {
      notificarErrorFormulario(
        formulario,
        "No se encontró el identificador del familiar a actualizar.",
      );
      return;
    }

    let valido = true;
    formulario.querySelectorAll("[required]").forEach((campo) => {
      if (campo.closest(".hidden")) return;
      if (!String(campo.value || "").trim()) {
        InputUI.establecerInvalido(campo, "Este campo es obligatorio");
        valido = false;
      }
    });
    if (!valido) {
      notificarErrorFormulario(
        formulario,
        "Completa todos los campos obligatorios del familiar.",
      );
      return;
    }

    const errorDisc = validarBloqueDiscapacidadFamiliar(formulario);
    if (errorDisc) {
      notificarErrorFormulario(formulario, errorDisc);
      return;
    }

    const btnSubmit = formulario.querySelector('button[type="submit"]');
    const textoSpan = btnSubmit?.querySelector("span");
    const textoOriginal = textoSpan?.textContent || "Actualizar Familiar";

    enviando = true;
    if (btnSubmit) btnSubmit.disabled = true;
    if (textoSpan) textoSpan.textContent = "Guardando...";

    try {
      const res = await Solicitud.post(
        `/api/familiares/${familiaId}`,
        new FormData(formulario),
      );
      if (!res.ok || !res.data?.ok) {
        notificarErrorFormulario(
          formulario,
          res.message || "No se pudo actualizar el familiar.",
        );
        return;
      }

      notificarExitoFormulario(
        formulario,
        res.data.message || "Familiar actualizado correctamente.",
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
