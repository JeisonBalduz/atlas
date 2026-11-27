import { Solicitud } from "/js/class/Solicitud.js";
import { InputUI } from "/js/class/validator/InputUI.js";
import { InputValidador } from "/js/class/validator/InputValidador.js";
import {
  notificarErrorFormulario,
  notificarExitoFormulario,
} from "/js/components/alerta/alerta.js";
import {
  inicializarCalendario,
  inicializarCalendariosContenedor,
} from "/js/components/calendario/calendario.js";
import { inicializarPasoMultiple } from "/js/components/pasoMultiple/pasoMultiple.js";
import {
  actualizarOpcionesSelect,
  inicializarSelectsPersonalizados,
} from "/js/components/select/selectPersonalizado.js";
import { mostrarToast } from "/js/components/toast/toast.js";
import { calcularEdad } from "/js/utils/calcularEdad.js";

const obtenerFechaHoyIso = () => {
  const hoy = new Date();
  const anio = hoy.getFullYear();
  const mes = String(hoy.getMonth() + 1).padStart(2, "0");
  const dia = String(hoy.getDate()).padStart(2, "0");
  return `${anio}-${mes}-${dia}`;
};

const parsearFechaNacimiento = (valor) => {
  const texto = String(valor || "").trim();
  if (!texto) return null;

  let dia;
  let mes;
  let anio;

  const matchVisual = /^(\d{2})-(\d{2})-(\d{4})$/.exec(texto);
  const matchIso = /^(\d{4})-(\d{2})-(\d{2})$/.exec(texto);

  if (matchVisual) {
    dia = Number(matchVisual[1]);
    mes = Number(matchVisual[2]);
    anio = Number(matchVisual[3]);
  } else if (matchIso) {
    anio = Number(matchIso[1]);
    mes = Number(matchIso[2]);
    dia = Number(matchIso[3]);
  } else {
    return { valida: false, motivo: "Formato inválido. Usa dd-mm-yyyy." };
  }

  if (mes < 1 || mes > 12 || dia < 1 || dia > 31 || anio < 1900) {
    return { valida: false, motivo: "La fecha de nacimiento no es válida." };
  }

  const fecha = new Date(anio, mes - 1, dia);
  if (
    fecha.getFullYear() !== anio ||
    fecha.getMonth() !== mes - 1 ||
    fecha.getDate() !== dia
  ) {
    return {
      valida: false,
      motivo: "La fecha de nacimiento no existe en el calendario.",
    };
  }

  const hoy = new Date();
  const anioHoy = hoy.getFullYear();
  const mesHoy = hoy.getMonth() + 1;
  const diaHoy = hoy.getDate();
  const esFutura =
    anio > anioHoy ||
    (anio === anioHoy && (mes > mesHoy || (mes === mesHoy && dia > diaHoy)));

  if (esFutura) {
    return {
      valida: false,
      motivo: "La fecha de nacimiento no puede ser futura.",
    };
  }

  const dd = String(dia).padStart(2, "0");
  const mm = String(mes).padStart(2, "0");
  const yyyy = String(anio).padStart(4, "0");

  return {
    valida: true,
    anio,
    mes,
    dia,
    visual: `${dd}-${mm}-${yyyy}`,
    iso: `${yyyy}-${mm}-${dd}`,
  };
};

const configurarValidaciones = () => {
  InputValidador.soloLetras(
    "#primerNombre, #primerApellido, #segundoNombre, #segundoApellido",
    {
      minimo: 2,
      limite: 50,
      requerido: true,
    },
  );
  InputValidador.soloLetras("#calle", { permitirNumeros: true, limite: 120 });
  InputValidador.soloNumeros("#cedula", { limite: 8, minimo: 6 });
  InputValidador.soloNumeros("#edad", { limite: 2 });
  InputValidador.soloNumeros("#telefono", { limite: 7, minimo: 7 });
  InputValidador.requerido("#form-registro select, #fecha_nacimiento, #fechaing3");
  InputValidador.archivo("#contrato, #notificacion", { tamanoMaximoMB: 5 });
};

