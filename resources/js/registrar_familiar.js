import { Solicitud } from "/js/class/Solicitud.js";
import { InputUI } from "/js/class/validator/InputUI.js";
import { InputValidador } from "/js/class/validator/InputValidador.js";
import {
  limpiarAlertaFormulario,
  notificarErrorFormulario,
  notificarExitoFormulario,
} from "/js/components/alerta/alerta.js";
import { crearBadge } from "/js/components/badge/crearBadge.js";
import { inicializarCalendariosContenedor } from "/js/components/calendario/calendario.js";
import { inicializarSelectsPersonalizados } from "/js/components/select/selectPersonalizado.js";
import {
  filtrarRecaudosPorDiscapacidad,
  sincronizarPanelDiscapacidad,
  sincronizarReglaCedulaMenor,
  sincronizarVisibilidadTomoFolio,
  validarBloqueDiscapacidadFamiliar,
} from "/js/utils/reglasFamiliar.js";

const renderTarjetaRecaudoFamiliar = (rec) => {
  const badge = crearBadge(
    rec.obligatorio ? "Obligatorio" : "Opcional",
    rec.obligatorio ? "yellow" : "neutro",
  );

  const controlHtml = rec.es_titular
    ? '<p class="rounded-lg bg-sky-50 px-3 py-2 text-xs text-sky-800">Este documento se verifica directamente en el expediente del trabajador titular.</p>'
    : `
      <div>
        <label for="doc-fam-${rec.tipo_documento_id}" class="block text-[11px] font-medium text-zinc-600 mb-1">
          Adjuntar archivo (PDF/JPG/PNG)
        </label>
        <input
          id="doc-fam-${rec.tipo_documento_id}"
          name="documentos[${rec.tipo_documento_id}]"
          type="file"
          accept=".pdf,.jpg,.jpeg,.png,.webp"
          class="atlas-doc-fam-input w-full rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-zinc-700 file:mr-2.5 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-white hover:file:bg-zinc-800 cursor-pointer"
        />
      </div>`;

  return `
    <article class="rounded-2xl border ${rec.obligatorio ? "border-yellow-300 bg-yellow-50/40" : "border-slate-200 bg-slate-50/40"} p-4 flex flex-col justify-between gap-3">
      <div class="space-y-1">
        <div class="flex items-start justify-between gap-2">
          <h3 class="text-sm font-semibold text-zinc-900">${rec.etiqueta}</h3>
          ${badge}
        </div>
        ${rec.motivo ? `<p class="text-xs text-zinc-500">${rec.motivo}</p>` : ""}
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
  return lista.map(renderTarjetaRecaudoFamiliar).join("");
};

const leerMapaRecaudos = (formulario) => {
  if (!formulario?.dataset?.recaudosParentesco) return {};
  try {
    return JSON.parse(formulario.dataset.recaudosParentesco) || {};
  } catch {
    return {};
  }
};

// Recibe el formulario; lee el descriptor JSON del recaudo único de discapacidad desde data-recaudo-discapacidad.
const leerRecaudoDiscapacidadBase = (formulario) => {
  if (!formulario?.dataset?.recaudoDiscapacidad) return null;
  try {
    return JSON.parse(formulario.dataset.recaudoDiscapacidad) || null;
  } catch {
    return null;
  }
};

// Recibe el formulario; valida campos obligatorios visibles y bloque condicional de discapacidad.
const validarCamposRequeridos = (formulario) => {
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
    return false;
  }

  const errorDisc = validarBloqueDiscapacidadFamiliar(formulario);
  if (errorDisc) {
    notificarErrorFormulario(formulario, errorDisc);
    return false;
  }

  return true;
};

// Recibe ninguno; configura las validaciones en tiempo real de nombre, cédula, tomo y folio.
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
    "#formulario-registrar-familiar select, #fecha_nacimiento",
  );
};

document.addEventListener("DOMContentLoaded", () => {
  inicializarSelectsPersonalizados();
  inicializarCalendariosContenedor();
  configurarValidaciones();

  const selectEmpleado = document.getElementById("empleado_id");
  const selectParentesco = document.getElementById("parentesco_id");
  const inputFechaNac = document.getElementById("fecha_nacimiento");
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
  const camposRequeridosDisc = [
    document.getElementById("grado_discapacidad_id"),
    document.getElementById("numero_carnet"),
    document.getElementById("entidad_emisora"),
  ];
  const contenedorRecaudos = document.getElementById(
    "contenedor-recaudos-familiar",
  );
  const tarjetaTitular = document.getElementById("tarjeta-resumen-titular");
  const formulario = document.getElementById("formulario-registrar-familiar");
  const recaudosPorParentesco = leerMapaRecaudos(formulario);
  const recaudoDiscapacidadBase = leerRecaudoDiscapacidadBase(formulario);

  // Recibe el ID de parentesco; renderiza los recaudos filtrando el documento de discapacidad según estado activo.
  const actualizarRecaudos = (parentescoId) => {
    if (!contenedorRecaudos) return;
    const base = recaudosPorParentesco[String(parentescoId || "")] || [];
    const tieneDiscapacidad = inputTieneDisc?.value === "1";
    const lista =
      base.length > 0
        ? filtrarRecaudosPorDiscapacidad(
            base,
            tieneDiscapacidad,
            recaudoDiscapacidadBase,
          )
        : [];
    contenedorRecaudos.innerHTML = renderRecaudosFamiliar(lista);
    if (Array.isArray(lista) && lista.length > 0) {
      InputValidador.archivo(".atlas-doc-fam-input", { tamanoMaximoMB: 5 });
    }
  };

  // Recibe ninguno; aplica regla de cédula para < 9 años, visibilidad de tomo/folio y recaudos.
  const sincronizarReglasDinamicas = () => {
    sincronizarReglaCedulaMenor({
      inputFecha: inputFechaNac,
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

  const cargarResumenTitular = async (empleadoId) => {
    if (!empleadoId) {
      tarjetaTitular?.classList.add("hidden");
      return;
    }

    const res = await Solicitud.get(`/api/empleados/${empleadoId}`, {
      claveCancelacion: "resumen-titular",
    });

    if (res.aborted) return;
    if (!res.ok || !res.data?.ok || !res.data?.data) {
      tarjetaTitular?.classList.add("hidden");
      return;
    }

    const emp = res.data.data;
    const persona = emp.persona || {};
    const cedula =
      persona.nacionalidad && persona.cedula
        ? `${String(persona.nacionalidad).toUpperCase()}-${persona.cedula}`
        : "—";

    document.getElementById("titular-nombre").textContent =
      emp.nombre_completo || `Empleado #${empleadoId}`;
    document.getElementById("titular-cedula").textContent = cedula;
    document.getElementById("titular-codigo").textContent =
      emp.codigo_empleado || "—";
    document.getElementById("titular-cargo").textContent =
      [emp.cargo, emp.departamento].filter(Boolean).join(" · ") ||
      "Sin asignar";
    tarjetaTitular?.classList.remove("hidden");
  };

  selectParentesco?.addEventListener("change", () => {
    sincronizarReglasDinamicas();
    actualizarRecaudos(selectParentesco.value);
  });
  inputFechaNac?.addEventListener("change", sincronizarReglasDinamicas);
  inputFechaNac?.addEventListener("input", sincronizarReglasDinamicas);

  btnToggleDisc?.addEventListener("click", () => {
    const proximo = inputTieneDisc?.value !== "1";
    sincronizarPanelDiscapacidad({
      activo: proximo,
      inputHiddenActivo: inputTieneDisc,
      panelDiscapacidad: panelDisc,
      botonToggle: btnToggleDisc,
      camposRequeridos: camposRequeridosDisc,
    });
    actualizarRecaudos(selectParentesco?.value);
  });

  sincronizarReglasDinamicas();
  if (selectParentesco?.value) actualizarRecaudos(selectParentesco.value);

  selectEmpleado?.addEventListener("change", () =>
    cargarResumenTitular(selectEmpleado.value),
  );
  if (selectEmpleado?.value) cargarResumenTitular(selectEmpleado.value);

  let enviando = false;

  formulario?.addEventListener("submit", async (evento) => {
    evento.preventDefault();
    if (enviando || !validarCamposRequeridos(formulario)) return;

    const btnSubmit = formulario.querySelector('button[type="submit"]');
    const textoSpan = btnSubmit?.querySelector("span");
    const textoOriginal = textoSpan?.textContent || "Guardar Familiar";

    enviando = true;
    if (btnSubmit) btnSubmit.disabled = true;
    if (textoSpan) textoSpan.textContent = "Guardando...";

    try {
      const res = await Solicitud.post(
        "/api/familiares",
        new FormData(formulario),
      );
      if (!res.ok || !res.data?.ok) {
        notificarErrorFormulario(
          formulario,
          res.message || "No se pudo registrar el familiar.",
        );
        return;
      }

      notificarExitoFormulario(
        formulario,
        res.data.message || "Familiar registrado correctamente.",
      );
      setTimeout(() => {
        window.location.href = "/empleado/registros/familiar";
      }, 650);
    } finally {
      enviando = false;
      if (btnSubmit) btnSubmit.disabled = false;
      if (textoSpan) textoSpan.textContent = textoOriginal;
    }
  });

  document
    .getElementById("btn-limpiar-familiar")
    ?.addEventListener("click", () => {
      limpiarAlertaFormulario(formulario);
      tarjetaTitular?.classList.add("hidden");
      sincronizarPanelDiscapacidad({
        activo: false,
        inputHiddenActivo: inputTieneDisc,
        panelDiscapacidad: panelDisc,
        botonToggle: btnToggleDisc,
        camposRequeridos: camposRequeridosDisc,
      });
      setTimeout(() => {
        sincronizarReglasDinamicas();
        actualizarRecaudos(selectParentesco?.value);
      }, 0);
    });
});
