/**
 * registrar_familiar.js
 * Formulario de registro de carga familiar con validación visual y datos de prueba.
 * Guía estructural: PersonasModel, FamiliaresModel, TipoParentescoModel, EstadoCivilModel.
 */

import { InputValidador } from "/js/class/validator/InputValidador.js";
import { InputUI } from "/js/class/validator/InputUI.js";
import {
  inicializarSelectsPersonalizados,
  actualizarOpcionesSelect,
} from "/js/components/selectPersonalizado.js";
import { mostrarToast } from "/js/components/toast.js";

// TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
const empleadosTitularesPrueba = [
  {
    empleado_id: 1,
    codigo_empleado: "EMP-001",
    cedula: "V-21456789",
    nombre: "Carlos Eduardo Mendoza Ríos",
    cargo: "Analista de Talento Humano · Talento Humano",
  },
  {
    empleado_id: 2,
    codigo_empleado: "EMP-002",
    cedula: "V-19876543",
    nombre: "María Fernanda López Silva",
    cargo: "Coordinadora de Tecnología · Tecnología",
  },
  {
    empleado_id: 3,
    codigo_empleado: "EMP-003",
    cedula: "V-17654321",
    nombre: "José Gregorio Ramírez Pérez",
    cargo: "Facilitador Técnico · Formación",
  },
];

const estadosCivilesPrueba = {
  1: "Soltero(a)",
  2: "Casado(a)",
  3: "Divorciado(a)",
  4: "Viudo(a)",
  5: "Unión Estable",
};

const parentescosPrueba = {
  1: "Hijo(a)",
  2: "Cónyuge",
  3: "Madre",
  4: "Padre",
  5: "Hermano(a)",
};

document.addEventListener("DOMContentLoaded", () => {
  inicializarSelectsPersonalizados();

  // Poblar selectores desde datos de prueba
  actualizarOpcionesSelect(
    "#empleado_id",
    empleadosTitularesPrueba.map((emp) => ({
      valor: emp.empleado_id,
      etiqueta: emp.nombre,
      subetiqueta: emp.cedula,
    }))
  );
  actualizarOpcionesSelect("#estado_civil_id", estadosCivilesPrueba);
  actualizarOpcionesSelect("#parentesco_id", parentescosPrueba);

  // Mostrar tarjeta resumen al elegir un titular
  const selectEmpleado = document.getElementById("empleado_id");
  const tarjetaTitular = document.getElementById("tarjeta-resumen-titular");

  selectEmpleado?.addEventListener("change", () => {
    const titular = empleadosTitularesPrueba.find(
      (emp) => String(emp.empleado_id) === String(selectEmpleado.value)
    );

    if (!titular) {
      tarjetaTitular?.classList.add("hidden");
      return;
    }

    document.getElementById("titular-nombre").textContent = titular.nombre;
    document.getElementById("titular-cedula").textContent = titular.cedula;
    document.getElementById("titular-codigo").textContent = titular.codigo_empleado;
    document.getElementById("titular-cargo").textContent = titular.cargo;
    tarjetaTitular?.classList.remove("hidden");
  });

  // Validaciones de campos
  InputValidador.soloLetras("#primer_nombre, #segundo_nombre, #primer_apellido, #segundo_apellido", {
    minimo: 2,
    limite: 100,
    requerido: true,
  });
  InputValidador.soloNumeros("#cedula", { minimo: 6, limite: 9, requerido: true });
  InputValidador.soloNumeros("#tomo, #folio", { minimo: 1, limite: 6, requerido: true });
  InputValidador.requerido("#formulario-registrar-familiar select, #fecha_nacimiento");

  // Simulación de guardado
  const formulario = document.getElementById("formulario-registrar-familiar");
  formulario?.addEventListener("submit", (evento) => {
    evento.preventDefault();

    const camposRequeridos = formulario.querySelectorAll("[required]");
    let formularioValido = true;

    camposRequeridos.forEach((campo) => {
      if (!String(campo.value || "").trim()) {
        InputUI.establecerInvalido(campo, "Este campo es obligatorio");
        formularioValido = false;
      }
    });

    if (!formularioValido) {
      mostrarToast("error", "Completa todos los campos obligatorios del familiar.");
      return;
    }

    // TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
    const datosFormulario = Object.fromEntries(new FormData(formulario).entries());
    console.info("Registro de familiar simulado:", datosFormulario);
    mostrarToast("success", `Familiar ${datosFormulario.primer_nombre} ${datosFormulario.primer_apellido} registrado correctamente.`);
    formulario.reset();
    tarjetaTitular?.classList.add("hidden");
  });

  document.getElementById("btn-limpiar-familiar")?.addEventListener("click", () => {
    tarjetaTitular?.classList.add("hidden");
  });
});