document.addEventListener("DOMContentLoaded", () => {
  inicializarSelectsPersonalizados();

  const formularioRegistro = document.getElementById("form-registro");
  const inputFechaNacimiento = document.getElementById("fecha_nacimiento");
  const inputEdad = document.getElementById("edad");
  const selectEstado = document.getElementById("estado");
  const selectMunicipio = document.getElementById("municipio");

  const limpiarEdad = () => {
    if (inputEdad) {
      inputEdad.value = "";
      InputUI.establecerNeutral(inputEdad);
    }
  };

  const validarYActualizarFechaNacimiento = (valorBruto) => {
    const texto = String(valorBruto ?? inputFechaNacimiento?.value ?? "").trim();

    if (!texto) {
      limpiarEdad();
      if (inputFechaNacimiento) InputUI.establecerNeutral(inputFechaNacimiento);
      return null;
    }

    const resultado = parsearFechaNacimiento(texto);
    if (!resultado || !resultado.valida) {
      limpiarEdad();
      if (inputFechaNacimiento) {
        InputUI.establecerInvalido(
          inputFechaNacimiento,
          resultado?.motivo || "Fecha de nacimiento inválida.",
        );
      }
      return null;
    }

    if (inputFechaNacimiento && inputFechaNacimiento.value !== resultado.visual) {
      inputFechaNacimiento.value = resultado.visual;
    }
    if (inputFechaNacimiento) {
      InputUI.establecerValido(inputFechaNacimiento);
    }

    const edadCalculada = calcularEdad(
      resultado.anio,
      resultado.mes,
      resultado.dia,
    );
    if (inputEdad) {
      inputEdad.value = edadCalculada;
      if (edadCalculada !== "") {
        InputUI.establecerValido(inputEdad);
      } else {
        InputUI.establecerNeutral(inputEdad);
      }
    }

    return resultado;
  };

  const calFechaNacimiento = inicializarCalendario(inputFechaNacimiento, {
    placeholder: "dd-mm-yyyy",
    maxDate: obtenerFechaHoyIso(),
    alSeleccionar: (fechaSeleccionada) => {
      validarYActualizarFechaNacimiento(fechaSeleccionada);
    },
  });
  calFechaNacimiento?.set("dateFormat", "d-m-Y");

  inicializarCalendariosContenedor();
  configurarValidaciones();

  inputFechaNacimiento?.addEventListener("change", () => {
    if (!inputFechaNacimiento.value.trim()) {
      limpiarEdad();
      InputUI.establecerInvalido(
        inputFechaNacimiento,
        "Este campo es obligatorio",
      );
      return;
    }
    validarYActualizarFechaNacimiento(inputFechaNacimiento.value);
  });

  inputFechaNacimiento?.addEventListener("blur", () => {
    if (calFechaNacimiento?.isOpen) return;
    if (!inputFechaNacimiento.value.trim()) {
      limpiarEdad();
      InputUI.establecerInvalido(
        inputFechaNacimiento,
        "Este campo es obligatorio",
      );
      return;
    }
    validarYActualizarFechaNacimiento(inputFechaNacimiento.value);
  });

  formularioRegistro?.addEventListener("reset", () => {
    setTimeout(() => {
      limpiarEdad();
      if (inputFechaNacimiento) InputUI.establecerNeutral(inputFechaNacimiento);
    }, 0);
  });

  selectEstado?.addEventListener("change", async () => {
    const estadoId = selectEstado.value;
    actualizarOpcionesSelect("#municipio", {});
    actualizarOpcionesSelect("#parroquia", {});
    if (!estadoId) return;

    const res = await Solicitud.get(`/api/ubicacion/municipios/${estadoId}`);
    if (!res.ok || !res.data?.ok) {
      mostrarToast(
        "error",
        res.message || "No se pudieron cargar los municipios.",
      );
      return;
    }
    actualizarOpcionesSelect("#municipio", res.data.data || {});
  });

  selectMunicipio?.addEventListener("change", async () => {
    const municipioId = selectMunicipio.value;
    actualizarOpcionesSelect("#parroquia", {});
    if (!municipioId) return;

    const res = await Solicitud.get(`/api/ubicacion/parroquias/${municipioId}`);
    if (!res.ok || !res.data?.ok) {
      mostrarToast(
        "error",
        res.message || "No se pudieron cargar las parroquias.",
      );
      return;
    }
    actualizarOpcionesSelect("#parroquia", res.data.data || {});
  });

  let enviando = false;

  inicializarPasoMultiple("#form-registro", {
    alFinalizar: async (formulario) => {
      if (enviando) return;
      enviando = true;

      const btnFinalizar = formulario.querySelector("[data-step-next]");
      const etiquetaBtn = formulario.querySelector("[data-step-next-label]");
      const textoOriginal = etiquetaBtn?.textContent || "Finalizar";

      if (btnFinalizar) btnFinalizar.disabled = true;
      if (etiquetaBtn) etiquetaBtn.textContent = "Guardando...";

      try {
        const resultadoFecha = validarYActualizarFechaNacimiento(
          inputFechaNacimiento?.value,
        );
        if (!resultadoFecha) {
          if (inputFechaNacimiento && !inputFechaNacimiento.value.trim()) {
            InputUI.establecerInvalido(
              inputFechaNacimiento,
              "Este campo es obligatorio",
            );
          }
          notificarErrorFormulario(
            formulario,
            "Selecciona una fecha de nacimiento válida en formato dd-mm-yyyy.",
          );
          return;
        }

        const datosEnvio = new FormData(formulario);
        datosEnvio.set("fecha_nacimiento", resultadoFecha.iso);

        const res = await Solicitud.post(
          "/api/empleados",
          datosEnvio,
        );
        if (!res.ok || !res.data?.ok) {
          notificarErrorFormulario(
            formulario,
            res.message || "No se pudo registrar el trabajador.",
          );
          return;
        }

        notificarExitoFormulario(
          formulario,
          res.data.message || "Trabajador registrado en estado Pendiente.",
        );
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } finally {
        enviando = false;
        if (btnFinalizar) btnFinalizar.disabled = false;
        if (etiquetaBtn) etiquetaBtn.textContent = textoOriginal;
      }
    },
  });
});
