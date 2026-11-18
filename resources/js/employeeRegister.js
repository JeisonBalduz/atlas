import { InputValidator } from "/js/class/validator/InputValidator.js";
import { initMultiStep } from "/js/components/multiStep.js";
import {
  initCustomSelects,
  updateSelectOptions,
} from "/js/components/customSelect.js";
import { showToast } from "/js/components/toast.js";

document.addEventListener("DOMContentLoaded", () => {
  // Inicializar Selects con Buscador y Desplegable Inteligente
  initCustomSelects();

  // Sincronizar dinámicamente los días según el mes y año de nacimiento
  const yearSelect = document.getElementById("ano");
  const monthSelect = document.getElementById("meses");
  const daySelect = document.getElementById("dia");

  const updateDaysInMonth = () => {
    const year = parseInt(yearSelect.value, 10) || new Date().getFullYear();
    const month = parseInt(monthSelect.value, 10);

    if (!month) {
      updateSelectOptions(
        "#dia",
        Array.from(
          {
            length: 31,
          },
          (_, i) => i + 1,
        ),
        daySelect.value,
      );
      return;
    }

    // new Date(año, mes, 0).getDate() calcula los días exactos del mes (28, 29, 30 o 31 días)
    const daysCount = new Date(year, month, 0).getDate();
    const days = Array.from(
      {
        length: daysCount,
      },
      (_, i) => i + 1,
    );

    updateSelectOptions("#dia", days, daySelect.value);
  };

  monthSelect?.addEventListener("change", updateDaysInMonth);
  yearSelect?.addEventListener("change", updateDaysInMonth);

  // Validaciones por campo (letras con mínimo 7 y máximo 50 caracteres)
  InputValidator.lettersOnly("#primerNombre, #primerApellido", {
    min: 7,
    limit: 50,
    required: true,
  });
  InputValidator.lettersOnly("#segundoNombre, #segundoApellido", {
    min: 7,
    limit: 50,
    required: true,
  });
  InputValidator.lettersOnly("#calle", {
    allowNumbers: true,
    limit: 120,
  });
  InputValidator.numbersOnly("#cedula", {
    limit: 8,
    min: 6,
  });
  InputValidator.numbersOnly("#edad", {
    limit: 2,
  });
  InputValidator.numbersOnly("#telefono", {
    limit: 7,
    min: 7,
  });
  InputValidator.required("#form-registro select, #fechaing3");
  InputValidator.file("#achivoDis, #contrato, #notificacion", {
    maxSizeMB: 5,
  });

  // Formulario por pasos
  initMultiStep("#form-registro", {
    onFinish: () => showToast("success", "Registro completado correctamente"),
  });
});
