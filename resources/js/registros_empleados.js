/**
 * registros_empleados.js
 * Gestión de la vista de listado de empleados con datos de prueba en memoria.
 * Guía estructural: EmpleadosModel, PersonasModel, CargoModel, DepartamentosModel, DependenciasModel, EstatusModel.
 */

import { TablaInteractiva } from "/js/class/TablaInteractiva.js";
import { ControladorModal } from "/js/class/ControladorModal.js";
import { mostrarToast } from "/js/components/toast.js";

// TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
let empleadosPrueba = [
  {
    empleado_id: 1,
    persona_id: 101,
    codigo_empleado: "EMP-001",
    nacionalidad: "V",
    cedula: 21456789,
    nombre_completo: "Carlos Eduardo Mendoza Ríos",
    cargo: "Analista de Talento Humano",
    departamento: "Talento Humano",
    dependencia: "Sede Central",
    estatus: "Activo",
    estatus_actual: 1,
    nivel_academico: "Universitario",
    carrera: "Relaciones Industriales",
    correo: "carlos.mendoza@inces.gob.ve",
    telefono: "0414-2345678",
  },
  {
    empleado_id: 2,
    persona_id: 102,
    codigo_empleado: "EMP-002",
    nacionalidad: "V",
    cedula: 19876543,
    nombre_completo: "María Fernanda López Silva",
    cargo: "Coordinadora de Tecnología",
    departamento: "Tecnología",
    dependencia: "Sede Central",
    estatus: "Activo",
    estatus_actual: 1,
    nivel_academico: "Maestría",
    carrera: "Ingeniería en Informática",
    correo: "maria.lopez@inces.gob.ve",
    telefono: "0424-8765432",
  },
  {
    empleado_id: 3,
    persona_id: 103,
    codigo_empleado: "EMP-003",
    nacionalidad: "V",
    cedula: 17654321,
    nombre_completo: "José Gregorio Ramírez Pérez",
    cargo: "Facilitador Técnico",
    departamento: "Formación",
    dependencia: "CFS La Romana",
    estatus: "Vacaciones",
    estatus_actual: 1,
    nivel_academico: "TSU",
    carrera: "Electromecánica",
    correo: "jose.ramirez@inces.gob.ve",
    telefono: "0416-5551234",
  },
  {
    empleado_id: 4,
    persona_id: 104,
    codigo_empleado: "EMP-004",
    nacionalidad: "V",
    cedula: 24112233,
    nombre_completo: "Ana Gabriela Torres Morales",
    cargo: "Asistente Administrativo",
    departamento: "Administración",
    dependencia: "CFS La Victoria",
    estatus: "Reposo",
    estatus_actual: 1,
    nivel_academico: "TSU",
    carrera: "Administración de Empresas",
    correo: "ana.torres@inces.gob.ve",
    telefono: "0412-9988776",
  },
  {
    empleado_id: 5,
    persona_id: 105,
    codigo_empleado: "EMP-005",
    nacionalidad: "E",
    cedula: 82345123,
    nombre_completo: "Luis Alberto Castillo Vera",
    cargo: "Jefe de División",
    departamento: "Planificación",
    dependencia: "Sede Central",
    estatus: "Inactivo",
    estatus_actual: 0,
    nivel_academico: "Especialización",
    carrera: "Gerencia Pública",
    correo: "luis.castillo@inces.gob.ve",
    telefono: "0426-3322110",
  },
];

const variantePorEstatus = (estatus) => {
  const mapa = {
    Activo: "green",
    Vacaciones: "sky",
    Reposo: "yellow",
    Inactivo: "red",
  };
  return mapa[estatus] || "neutro";
};

const actualizarResumen = () => {
  const total = empleadosPrueba.length;
  const activos = empleadosPrueba.filter((e) => e.estatus === "Activo").length;
  const otros = total - activos;

  const nodoTotal = document.getElementById("resumen-total-empleados");
  const nodoActivos = document.getElementById("resumen-empleados-activos");
  const nodoInactivos = document.getElementById("resumen-empleados-inactivos");

  if (nodoTotal) nodoTotal.textContent = String(total);
  if (nodoActivos) nodoActivos.textContent = String(activos);
  if (nodoInactivos) nodoInactivos.textContent = String(otros);
};

