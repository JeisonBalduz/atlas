import { InputUI } from "/js/class/validator/InputUI.js";

// Recibe una fecha ISO (YYYY-MM-DD); retorna la edad exacta cumplida en años o null si no es válida.
export const calcularEdadDesdeIso = (fechaIso) => {
  const texto = String(fechaIso || "").trim();
  if (!/^\d{4}-\d{2}-\d{2}$/.test(texto)) return null;

  const [anio, mes, dia] = texto.split("-").map(Number);
  const hoy = new Date();
  let edad = hoy.getFullYear() - anio;
  const mesActual = hoy.getMonth() + 1;
  const diaActual = hoy.getDate();

  if (mesActual < mes || (mesActual === mes && diaActual < dia)) {
    edad -= 1;
  }

  return edad >= 0 && edad <= 130 ? edad : null;
};

// Recibe un elemento <select> de parentesco; retorna true solo si corresponde a Hijo o Hija.
export const esParentescoHijo = (selectParentesco) => {
  if (!selectParentesco) return true;
  const valor = String(selectParentesco.value || "").trim();
  if (!valor) return true;

  const opcion = selectParentesco.options?.[selectParentesco.selectedIndex];
  const etiqueta = String(opcion?.textContent || "").trim().toLowerCase();
  if (etiqueta) {
    if (/^hij[oa]\b/.test(etiqueta)) return true;
    if (/(padre|madre|cónyuge|conyuge|espos|pareja|herman|abuel|t[ií]o|suegr)/.test(etiqueta)) {
      return false;
    }
  }

  return valor === "3" || valor === "4";
};

// Recibe los recaudos del parentesco, estado de discapacidad y recaudo base ID 10; filtra u agrega el recaudo de discapacidad.
export const filtrarRecaudosPorDiscapacidad = (
  listaRecaudos,
  tieneDiscapacidad,
  recaudoDiscapacidadBase = null,
) => {
  const base = Array.isArray(listaRecaudos) ? listaRecaudos : [];
  const sinDiscapacidad = base.filter((rec) => Number(rec.tipo_documento_id) !== 10);

  if (!tieneDiscapacidad) {
    return sinDiscapacidad;
  }

  const existenteEnLista = base.find((rec) => Number(rec.tipo_documento_id) === 10);
  const recaudoDisc = existenteEnLista || recaudoDiscapacidadBase || {
    tipo_documento_id: 10,
    etiqueta: "Certificado CONAPDIS / Informe Médico IVSS",
    obligatorio: false,
    motivo: "Opcional · Soporte único de discapacidad registrada (CONAPDIS / IVSS)",
    es_titular: false,
    cargado: false,
  };

  return [
    ...sinDiscapacidad,
    {
      ...recaudoDisc,
      etiqueta: "Certificado CONAPDIS / Informe Médico IVSS",
      obligatorio: false,
    },
  ];
};

// Recibe el input de fecha y los nodos de cédula; hace opcional la cédula para menores de 9 años.
export const sincronizarReglaCedulaMenor = ({ inputFecha, inputCedula, avisoCedula }) => {
  if (!inputCedula) return false;
  const edad = calcularEdadDesdeIso(inputFecha?.value);
  const esMenorDeNueve = edad !== null && edad < 9;

  inputCedula.required = !esMenorDeNueve;
  if (esMenorDeNueve) {
    inputCedula.placeholder = "Opcional (< 9 años: se genera por Tomo/Folio)";
    if (!String(inputCedula.value || "").trim()) {
      InputUI.establecerNeutral(inputCedula);
    }
  } else {
    inputCedula.placeholder = "Ej: 31245678";
  }

  avisoCedula?.classList.toggle("hidden", !esMenorDeNueve);
  return esMenorDeNueve;
};

// Recibe el select de parentesco y bloques de tomo/folio; oculta y desactiva obligatoriedad para no hijos.
export const sincronizarVisibilidadTomoFolio = ({
  selectParentesco,
  bloquesTomoFolio,
  inputTomo,
  inputFolio,
}) => {
  const mostrar = esParentescoHijo(selectParentesco);

  bloquesTomoFolio.forEach((nodo) => nodo?.classList.toggle("hidden", !mostrar));
  [inputTomo, inputFolio].forEach((campo) => {
    if (!campo) return;
    campo.required = mostrar;
    if (!mostrar) {
      InputUI.establecerNeutral(campo);
    }
  });

  return mostrar;
};

// Recibe los elementos del apartado de discapacidad; alterna visibilidad, estado oculto y obligatoriedad.
export const sincronizarPanelDiscapacidad = ({
  activo,
  inputHiddenActivo,
  panelDiscapacidad,
  botonToggle,
  camposRequeridos = [],
}) => {
  const estaActivo = Boolean(activo);
  if (inputHiddenActivo) inputHiddenActivo.value = estaActivo ? "1" : "0";
  panelDiscapacidad?.classList.toggle("hidden", !estaActivo);

  if (botonToggle) {
    const spanTexto = botonToggle.querySelector("[data-texto-toggle-disc]");
    if (spanTexto) {
      spanTexto.textContent = estaActivo ? "Ocultar discapacidad" : "Asignar discapacidad";
    }
    botonToggle.classList.toggle("bg-marca", !estaActivo);
    botonToggle.classList.toggle("text-white", !estaActivo);
    botonToggle.classList.toggle("hover:bg-marca-700", !estaActivo);
    botonToggle.classList.toggle("border", estaActivo);
    botonToggle.classList.toggle("border-red-200", estaActivo);
    botonToggle.classList.toggle("bg-red-50", estaActivo);
    botonToggle.classList.toggle("text-red-700", estaActivo);
    botonToggle.classList.toggle("hover:bg-red-100", estaActivo);
  }

  camposRequeridos.forEach((campo) => {
    if (!campo) return;
    campo.required = estaActivo;
    if (!estaActivo) {
      InputUI.establecerNeutral(campo);
    }
  });

  return estaActivo;
};

// Recibe el formulario del familiar; valida los campos de discapacidad cuando está activada.
export const validarBloqueDiscapacidadFamiliar = (formulario) => {
  if (formulario?.querySelector("#tiene_discapacidad")?.value !== "1") {
    return null;
  }

  const grado = String(formulario.querySelector("#grado_discapacidad_id")?.value || "").trim();
  const carnet = String(formulario.querySelector("#numero_carnet")?.value || "").trim();
  const entidad = String(formulario.querySelector("#entidad_emisora")?.value || "").trim();
  const emision = String(formulario.querySelector("#fecha_emision_carnet")?.value || "").trim();
  const vencimiento = String(formulario.querySelector("#fecha_vencimiento_carnet")?.value || "").trim();

  if (!grado || !carnet || !entidad) {
    return "Completa el grado de discapacidad, número de carnet CONAPDIS y entidad emisora.";
  }
  if (emision && vencimiento && vencimiento < emision) {
    return "La fecha de vencimiento del carnet no puede ser menor que la fecha de emisión.";
  }

  return null;
};
