import { InputValidador } from "/js/class/validator/InputValidador.js";
import { inicializarPasoMultiple } from "/js/components/pasoMultiple.js";
import {
  inicializarSelectsPersonalizados,
  actualizarOpcionesSelect,
} from "/js/components/selectPersonalizado.js";
import { mostrarToast } from "/js/components/toast.js";

document.addEventListener("DOMContentLoaded", () => {
  // Inicializar Selects con Buscador y Desplegable Inteligente
  inicializarSelectsPersonalizados();

  // Sincronizar dinámicamente los días según el mes y año de nacimiento
  const selectAno = document.getElementById("ano");
  const selectMes = document.getElementById("meses");
  const selectDia = document.getElementById("dia");

  const actualizarDiasDelMes = () => {
    const ano = parseInt(selectAno.value, 10) || new Date().getFullYear();
    const mes = parseInt(selectMes.value, 10);

    if (!mes) {
      actualizarOpcionesSelect(
        "#dia",
        Array.from(
          {
            length: 31,
          },
          (_, indice) => indice + 1,
        ),
        selectDia.value,
      );
      return;
    }

    // new Date(año, mes, 0).getDate() calcula los días exactos del mes (28, 29, 30 o 31 días)
    const cantidadDias = new Date(ano, mes, 0).getDate();
    const dias = Array.from(
      {
        length: cantidadDias,
      },
      (_, indice) => indice + 1,
    );

    actualizarOpcionesSelect("#dia", dias, selectDia.value);
  };

  selectMes?.addEventListener("change", actualizarDiasDelMes);
  selectAno?.addEventListener("change", actualizarDiasDelMes);

  // Validaciones por campo (letras con mínimo 7 y máximo 50 caracteres)
  InputValidador.soloLetras("#primerNombre, #primerApellido", {
    minimo: 7,
    limite: 50,
    requerido: true,
  });

  InputValidador.soloLetras("#segundoNombre, #segundoApellido", {
    minimo: 7,
    limite: 50,
    requerido: true,
  });

  InputValidador.soloLetras("#calle", {
    permitirNumeros: true,
    limite: 120,
  });

  InputValidador.soloNumeros("#cedula", {
    limite: 8,
    minimo: 6,
  });

  InputValidador.soloNumeros("#edad", {
    limite: 2,
  });

  InputValidador.soloNumeros("#telefono", {
    limite: 7,
    minimo: 7,
  });

  InputValidador.requerido("#form-registro select, #fechaing3");

  InputValidador.archivo("#achivoDis, #contrato, #notificacion", {
    tamanoMaximoMB: 5,
  });

  // Formulario por pasos
  inicializarPasoMultiple("#form-registro", {
    alFinalizar: () => mostrarToast("success", "Registro completado correctamente"),
  });
});
