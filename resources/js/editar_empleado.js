/**
 * editar_empleado.js
 * Precarga de datos de prueba en el formulario de edición de empleado y simulación de actualización.
 * Guía estructural: PersonasModel, EmpleadosModel, DireccionModel, TelefonoPersonasModel.
 */

import { InputValidador } from "/js/class/validator/InputValidador.js";
import {
  inicializarSelectsPersonalizados,
  actualizarOpcionesSelect,
} from "/js/components/selectPersonalizado.js";
import { mostrarToast } from "/js/components/toast.js";

// TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
const catalogosEdicion = {
  estadosCiviles: { 1: "Soltero(a)", 2: "Casado(a)", 3: "Divorciado(a)", 4: "Viudo(a)" },
  dependencias: { 1: "Sede Central", 2: "CFS La Romana", 3: "CFS La Victoria" },
  departamentos: { 1: "Talento Humano", 2: "Tecnología", 3: "Administración", 4: "Formación" },
  cargos: { 1: "Analista de Talento Humano", 2: "Coordinador", 3: "Facilitador Técnico", 4: "Jefe de División" },
  estatus: { 1: "Activo", 2: "Vacaciones", 3: "Reposo", 4: "Jubilado" },
  nivelesAcademicos: { 1: "Bachiller", 2: "TSU", 3: "Universitario", 4: "Especialización", 5: "Maestría" },
  carreras: { 1: "Relaciones Industriales", 2: "Ingeniería en Informática", 3: "Administración de Empresas", 4: "Electromecánica" },
  parroquias: { 1: "Catedral", 2: "Altagracia", 3: "San Juan", 4: "El Recreo" },
};

const empleadoSeleccionadoPrueba = {
  empleado_id: 1,
  codigo_empleado: "EMP-001",
  primer_nombre: "Carlos",
  segundo_nombre: "Eduardo",
  primer_apellido: "Mendoza",
  segundo_apellido: "Ríos",
  nacionalidad: "V",
  cedula: "21456789",
  fecha_nacimiento: "1990-08-14",
  genero: "Masculino",
  estado_civil_id: "2",
  dependencia_id: "1",
  departamento_id: "1",
  cargo_id: "1",
  estatus_id: "1",
  nivel_academico_id: "3",
  carrera_id: "1",
  correo: "carlos.mendoza@inces.gob.ve",
  estatus_actual: "1",
  parroquia_id: "4",
  vivienda: "Apartamento",
  calle: "Av. Libertador",
  nombre_urb: "Urb. Los Caobos",
  piso_edificio: "Piso 4",
  numero_vivienda: "4-B",
  descripcion: "Frente a la plaza principal",
  telefono_principal: "0414-2345678",
  telefono_emergencia: "0424-9876543",
};

const asignarValor = (id, valor) => {
  const elemento = document.getElementById(id);
  if (elemento) {
    elemento.value = valor ?? "";
  }
};

document.addEventListener("DOMContentLoaded", () => {
  inicializarSelectsPersonalizados();

  // Poblar selects con valores preseleccionados
  actualizarOpcionesSelect("#estado_civil_id", catalogosEdicion.estadosCiviles, empleadoSeleccionadoPrueba.estado_civil_id);
  actualizarOpcionesSelect("#dependencia_id", catalogosEdicion.dependencias, empleadoSeleccionadoPrueba.dependencia_id);
  actualizarOpcionesSelect("#departamento_id", catalogosEdicion.departamentos, empleadoSeleccionadoPrueba.departamento_id);
  actualizarOpcionesSelect("#cargo_id", catalogosEdicion.cargos, empleadoSeleccionadoPrueba.cargo_id);
  actualizarOpcionesSelect("#estatus_id", catalogosEdicion.estatus, empleadoSeleccionadoPrueba.estatus_id);
  actualizarOpcionesSelect("#nivel_academico_id", catalogosEdicion.nivelesAcademicos, empleadoSeleccionadoPrueba.nivel_academico_id);
  actualizarOpcionesSelect("#carrera_id", catalogosEdicion.carreras, empleadoSeleccionadoPrueba.carrera_id);
  actualizarOpcionesSelect("#parroquia_id", catalogosEdicion.parroquias, empleadoSeleccionadoPrueba.parroquia_id);

  // Precargar inputs y selects estáticos
  [
    "codigo_empleado",
    "primer_nombre",
    "segundo_nombre",
    "primer_apellido",
    "segundo_apellido",
    "cedula",
    "fecha_nacimiento",
    "correo",
    "vivienda",
    "calle",
    "nombre_urb",
    "piso_edificio",
    "numero_vivienda",
    "descripcion",
    "telefono_principal",
    "telefono_emergencia",
  ].forEach((campo) => asignarValor(campo, empleadoSeleccionadoPrueba[campo]));

  actualizarOpcionesSelect("#nacionalidad", { V: "V", E: "E" }, empleadoSeleccionadoPrueba.nacionalidad);
  actualizarOpcionesSelect("#genero", { Masculino: "Masculino", Femenino: "Femenino" }, empleadoSeleccionadoPrueba.genero);
  actualizarOpcionesSelect("#estatus_actual", { 1: "Activo (1)", 0: "Inactivo (0)" }, empleadoSeleccionadoPrueba.estatus_actual);

  const subtitulo = document.getElementById("subtitulo-editar-empleado");
  if (subtitulo) {
    subtitulo.textContent = `Editando ficha de ${empleadoSeleccionadoPrueba.primer_nombre} ${empleadoSeleccionadoPrueba.primer_apellido} (${empleadoSeleccionadoPrueba.codigo_empleado}).`;
  }

  // Validaciones
  InputValidador.soloLetras("#primer_nombre, #segundo_nombre, #primer_apellido, #segundo_apellido", { minimo: 2, limite: 100, requerido: true });
  InputValidador.soloNumeros("#cedula", { minimo: 6, limite: 9, requerido: true });
  InputValidador.correoElectronico("#correo");

  // Simulación de actualización
  const formulario = document.getElementById("formulario-editar-empleado");
  formulario?.addEventListener("submit", (evento) => {
    evento.preventDefault();
    // TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
    const datosActualizados = Object.fromEntries(new FormData(formulario).entries());
    console.info("Actualización de empleado simulada:", datosActualizados);
    mostrarToast("success", `Ficha de ${datosActualizados.primer_nombre} ${datosActualizados.primer_apellido} actualizada correctamente.`);
  });
});
