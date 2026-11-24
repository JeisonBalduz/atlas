import { Solicitud } from "/js/class/Solicitud.js";
import { InputValidador } from "/js/class/validator/InputValidador.js";
import {
  notificarErrorFormulario,
  notificarExitoFormulario,
} from "/js/components/alerta/alerta.js";
// import { //inicializarCalendariosContenedor } from '/js/components/calendario/calendario.js';
import { inicializarPasoMultiple } from "/js/components/pasoMultiple/pasoMultiple.js";
import {
  actualizarOpcionesSelect,
  inicializarSelectsPersonalizados,
} from "/js/components/select/selectPersonalizado.js";
import { mostrarToast } from "/js/components/toast/toast.js";
import { calcularEdad } from "/js/utils/calcularEdad.js";

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
  InputValidador.requerido("#form-registro select, #fechaing3");
  InputValidador.archivo("#contrato, #notificacion", { tamanoMaximoMB: 5 });
};

document.addEventListener("DOMContentLoaded", () => {
  inicializarSelectsPersonalizados();
  //inicializarCalendariosContenedor();
  configurarValidaciones();

  const selectAno = document.getElementById("ano");
  const selectMes = document.getElementById("meses");
  const selectDia = document.getElementById("dia");
  const inputEdad = document.getElementById("edad");
  const selectEstado = document.getElementById("estado");
  const selectMunicipio = document.getElementById("municipio");

  const actualizarDiasDelMes = () => {
    const ano =
      parseInt(selectAno?.value || "", 10) || new Date().getFullYear();
    const mes = parseInt(selectMes?.value || "", 10);
    const totalDias = mes ? new Date(ano, mes, 0).getDate() : 31;
    const dias = Array.from({ length: totalDias }, (_, indice) => indice + 1);
    actualizarOpcionesSelect("#dia", dias, selectDia?.value || "");
  };

  const actualizarEdad = () => {
    const calculada = calcularEdad(
      parseInt(selectAno?.value || "", 10),
      parseInt(selectMes?.value || "", 10),
      parseInt(selectDia?.value || "", 10),
    );
    if (inputEdad && calculada !== "") inputEdad.value = calculada;
  };

  selectMes?.addEventListener("change", () => {
    actualizarDiasDelMes();
    actualizarEdad();
  });
  selectAno?.addEventListener("change", () => {
    actualizarDiasDelMes();
    actualizarEdad();
  });
  selectDia?.addEventListener("change", actualizarEdad);

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
        const res = await Solicitud.post(
          "/api/empleados",
          new FormData(formulario),
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
          window.location.href = "/empleado/registros";
        }, 700);
      } finally {
        enviando = false;
        if (btnFinalizar) btnFinalizar.disabled = false;
        if (etiquetaBtn) etiquetaBtn.textContent = textoOriginal;
      }
    },
  });
});
