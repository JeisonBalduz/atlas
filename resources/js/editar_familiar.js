/**
 * editar_familiar.js
 * Precarga de datos de prueba del familiar y su empleado titular con guardado simulado.
 * Guía estructural: FamiliaresModel, PersonasModel, TipoParentescoModel.
 */

import { InputValidador } from "/js/class/validator/InputValidador.js";
import {
  inicializarSelectsPersonalizados,
  actualizarOpcionesSelect,
} from "/js/components/selectPersonalizado.js";
import { mostrarToast } from "/js/components/toast.js";

// TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
const parentescosPrueba = {
  1: "Hijo(a)",
  2: "Cónyuge",
  3: "Madre",
  4: "Padre",
  5: "Hermano(a)",
};

const familiarSeleccionadoPrueba = {
  familia_id: 1,
  titular_nombre: "Carlos Eduardo Mendoza Ríos",
  titular_cedula: "V-21456789 · EMP-001",
  titular_cargo: "Analista de Talento Humano · Talento Humano",
  nacionalidad: "V",
  cedula: "31245678",
  primer_nombre: "Sofía",
  segundo_nombre: "Valentina",
  primer_apellido: "Mendoza",
  segundo_apellido: "Herrera",
  fecha_nacimiento: "2014-06-18",
  genero: "Femenino",
  parentesco_id: "1",
  tomo: "4",
  folio: "128",
};

document.addEventListener("DOMContentLoaded", () => {
  inicializarSelectsPersonalizados();

  // Cargar resumen del empleado titular
  document.getElementById("editar-titular-nombre").textContent = familiarSeleccionadoPrueba.titular_nombre;
  document.getElementById("editar-titular-cedula").textContent = familiarSeleccionadoPrueba.titular_cedula;
  document.getElementById("editar-titular-cargo").textContent = familiarSeleccionadoPrueba.titular_cargo;

  // Precargar campos del familiar
  ["cedula", "primer_nombre", "segundo_nombre", "primer_apellido", "segundo_apellido", "fecha_nacimiento", "tomo", "folio"].forEach(
    (campo) => {
      const nodo = document.getElementById(campo);
      if (nodo) nodo.value = familiarSeleccionadoPrueba[campo];
    }
  );

  actualizarOpcionesSelect("#nacionalidad", { V: "V", E: "E" }, familiarSeleccionadoPrueba.nacionalidad);
  actualizarOpcionesSelect("#genero", { Masculino: "Masculino", Femenino: "Femenino" }, familiarSeleccionadoPrueba.genero);
  actualizarOpcionesSelect("#parentesco_id", parentescosPrueba, familiarSeleccionadoPrueba.parentesco_id);

  // Validaciones
  InputValidador.soloLetras("#primer_nombre, #segundo_nombre, #primer_apellido, #segundo_apellido", { minimo: 2, limite: 100, requerido: true });
  InputValidador.soloNumeros("#cedula", { minimo: 6, limite: 9, requerido: true });
  InputValidador.soloNumeros("#tomo, #folio", { minimo: 1, limite: 6, requerido: true });

  const formulario = document.getElementById("formulario-editar-familiar");
  formulario?.addEventListener("submit", (evento) => {
    evento.preventDefault();
    // TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
    const datos = Object.fromEntries(new FormData(formulario).entries());
    console.info("Actualización de familiar simulada:", datos);
    mostrarToast("success", `Datos de ${datos.primer_nombre} ${datos.primer_apellido} actualizados correctamente.`);
  });
});