document.addEventListener("DOMContentLoaded", () => {
  const modalFicha = new ControladorModal("#modal-ficha-empleado");
  const modalEliminar = new ControladorModal("#modal-eliminar-empleado");

  const tabla = new TablaInteractiva({
    contenedor: "#tabla-empleados",
    datos: empleadosPrueba,
    claveId: "empleado_id",
    porPagina: 10,
    columnas: [
      { clave: "codigo_empleado", etiqueta: "Código", ordenable: true },
      {
        clave: "cedula",
        etiqueta: "Cédula",
        ordenable: true,
        renderizar: (fila) =>
          `<span class="font-medium text-zinc-900">${TablaInteractiva.escaparHtml(`${fila.nacionalidad}-${fila.cedula}`)}</span>`,
      },
      { clave: "nombre_completo", etiqueta: "Nombre completo", ordenable: true },
      { clave: "cargo", etiqueta: "Cargo", ordenable: true },
      { clave: "departamento", etiqueta: "Departamento", ordenable: true },
      { clave: "dependencia", etiqueta: "Dependencia", ordenable: true },
      {
        clave: "estatus",
        etiqueta: "Estatus",
        ordenable: true,
        alineacion: "center",
        renderizar: (fila) =>
          TablaInteractiva.crearBadgeEstado(fila.estatus, variantePorEstatus(fila.estatus)),
      },
      {
        clave: "acciones",
        etiqueta: "Acciones",
        alineacion: "right",
        renderizar: (fila) =>
          TablaInteractiva.crearBotonesAccion(fila.empleado_id, {
            hrefEditar: `/editar_empleado?id=${fila.empleado_id}`,
            etiquetaEliminar: "Desactivar empleado",
          }),
      },
    ],
    alAccionar: (accion, _id, registro) => {
      if (!registro) return;

      if (accion === "mostrar") {
        modalFicha.abrir({
          titulo: registro.nombre_completo,
          subtitulo: `${registro.codigo_empleado} · ${registro.cargo}`,
          contenidoHtml: `
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                <dt class="text-xs font-medium text-zinc-500">Documento de identidad</dt>
                <dd class="mt-1 font-semibold text-zinc-900">${TablaInteractiva.escaparHtml(`${registro.nacionalidad}-${registro.cedula}`)}</dd>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                <dt class="text-xs font-medium text-zinc-500">Estatus laboral</dt>
                <dd class="mt-1">${TablaInteractiva.crearBadgeEstado(registro.estatus, variantePorEstatus(registro.estatus))}</dd>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                <dt class="text-xs font-medium text-zinc-500">Departamento y Dependencia</dt>
                <dd class="mt-1 font-medium text-zinc-900">${TablaInteractiva.escaparHtml(`${registro.departamento} · ${registro.dependencia}`)}</dd>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                <dt class="text-xs font-medium text-zinc-500">Formación académica</dt>
                <dd class="mt-1 font-medium text-zinc-900">${TablaInteractiva.escaparHtml(`${registro.nivel_academico} en ${registro.carrera}`)}</dd>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                <dt class="text-xs font-medium text-zinc-500">Correo institucional</dt>
                <dd class="mt-1 font-medium text-zinc-900">${TablaInteractiva.escaparHtml(registro.correo)}</dd>
              </div>
              <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                <dt class="text-xs font-medium text-zinc-500">Teléfono de contacto</dt>
                <dd class="mt-1 font-medium text-zinc-900">${TablaInteractiva.escaparHtml(registro.telefono)}</dd>
              </div>
            </dl>
          `,
        });
      }

      if (accion === "eliminar") {
        modalEliminar.abrir({
          titulo: "Confirmar baja de empleado",
          subtitulo: `Código ${registro.codigo_empleado}`,
          contenidoHtml: `
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
              ¿Deseas marcar como <strong>Inactivo</strong> a <strong>${TablaInteractiva.escaparHtml(registro.nombre_completo)}</strong>?
            </div>
          `,
          textoConfirmar: "Desactivar",
          alConfirmar: () => {
            // TODO: Reemplazar datos de prueba con consulta real a la base de datos / API
            empleadosPrueba = empleadosPrueba.map((item) =>
              item.empleado_id === registro.empleado_id
                ? { ...item, estatus: "Inactivo", estatus_actual: 0 }
                : item
            );
            tabla.actualizarDatos(empleadosPrueba);
            actualizarResumen();
            mostrarToast("success", `Empleado ${registro.codigo_empleado} marcado como inactivo.`);
          },
        });
      }
    },
  });

  // Filtro rápido por estatus
  const filtroEstatus = document.getElementById("filtro-estatus-empleado");
  filtroEstatus?.addEventListener("change", (evento) => {
    tabla.establecerFiltro("estatus", evento.target.value);
  });

  actualizarResumen();
});
